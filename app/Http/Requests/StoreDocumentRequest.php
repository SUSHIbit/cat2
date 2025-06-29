<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * Store Document Request
 * 
 * Validates document upload requests with comprehensive file validation,
 * size limits, and type restrictions for the Cat Document Simplifier.
 */
class StoreDocumentRequest extends FormRequest
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
        $maxFileSize = config('cat-simplifier.uploads.max_file_size', 10240); // KB
        $supportedTypes = Document::SUPPORTED_TYPES;
        
        return [
            'file' => [
                'required',
                'file',
                File::types(array_keys($supportedTypes))
                    ->max($maxFileSize)
                    ->rules([
                        'mimes:pdf,docx,pptx',
                        'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    ]),
            ],
            'title' => [
                'nullable',
                'string',
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
        $maxSizeMB = round(config('cat-simplifier.uploads.max_file_size', 10240) / 1024, 1);
        
        return [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is invalid.',
            'file.types' => 'Only PDF, Word (.docx), and PowerPoint (.pptx) files are supported.',
            'file.max' => "The file size must not exceed {$maxSizeMB}MB.",
            'file.mimes' => 'Invalid file type. Please upload a PDF, Word document, or PowerPoint presentation.',
            'file.mimetypes' => 'Invalid file format detected. Please ensure the file is not corrupted.',
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
            'file' => 'document file',
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
        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->input('title')),
            ]);
        }
        
        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->input('description')),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional file validation
            if ($this->hasFile('file') && $this->file('file')->isValid()) {
                $file = $this->file('file');
                
                // Check if file is actually readable
                if (!is_readable($file->getRealPath())) {
                    $validator->errors()->add('file', 'The uploaded file cannot be read. Please try uploading again.');
                    return;
                }
                
                // Check file size again (double-check)
                $maxSizeBytes = config('cat-simplifier.uploads.max_file_size', 10240) * 1024;
                if ($file->getSize() > $maxSizeBytes) {
                    $maxSizeMB = round($maxSizeBytes / (1024 * 1024), 1);
                    $validator->errors()->add('file', "File size exceeds the maximum limit of {$maxSizeMB}MB.");
                    return;
                }
                
                // Validate MIME type more strictly
                $detectedMimeType = mime_content_type($file->getRealPath());
                $allowedMimeTypes = array_values(Document::SUPPORTED_TYPES);
                
                if (!in_array($detectedMimeType, $allowedMimeTypes)) {
                    $validator->errors()->add('file', 'File type verification failed. Please ensure you are uploading a valid PDF, Word, or PowerPoint file.');
                    return;
                }
                
                // Check for minimum file size (empty files)
                if ($file->getSize() < 1024) { // Less than 1KB
                    $validator->errors()->add('file', 'The file appears to be empty or too small to process.');
                    return;
                }
                
                // Validate file extension matches MIME type
                $extension = strtolower($file->getClientOriginalExtension());
                $expectedMimeType = Document::SUPPORTED_TYPES[$extension] ?? null;
                
                if (!$expectedMimeType || $detectedMimeType !== $expectedMimeType) {
                    $validator->errors()->add('file', 'File extension does not match the file content. Please ensure the file is not corrupted or renamed.');
                    return;
                }
            }
            
            // Validate title if provided
            if ($this->filled('title')) {
                $title = $this->input('title');
                
                // Check for minimum length
                if (strlen(trim($title)) < 2) {
                    $validator->errors()->add('title', 'Title must be at least 2 characters long.');
                }
                
                // Check for potentially dangerous content
                if (preg_match('/<[^>]*>/', $title)) {
                    $validator->errors()->add('title', 'Title cannot contain HTML tags.');
                }
            }
            
            // Check user's document limits
            $user = auth()->user();
            if ($user && $user->hasReachedDocumentLimit()) {
                $validator->errors()->add('file', 'You have reached your document upload limit. Please delete some documents or contact support.');
            }
            
            // Check user's file size limits
            if ($user && $this->hasFile('file') && $this->file('file')->isValid()) {
                $fileSize = $this->file('file')->getSize();
                $totalSizeLimit = 100 * 1024 * 1024; // 100MB default limit
                
                if ($user->getTotalFileSize() + $fileSize > $totalSizeLimit) {
                    $validator->errors()->add('file', 'This upload would exceed your total storage limit. Please delete some documents first.');
                }
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // Log validation failures for monitoring
        if ($this->hasFile('file')) {
            $file = $this->file('file');
            \Log::warning('Document upload validation failed', [
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'errors' => $validator->errors()->toArray(),
            ]);
        }
        
        parent::failedValidation($validator);
    }
}