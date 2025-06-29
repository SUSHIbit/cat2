<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Document Model
 * 
 * Represents uploaded documents that will be processed by AI for simplification.
 * Handles file metadata, content extraction, and processing status tracking.
 * 
 * @property int $id
 * @property int $user_id
 * @property string $original_filename
 * @property string $stored_filename
 * @property string $file_path
 * @property string $mime_type
 * @property int $file_size
 * @property string $file_hash
 * @property string|null $title
 * @property string|null $description
 * @property array|null $metadata
 * @property string $status
 * @property string|null $processing_error
 * @property \Carbon\Carbon|null $processed_at
 * @property string|null $extracted_content
 * @property array|null $content_statistics
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Document extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'original_filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'file_hash',
        'title',
        'description',
        'metadata',
        'status',
        'processing_error',
        'processed_at',
        'extracted_content',
        'content_statistics',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'content_statistics' => 'array',
        'processed_at' => 'datetime',
        'file_size' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'file_hash',
        'file_path',
    ];

    /**
     * Document processing status constants
     */
    public const STATUS_UPLOADED = 'uploaded';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * Supported file types with their MIME types
     */
    public const SUPPORTED_TYPES = [
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    /**
     * Maximum file size in bytes (10MB)
     */
    public const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Get the user that owns the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all simplifications for this document.
     */
    public function simplifications(): HasMany
    {
        return $this->hasMany(Simplification::class);
    }

    /**
     * Get the latest completed simplification.
     */
    public function latestSimplification(): HasMany
    {
        return $this->simplifications()
            ->where('status', Simplification::STATUS_COMPLETED)
            ->latest();
    }

    /**
     * Get the favorite simplifications for this document.
     */
    public function favoriteSimplifications(): HasMany
    {
        return $this->simplifications()
            ->where('is_favorite', true)
            ->where('status', Simplification::STATUS_COMPLETED);
    }

    /**
     * Scope to filter documents by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get documents that are ready for processing.
     */
    public function scopeReadyForProcessing($query)
    {
        return $query->where('status', self::STATUS_UPLOADED);
    }

    /**
     * Scope to get completed documents.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Check if the document is processed successfully.
     */
    public function isProcessed(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the document is currently being processed.
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if the document processing failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the document can be processed.
     */
    public function canBeProcessed(): bool
    {
        return in_array($this->status, [self::STATUS_UPLOADED, self::STATUS_FAILED]);
    }

    /**
     * Mark the document as processing.
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => self::STATUS_PROCESSING,
            'processing_error' => null,
        ]);
    }

    /**
     * Mark the document as completed.
     */
    public function markAsCompleted(string $extractedContent = null): bool
    {
        $data = [
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
            'processing_error' => null,
        ];

        if ($extractedContent !== null) {
            $data['extracted_content'] = $extractedContent;
            $data['content_statistics'] = $this->calculateContentStatistics($extractedContent);
        }

        return $this->update($data);
    }

    /**
     * Mark the document as failed with an error message.
     */
    public function markAsFailed(string $error): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'processing_error' => $error,
        ]);
    }

    /**
     * Get the file extension from the original filename.
     */
    public function getFileExtension(): string
    {
        return strtolower(pathinfo($this->original_filename, PATHINFO_EXTENSION));
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the full file URL.
     */
    public function getFileUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the absolute file path.
     */
    public function getAbsoluteFilePath(): string
    {
        return Storage::path($this->file_path);
    }

    /**
     * Generate a unique stored filename.
     */
    public static function generateStoredFilename(string $originalFilename): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        return Str::uuid() . '.' . $extension;
    }

    /**
     * Check if the file type is supported.
     */
    public static function isSupportedType(string $mimeType): bool
    {
        return in_array($mimeType, self::SUPPORTED_TYPES);
    }

    /**
     * Get supported file extensions.
     */
    public static function getSupportedExtensions(): array
    {
        return array_keys(self::SUPPORTED_TYPES);
    }

    /**
     * Calculate content statistics from extracted text.
     */
    protected function calculateContentStatistics(string $content): array
    {
        $wordCount = str_word_count($content);
        $characterCount = strlen($content);
        $characterCountNoSpaces = strlen(str_replace(' ', '', $content));
        $lineCount = substr_count($content, "\n") + 1;
        $paragraphCount = count(array_filter(explode("\n\n", $content)));

        return [
            'word_count' => $wordCount,
            'character_count' => $characterCount,
            'character_count_no_spaces' => $characterCountNoSpaces,
            'line_count' => $lineCount,
            'paragraph_count' => $paragraphCount,
            'estimated_reading_time_minutes' => max(1, round($wordCount / 200)), // 200 words per minute
        ];
    }

    /**
     * Delete the physical file when the model is deleted.
     */
    public function deleteFile(): bool
    {
        if (Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }
        
        return true;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate title from filename if not provided
        static::creating(function ($document) {
            if (empty($document->title)) {
                $document->title = pathinfo($document->original_filename, PATHINFO_FILENAME);
            }
        });

        // Clean up file when document is force deleted
        static::forceDeleted(function ($document) {
            $document->deleteFile();
        });
    }
}