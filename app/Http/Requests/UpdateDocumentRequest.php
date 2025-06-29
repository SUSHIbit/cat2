<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Document Request
 * 
 * Validates document update requests allowing users to modify
 * title and description of their uploaded documents.
 */
class UpdateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by middleware and route model binding
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
            'title' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\w\s\-\(\)\[\]\.]+$/', // Allow alphanumeric, spaces, and common punctuation
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Document title is required.',
            'title.min' => 'Title must be at least 2 characters long.',
            'title.max' => 'Title must not exceed 255 characters.',
            'title.regex' => 'Title contains invalid characters. Please use only letters, numbers, spaces, and basic punctuation.',
            'description.max' => 'Description must not exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'document title',
            'description' => 'document description',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from text inputs
        $this->merge([
            'title' => trim($this->input('title', '')),
            'description' => trim($this->input('description', '')),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $title = $this->input('title');
            
            // Check for potentially dangerous content in title
            if ($title && preg_match('/<[^>]*>/', $title)) {
                $validator->errors()->add('title', 'Title cannot contain HTML tags.');
            }
            
            // Check for script injection attempts
            if ($title && preg_match('/script|javascript|vbscript|onload|onerror/i', $title)) {
                $validator->errors()->add('title', 'Title contains prohibited content.');
            }
            
            $description = $this->input('description');
            
            // Basic HTML/script check for description
            if ($description && preg_match('/<script|javascript:|vbscript:|onload=|onerror=/i', $description)) {
                $validator->errors()->add('description', 'Description contains prohibited content.');
            }
        });
    }
}