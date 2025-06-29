<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * Extended to include relationships with documents and simplifications.
 * Provides methods for user statistics and document management.
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get all documents belonging to the user.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get all simplifications belonging to the user.
     */
    public function simplifications(): HasMany
    {
        return $this->hasMany(Simplification::class);
    }

    /**
     * Get user's recent documents.
     */
    public function recentDocuments(int $limit = 10): HasMany
    {
        return $this->documents()
            ->latest()
            ->limit($limit);
    }

    /**
     * Get user's favorite simplifications.
     */
    public function favoriteSimplifications(): HasMany
    {
        return $this->simplifications()
            ->where('is_favorite', true)
            ->where('status', Simplification::STATUS_COMPLETED);
    }

    /**
     * Get user's completed simplifications.
     */
    public function completedSimplifications(): HasMany
    {
        return $this->simplifications()
            ->where('status', Simplification::STATUS_COMPLETED);
    }

    /**
     * Get user's public simplifications.
     */
    public function publicSimplifications(): HasMany
    {
        return $this->simplifications()
            ->where('is_public', true)
            ->where('status', Simplification::STATUS_COMPLETED);
    }

    /**
     * Get user's processed documents.
     */
    public function processedDocuments(): HasMany
    {
        return $this->documents()
            ->where('status', Document::STATUS_COMPLETED);
    }

    /**
     * Get user's documents that are ready for processing.
     */
    public function documentsReadyForProcessing(): HasMany
    {
        return $this->documents()
            ->where('status', Document::STATUS_UPLOADED);
    }

    /**
     * Get user's documents that are currently being processed.
     */
    public function documentsInProcessing(): HasMany
    {
        return $this->documents()
            ->where('status', Document::STATUS_PROCESSING);
    }

    /**
     * Get user's documents that failed processing.
     */
    public function failedDocuments(): HasMany
    {
        return $this->documents()
            ->where('status', Document::STATUS_FAILED);
    }

    /**
     * Get total number of documents uploaded by user.
     */
    public function getTotalDocumentsCount(): int
    {
        return $this->documents()->count();
    }

    /**
     * Get total number of completed simplifications by user.
     */
    public function getTotalSimplificationsCount(): int
    {
        return $this->completedSimplifications()->count();
    }

    /**
     * Get total number of favorite simplifications by user.
     */
    public function getFavoritesCount(): int
    {
        return $this->favoriteSimplifications()->count();
    }

    /**
     * Get total file size of all user documents in bytes.
     */
    public function getTotalFileSize(): int
    {
        return $this->documents()->sum('file_size');
    }

    /**
     * Get formatted total file size.
     */
    public function getFormattedTotalFileSize(): string
    {
        $bytes = $this->getTotalFileSize();
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get total AI processing cost for user.
     */
    public function getTotalProcessingCost(): float
    {
        return $this->simplifications()
            ->whereNotNull('processing_cost')
            ->sum('processing_cost');
    }

    /**
     * Get formatted total processing cost.
     */
    public function getFormattedTotalCost(): string
    {
        return '$' . number_format($this->getTotalProcessingCost(), 2);
    }

    /**
     * Get total tokens used by user.
     */
    public function getTotalTokensUsed(): int
    {
        return $this->simplifications()
            ->whereNotNull('tokens_used')
            ->sum('tokens_used');
    }

    /**
     * Get average user rating for simplifications.
     */
    public function getAverageRating(): ?float
    {
        $ratings = $this->simplifications()
            ->whereNotNull('user_rating')
            ->pluck('user_rating');

        if ($ratings->isEmpty()) {
            return null;
        }

        return round($ratings->average(), 1);
    }

    /**
     * Get user's document processing success rate as percentage.
     */
    public function getProcessingSuccessRate(): float
    {
        $totalDocuments = $this->documents()->count();
        
        if ($totalDocuments === 0) {
            return 0;
        }

        $successfulDocuments = $this->processedDocuments()->count();
        
        return round(($successfulDocuments / $totalDocuments) * 100, 1);
    }

    /**
     * Get user's most used AI model.
     */
    public function getMostUsedAiModel(): ?string
    {
        return $this->simplifications()
            ->selectRaw('ai_model, COUNT(*) as count')
            ->groupBy('ai_model')
            ->orderByDesc('count')
            ->first()?->ai_model;
    }

    /**
     * Get user's preferred complexity level based on usage.
     */
    public function getPreferredComplexityLevel(): ?string
    {
        return $this->simplifications()
            ->selectRaw('complexity_level, COUNT(*) as count')
            ->groupBy('complexity_level')
            ->orderByDesc('count')
            ->first()?->complexity_level;
    }

    /**
     * Check if user has reached document upload limit.
     */
    public function hasReachedDocumentLimit(int $limit = 100): bool
    {
        return $this->documents()->count() >= $limit;
    }

    /**
     * Check if user has reached file size limit.
     */
    public function hasReachedFileSizeLimit(int $limitInBytes = 100 * 1024 * 1024): bool // 100MB default
    {
        return $this->getTotalFileSize() >= $limitInBytes;
    }

    /**
     * Get user dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        return [
            'total_documents' => $this->getTotalDocumentsCount(),
            'total_simplifications' => $this->getTotalSimplificationsCount(),
            'favorites_count' => $this->getFavoritesCount(),
            'total_file_size' => $this->getFormattedTotalFileSize(),
            'total_cost' => $this->getFormattedTotalCost(),
            'success_rate' => $this->getProcessingSuccessRate(),
            'average_rating' => $this->getAverageRating(),
            'most_used_model' => $this->getMostUsedAiModel(),
            'preferred_complexity' => $this->getPreferredComplexityLevel(),
            'documents_in_processing' => $this->documentsInProcessing()->count(),
            'failed_documents' => $this->failedDocuments()->count(),
        ];
    }

    /**
     * Get user activity summary for a specific period.
     */
    public function getActivitySummary(\Carbon\Carbon $since = null): array
    {
        if ($since === null) {
            $since = now()->subMonth(); // Default to last month
        }

        return [
            'documents_uploaded' => $this->documents()->where('created_at', '>=', $since)->count(),
            'simplifications_created' => $this->simplifications()->where('created_at', '>=', $since)->count(),
            'favorites_added' => $this->simplifications()
                ->where('is_favorite', true)
                ->where('updated_at', '>=', $since)
                ->count(),
            'public_shares' => $this->simplifications()
                ->where('is_public', true)
                ->where('updated_at', '>=', $since)
                ->count(),
        ];
    }
}