<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Simplification Model
 * 
 * Represents AI-generated cat stories created from document content.
 * Tracks processing parameters, AI usage, and user feedback.
 * 
 * @property int $id
 * @property int $document_id
 * @property int $user_id
 * @property string $ai_model
 * @property string $complexity_level
 * @property array|null $processing_parameters
 * @property string|null $simplified_title
 * @property string $cat_story
 * @property string|null $summary
 * @property array|null $key_concepts
 * @property string $status
 * @property string|null $processing_error
 * @property \Carbon\Carbon|null $processed_at
 * @property int|null $tokens_used
 * @property float|null $processing_cost
 * @property int|null $processing_time_seconds
 * @property int|null $readability_score
 * @property array|null $quality_metrics
 * @property bool $is_favorite
 * @property int|null $user_rating
 * @property string|null $user_notes
 * @property bool $is_public
 * @property string|null $share_token
 * @property int $download_count
 * @property \Carbon\Carbon|null $last_downloaded_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Simplification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'user_id',
        'ai_model',
        'complexity_level',
        'processing_parameters',
        'simplified_title',
        'cat_story',
        'summary',
        'key_concepts',
        'status',
        'processing_error',
        'processed_at',
        'tokens_used',
        'processing_cost',
        'processing_time_seconds',
        'readability_score',
        'quality_metrics',
        'is_favorite',
        'user_rating',
        'user_notes',
        'is_public',
        'share_token',
        'download_count',
        'last_downloaded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'processing_parameters' => 'array',
        'key_concepts' => 'array',
        'quality_metrics' => 'array',
        'processed_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'is_favorite' => 'boolean',
        'is_public' => 'boolean',
        'tokens_used' => 'integer',
        'processing_cost' => 'decimal:6',
        'processing_time_seconds' => 'integer',
        'readability_score' => 'integer',
        'user_rating' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'share_token',
    ];

    /**
     * Processing status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Complexity level constants
     */
    public const COMPLEXITY_BASIC = 'basic';
    public const COMPLEXITY_INTERMEDIATE = 'intermediate';
    public const COMPLEXITY_ADVANCED = 'advanced';

    /**
     * AI model constants
     */
    public const MODEL_GPT_35_TURBO = 'gpt-3.5-turbo';
    public const MODEL_GPT_4 = 'gpt-4';

    /**
     * Rating constants
     */
    public const MIN_RATING = 1;
    public const MAX_RATING = 5;

    /**
     * Readability score constants
     */
    public const MIN_READABILITY = 1;
    public const MAX_READABILITY = 10;

    /**
     * Get the document that owns the simplification.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user that owns the simplification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter simplifications by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get completed simplifications.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get pending simplifications.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get favorite simplifications.
     */
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope to get public simplifications.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true)
            ->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter by complexity level.
     */
    public function scopeWithComplexity($query, string $complexity)
    {
        return $query->where('complexity_level', $complexity);
    }

    /**
     * Scope to filter by AI model.
     */
    public function scopeWithModel($query, string $model)
    {
        return $query->where('ai_model', $model);
    }

    /**
     * Scope to get highly rated simplifications.
     */
    public function scopeHighlyRated($query, int $minRating = 4)
    {
        return $query->where('user_rating', '>=', $minRating);
    }

    /**
     * Check if the simplification is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the simplification is currently being processed.
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if the simplification processing failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the simplification is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Mark the simplification as processing.
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => self::STATUS_PROCESSING,
            'processing_error' => null,
        ]);
    }

    /**
     * Mark the simplification as completed.
     */
    public function markAsCompleted(array $data = []): bool
    {
        $updateData = array_merge([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
            'processing_error' => null,
        ], $data);

        return $this->update($updateData);
    }

    /**
     * Mark the simplification as failed with an error message.
     */
    public function markAsFailed(string $error): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'processing_error' => $error,
        ]);
    }

    /**
     * Toggle favorite status.
     */
    public function toggleFavorite(): bool
    {
        return $this->update(['is_favorite' => !$this->is_favorite]);
    }

    /**
     * Set user rating.
     */
    public function setRating(int $rating): bool
    {
        if ($rating < self::MIN_RATING || $rating > self::MAX_RATING) {
            return false;
        }

        return $this->update(['user_rating' => $rating]);
    }

    /**
     * Generate a share token for public sharing.
     */
    public function generateShareToken(): string
    {
        $token = Str::random(32);
        $this->update(['share_token' => $token]);
        return $token;
    }

    /**
     * Make the simplification public and generate share token.
     */
    public function makePublic(): string
    {
        $token = $this->generateShareToken();
        $this->update(['is_public' => true]);
        return $token;
    }

    /**
     * Make the simplification private.
     */
    public function makePrivate(): bool
    {
        return $this->update([
            'is_public' => false,
            'share_token' => null,
        ]);
    }

    /**
     * Increment download count.
     */
    public function incrementDownloadCount(): bool
    {
        return $this->update([
            'download_count' => $this->download_count + 1,
            'last_downloaded_at' => now(),
        ]);
    }

    /**
     * Get the estimated reading time for the cat story.
     */
    public function getEstimatedReadingTime(): int
    {
        $wordCount = str_word_count($this->cat_story);
        return max(1, round($wordCount / 200)); // 200 words per minute
    }

    /**
     * Get the word count of the cat story.
     */
    public function getWordCount(): int
    {
        return str_word_count($this->cat_story);
    }

    /**
     * Get complexity level display name.
     */
    public function getComplexityDisplayName(): string
    {
        return match($this->complexity_level) {
            self::COMPLEXITY_BASIC => 'Basic',
            self::COMPLEXITY_INTERMEDIATE => 'Intermediate',
            self::COMPLEXITY_ADVANCED => 'Advanced',
            default => 'Unknown',
        };
    }

    /**
     * Get AI model display name.
     */
    public function getModelDisplayName(): string
    {
        return match($this->ai_model) {
            self::MODEL_GPT_35_TURBO => 'GPT-3.5 Turbo',
            self::MODEL_GPT_4 => 'GPT-4',
            default => $this->ai_model,
        };
    }

    /**
     * Get the share URL for public simplifications.
     */
    public function getShareUrl(): ?string
    {
        if (!$this->is_public || !$this->share_token) {
            return null;
        }

        return route('simplifications.public', $this->share_token);
    }

    /**
     * Get readability score as a percentage.
     */
    public function getReadabilityPercentage(): ?int
    {
        if ($this->readability_score === null) {
            return null;
        }

        return ($this->readability_score / self::MAX_READABILITY) * 100;
    }

    /**
     * Get processing cost formatted as currency.
     */
    public function getFormattedCost(): string
    {
        if ($this->processing_cost === null) {
            return 'N/A';
        }

        return '$' . number_format($this->processing_cost, 4);
    }

    /**
     * Get processing time formatted.
     */
    public function getFormattedProcessingTime(): string
    {
        if ($this->processing_time_seconds === null) {
            return 'N/A';
        }

        if ($this->processing_time_seconds < 60) {
            return $this->processing_time_seconds . 's';
        }

        $minutes = floor($this->processing_time_seconds / 60);
        $seconds = $this->processing_time_seconds % 60;
        
        return $minutes . 'm ' . $seconds . 's';
    }

    /**
     * Get available complexity levels.
     */
    public static function getComplexityLevels(): array
    {
        return [
            self::COMPLEXITY_BASIC => 'Basic',
            self::COMPLEXITY_INTERMEDIATE => 'Intermediate',
            self::COMPLEXITY_ADVANCED => 'Advanced',
        ];
    }

    /**
     * Get available AI models.
     */
    public static function getAvailableModels(): array
    {
        return [
            self::MODEL_GPT_35_TURBO => 'GPT-3.5 Turbo',
            self::MODEL_GPT_4 => 'GPT-4',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate simplified title from document title if not provided
        static::creating(function ($simplification) {
            if (empty($simplification->simplified_title) && $simplification->document) {
                $simplification->simplified_title = 'Cat Story: ' . $simplification->document->title;
            }
        });
    }
}