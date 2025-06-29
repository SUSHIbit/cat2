<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Simplification Request
 * 
 * Validates simplification update requests allowing users to modify
 * titles, notes, and ratings for their completed simplifications.
 */
class UpdateSimplificationRequest extends FormRequest
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
            'simplified_title' => [
                'nullable',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\w\s\-\(\)\[\]\.!?]+$/', // Allow alphanumeric, spaces, and common punctuation
            ],
            'user_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'user_rating' => [
                'nullable',
                'integer',
                'min:1',
                'max:5',
            ],
            'is_favorite' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'simplified_title.min' => 'Title must be at least 2 characters long.',
            'simplified_title.max' => 'Title must not exceed 255 characters.',
            'simplified_title.regex' => 'Title contains invalid characters. Please use only letters, numbers, spaces, and basic punctuation.',
            'user_notes.max' => 'Notes must not exceed 1000 characters.',
            'user_rating.integer' => 'Rating must be a number.',
            'user_rating.min' => 'Rating must be at least 1 star.',
            'user_rating.max' => 'Rating must not exceed 5 stars.',
            'is_favorite.boolean' => 'Favorite status must be true or false.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'simplified_title' => 'title',
            'user_notes' => 'notes',
            'user_rating' => 'rating',
            'is_favorite' => 'favorite status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from text inputs
        if ($this->has('simplified_title')) {
            $this->merge([
                'simplified_title' => trim($this->input('simplified_title')),
            ]);
        }
        
        if ($this->has('user_notes')) {
            $this->merge([
                'user_notes' => trim($this->input('user_notes')),
            ]);
        }
        
        // Convert is_favorite to boolean
        if ($this->has('is_favorite')) {
            $this->merge([
                'is_favorite' => filter_var($this->input('is_favorite'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $title = $this->input('simplified_title');
            
            // Check for potentially dangerous content in title
            if ($title && preg_match('/<[^>]*>/', $title)) {
                $validator->errors()->add('simplified_title', 'Title cannot contain HTML tags.');
            }
            
            // Check for script injection attempts in title
            if ($title && preg_match('/script|javascript|vbscript|onload|onerror/i', $title)) {
                $validator->errors()->add('simplified_title', 'Title contains prohibited content.');
            }
            
            $notes = $this->input('user_notes');
            
            // Basic HTML/script check for notes
            if ($notes && preg_match('/<script|javascript:|vbscript:|onload=|onerror=/i', $notes)) {
                $validator->errors()->add('user_notes', 'Notes contain prohibited content.');
            }
        });
    }
}