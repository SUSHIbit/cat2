<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Simplification;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard Controller
 * 
 * Handles the main dashboard view with user statistics, recent documents,
 * and activity summaries for the Cat Document Simplifier application.
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard with user statistics and recent activity.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get dashboard statistics
        $stats = $user->getDashboardStats();
        
        // Get recent documents (last 10)
        $recentDocuments = $user->recentDocuments(10)
            ->with(['simplifications' => function ($query) {
                $query->where('status', Simplification::STATUS_COMPLETED)
                      ->latest()
                      ->limit(1);
            }])
            ->get();
        
        // Get recent simplifications (last 5)
        $recentSimplifications = $user->completedSimplifications()
            ->with(['document'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Get favorite simplifications (last 5)
        $favoriteSimplifications = $user->favoriteSimplifications()
            ->with(['document'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Get documents ready for processing
        $documentsReadyForProcessing = $user->documentsReadyForProcessing()
            ->latest()
            ->limit(5)
            ->get();
        
        // Get documents currently being processed
        $documentsInProcessing = $user->documentsInProcessing()
            ->with(['simplifications' => function ($query) {
                $query->where('status', Simplification::STATUS_PROCESSING);
            }])
            ->latest()
            ->get();
        
        // Get failed documents
        $failedDocuments = $user->failedDocuments()
            ->latest()
            ->limit(3)
            ->get();
        
        // Get activity summary for last 30 days
        $activitySummary = $user->getActivitySummary(now()->subDays(30));
        
        // Get processing statistics
        $processingStats = $this->getProcessingStats($user);
        
        return view('dashboard.index', compact(
            'stats',
            'recentDocuments',
            'recentSimplifications',
            'favoriteSimplifications',
            'documentsReadyForProcessing',
            'documentsInProcessing',
            'failedDocuments',
            'activitySummary',
            'processingStats'
        ));
    }
    
    /**
     * Get processing statistics for the user.
     */
    protected function getProcessingStats($user): array
    {
        // Get total documents by status
        $documentStats = [
            'uploaded' => $user->documents()->where('status', Document::STATUS_UPLOADED)->count(),
            'processing' => $user->documents()->where('status', Document::STATUS_PROCESSING)->count(),
            'completed' => $user->documents()->where('status', Document::STATUS_COMPLETED)->count(),
            'failed' => $user->documents()->where('status', Document::STATUS_FAILED)->count(),
            'archived' => $user->documents()->where('status', Document::STATUS_ARCHIVED)->count(),
        ];
        
        // Get simplification stats by complexity
        $complexityStats = [
            'basic' => $user->simplifications()->where('complexity_level', Simplification::COMPLEXITY_BASIC)->where('status', Simplification::STATUS_COMPLETED)->count(),
            'intermediate' => $user->simplifications()->where('complexity_level', Simplification::COMPLEXITY_INTERMEDIATE)->where('status', Simplification::STATUS_COMPLETED)->count(),
            'advanced' => $user->simplifications()->where('complexity_level', Simplification::COMPLEXITY_ADVANCED)->where('status', Simplification::STATUS_COMPLETED)->count(),
        ];
        
        // Get AI model usage stats
        $modelStats = [
            'gpt35' => $user->simplifications()->where('ai_model', Simplification::MODEL_GPT_35_TURBO)->where('status', Simplification::STATUS_COMPLETED)->count(),
            'gpt4' => $user->simplifications()->where('ai_model', Simplification::MODEL_GPT_4)->where('status', Simplification::STATUS_COMPLETED)->count(),
        ];
        
        // Get monthly activity for chart (last 6 months)
        $monthlyActivity = $this->getMonthlyActivity($user);
        
        return [
            'documents' => $documentStats,
            'complexity' => $complexityStats,
            'models' => $modelStats,
            'monthly' => $monthlyActivity,
        ];
    }
    
    /**
     * Get monthly activity data for charts.
     */
    protected function getMonthlyActivity($user): array
    {
        $months = [];
        $documentsData = [];
        $simplificationsData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            $months[] = $date->format('M Y');
            
            $documentsData[] = $user->documents()
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
            
            $simplificationsData[] = $user->simplifications()
                ->where('status', Simplification::STATUS_COMPLETED)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
        }
        
        return [
            'months' => $months,
            'documents' => $documentsData,
            'simplifications' => $simplificationsData,
        ];
    }
    
    /**
     * Get quick actions data for the dashboard.
     */
    public function quickActions(Request $request): array
    {
        $user = $request->user();
        
        return [
            'upload_document' => [
                'title' => 'Upload New Document',
                'description' => 'Upload a PDF, Word, or PowerPoint document for simplification',
                'url' => route('documents.create'),
                'icon' => 'upload',
                'enabled' => !$user->hasReachedDocumentLimit(),
            ],
            'view_documents' => [
                'title' => 'My Documents',
                'description' => 'View and manage your uploaded documents',
                'url' => route('documents.index'),
                'icon' => 'folder',
                'count' => $user->getTotalDocumentsCount(),
            ],
            'view_simplifications' => [
                'title' => 'My Cat Stories',
                'description' => 'Browse your generated simplifications',
                'url' => route('simplifications.index'),
                'icon' => 'book',
                'count' => $user->getTotalSimplificationsCount(),
            ],
            'view_favorites' => [
                'title' => 'Favorites',
                'description' => 'Access your favorite simplifications',
                'url' => route('simplifications.favorites'),
                'icon' => 'heart',
                'count' => $user->getFavoritesCount(),
            ],
        ];
    }
}