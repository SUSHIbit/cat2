<?php

namespace App\Http\Requests;

use App\Models\Document;
use App\Models\Simplification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create Simplification Request
 * 
 * Validates simplification creation requests with document verification,
 * AI model selection, and complexity level validation.
 */
class CreateSimplificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'document_id' => [
                'required',
                'integer',
                'exists:documents,id',
                function ($attribute, $value, $fail) {
                    $document = Document::find($value);
                    
                    // Check if document belongs to authenticated user
                    if (!$document || $document->user_id !== auth()->id()) {
                        $fail('The selected document is invalid or does not belong to you.');
                        return;
                    }
                    
                    // Check if document is processed and ready for simplification
                    if ($document->status !== Document::STATUS_COMPLETED) {
                        $fail('The selected document is not ready for simplification. Please wait for processing to complete.');
                        return;
                    }
                    
                    // Check if document has extracted content
                    if (empty($document->extracted_content)) {
                        $fail('The selected document does not have extractable content for simplification.');
                        return;
                    }
                },
            ],
            'ai_model' => [
                'required',
                'string',
                Rule::in(array_keys(Simplification::getAvailableModels())),
            ],
            'complexity_level' => [
                'required',
                'string',
                Rule::in(array_keys(Simplification::getComplexityLevels())),
            ],
            'custom_instructions' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'document_id.required' => 'Please select a document to simplify.',
            'document_id.integer' => 'Invalid document selection.',
            'document_id.exists' => 'The selected document could not be found.',
            'ai_model.required' => 'Please select an AI model.',
            'ai_model.in' => 'The selected AI model is not available.',
            'complexity_level.required' => 'Please select a complexity level.',
            'complexity_level.in' => 'The selected complexity level is invalid.',
            'custom_instructions.max' => 'Custom instructions must not exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'document_id' => 'document',
            'ai_model' => 'AI model',
            'complexity_level' => 'complexity level',
            'custom_instructions' => 'custom instructions',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim custom instructions
        if ($this->has('custom_instructions')) {
            $this->merge([
                'custom_instructions' => trim($this->input('custom_instructions')),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check for custom instructions content safety
            $customInstructions = $this->input('custom_instructions');
            if ($customInstructions) {
                // Check for potentially harmful instructions
                $prohibitedPatterns = [
                    '/ignore.*previous.*instructions?/i',
                    '/bypass.*safety/i',
                    '/act\s+as.*(?:jailbreak|dan|evil)/i',
                    '/pretend.*you.*are/i',
                    '/roleplay.*as/i',
                ];
                
                foreach ($prohibitedPatterns as $pattern) {
                    if (preg_match($pattern, $customInstructions)) {
                        $validator->errors()->add('custom_instructions', 'Custom instructions contain prohibited content.');
                        break;
                    }
                }
                
                // Check for script injection attempts
                if (preg_match('/<script|javascript:|vbscript:|onload=|onerror=/i', $customInstructions)) {
                    $validator->errors()->add('custom_instructions', 'Custom instructions contain invalid content.');
                }
            }
            
            // Check if user has too many pending/processing simplifications
            $user = auth()->user();
            if ($user) {
                $pendingCount = $user->simplifications()
                    ->whereIn('status', [Simplification::STATUS_PENDING, Simplification::STATUS_PROCESSING])
                    ->count();
                
                $maxPending = 5; // Maximum pending simplifications
                if ($pendingCount >= $maxPending) {
                    $validator->errors()->add('document_id', "You have too many simplifications in progress. Please wait for some to complete before starting new ones (limit: {$maxPending}).");
                }
            }
            
            // Check for recent duplicate simplifications
            if ($this->filled('document_id')) {
                $documentId = $this->input('document_id');
                $aiModel = $this->input('ai_model');
                $complexityLevel = $this->input('complexity_level');
                
                $recentDuplicate = Simplification::where('document_id', $documentId)
                    ->where('ai_model', $aiModel)
                    ->where('complexity_level', $complexityLevel)
                    ->where('status', Simplification::STATUS_COMPLETED)
                    ->where('created_at', '>', now()->subHours(1))
                    ->exists();
                
                if ($recentDuplicate) {
                    $validator->errors()->add('document_id', 'A similar simplification was created recently. Please wait before creating another with the same settings.');
                }
            }
            
            // Validate AI model availability (could be disabled temporarily)
            $aiModel = $this->input('ai_model');
            if ($aiModel === Simplification::MODEL_GPT_4) {
                // Check if GPT-4 is available (could be based on user plan, rate limits, etc.)
                $gpt4Available = config('cat-simplifier.openai.gpt4_enabled', true);
                if (!$gpt4Available) {
                    $validator->errors()->add('ai_model', 'GPT-4 is currently unavailable. Please select GPT-3.5 Turbo.');
                }
            }
        });
    }

    /**
     * Get the validated data with additional processing.
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        
        // Add additional metadata
        $data['created_from_ip'] = $this->ip();
        $data['user_agent'] = $this->userAgent();
        
        return $data;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // Log validation failures for monitoring
        \Log::warning('Simplification creation validation failed', [
            'user_id' => auth()->id(),
            'document_id' => $this->input('document_id'),
            'ai_model' => $this->input('ai_model'),
            'complexity_level' => $this->input('complexity_level'),
            'errors' => $validator->errors()->toArray(),
        ]);
        
        parent::failedValidation($validator);
    }
}