<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSimplificationRequest;
use App\Http\Requests\UpdateSimplificationRequest;
use App\Jobs\GenerateSimplification;
use App\Models\Document;
use App\Models\Simplification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Simplification Controller
 * 
 * Handles creation, management, and sharing of AI-generated cat story simplifications
 * for the Cat Document Simplifier application.
 */
class SimplificationController extends Controller
{
    /**
     * SimplificationController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['public']);
        $this->middleware('simplification.ownership')->only(['show', 'edit', 'update', 'destroy', 'download']);
    }

    /**
     * Display a listing of the user's simplifications.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get filter parameters
        $status = $request->get('status');
        $complexity = $request->get('complexity');
        $model = $request->get('model');
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $favorites = $request->boolean('favorites');
        
        // Build query
        $query = $user->simplifications()->with(['document']);
        
        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($complexity) {
            $query->where('complexity_level', $complexity);
        }
        
        if ($model) {
            $query->where('ai_model', $model);
        }
        
        if ($favorites) {
            $query->where('is_favorite', true);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('simplified_title', 'like', "%{$search}%")
                  ->orWhere('cat_story', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhereHas('document', function ($docQuery) use ($search) {
                      $docQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }
        
        // Apply sorting
        $allowedSorts = ['created_at', 'processed_at', 'user_rating', 'readability_score', 'download_count'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }
        
        $simplifications = $query->paginate(12)->withQueryString();
        
        // Get filter options
        $statusOptions = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
        ];
        
        $complexityOptions = Simplification::getComplexityLevels();
        $modelOptions = Simplification::getAvailableModels();
        
        return view('simplifications.index', compact(
            'simplifications',
            'statusOptions',
            'complexityOptions',
            'modelOptions',
            'status',
            'complexity',
            'model',
            'search',
            'sort',
            'direction',
            'favorites'
        ));
    }

    /**
     * Show the form for creating a new simplification.
     */
    public function create(Request $request): View
    {
        $documentId = $request->get('document_id');
        $document = null;
        
        if ($documentId) {
            $document = Document::where('id', $documentId)
                ->where('user_id', $request->user()->id)
                ->where('status', Document::STATUS_COMPLETED)
                ->first();
                
            if (!$document) {
                abort(404, 'Document not found or not ready for simplification.');
            }
        }
        
        // Get user's processed documents if no specific document provided
        $documents = $document ? collect([$document]) : 
            $request->user()->processedDocuments()->latest()->get();
        
        $complexityLevels = Simplification::getComplexityLevels();
        $aiModels = Simplification::getAvailableModels();
        
        return view('simplifications.create', compact(
            'documents',
            'document',
            'complexityLevels',
            'aiModels'
        ));
    }

    /**
     * Store a newly created simplification.
     */
    public function store(CreateSimplificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $documentId = $request->input('document_id');
        
        // Verify document belongs to user and is processed
        $document = $user->processedDocuments()->findOrFail($documentId);
        
        try {
            // Create simplification record
            $simplification = Simplification::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'ai_model' => $request->input('ai_model'),
                'complexity_level' => $request->input('complexity_level'),
                'processing_parameters' => $this->buildProcessingParameters($request),
                'status' => Simplification::STATUS_PENDING,
            ]);
            
            // Dispatch job to generate simplification
            GenerateSimplification::dispatch($simplification);
            
            return redirect()
                ->route('simplifications.show', $simplification)
                ->with('success', 'Simplification started! Your cat story is being generated.');
                
        } catch (\Exception $e) {
            \Log::error('Simplification creation failed', [
                'user_id' => $user->id,
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['creation' => 'Failed to start simplification. Please try again.']);
        }
    }

    /**
     * Display the specified simplification.
     */
    public function show(Simplification $simplification): View
    {
        $simplification->load(['document', 'user']);
        
        return view('simplifications.show', compact('simplification'));
    }

    /**
     * Show the form for editing the specified simplification.
     */
    public function edit(Simplification $simplification): View
    {
        if (!$simplification->isCompleted()) {
            return redirect()
                ->route('simplifications.show', $simplification)
                ->withErrors(['edit' => 'Only completed simplifications can be edited.']);
        }
        
        return view('simplifications.edit', compact('simplification'));
    }

    /**
     * Update the specified simplification.
     */
    public function update(UpdateSimplificationRequest $request, Simplification $simplification): RedirectResponse
    {
        $simplification->update($request->validated());
        
        return redirect()
            ->route('simplifications.show', $simplification)
            ->with('success', 'Simplification updated successfully.');
    }

    /**
     * Remove the specified simplification from storage.
     */
    public function destroy(Simplification $simplification): RedirectResponse
    {
        try {
            $simplification->delete();
            
            return redirect()
                ->route('simplifications.index')
                ->with('success', 'Simplification deleted successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Simplification deletion failed', [
                'simplification_id' => $simplification->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['delete' => 'Failed to delete simplification. Please try again.']);
        }
    }

    /**
     * Download the simplification as a text file.
     */
    public function download(Simplification $simplification): Response
    {
        if (!$simplification->isCompleted()) {
            abort(404, 'Simplification not ready for download.');
        }
        
        // Track download
        $simplification->incrementDownloadCount();
        
        $content = $this->formatForDownload($simplification);
        $filename = Str::slug($simplification->simplified_title ?? 'cat-story') . '.txt';
        
        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Toggle favorite status of a simplification.
     */
    public function toggleFavorite(Simplification $simplification): RedirectResponse
    {
        $simplification->toggleFavorite();
        
        $message = $simplification->is_favorite ? 
            'Added to favorites!' : 
            'Removed from favorites.';
        
        return back()->with('success', $message);
    }

    /**
     * Rate a simplification.
     */
    public function rate(Request $request, Simplification $simplification): RedirectResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $simplification->update([
            'user_rating' => $request->input('rating'),
            'user_notes' => $request->input('notes'),
        ]);
        
        return back()->with('success', 'Rating saved successfully!');
    }

    /**
     * Make a simplification public and generate share token.
     */
    public function makePublic(Simplification $simplification): RedirectResponse
    {
        if (!$simplification->isCompleted()) {
            return back()->withErrors(['share' => 'Only completed simplifications can be shared publicly.']);
        }
        
        $shareToken = $simplification->makePublic();
        $shareUrl = route('simplifications.public', $shareToken);
        
        return back()->with('success', 'Simplification is now public! Share URL: ' . $shareUrl);
    }

    /**
     * Make a simplification private.
     */
    public function makePrivate(Simplification $simplification): RedirectResponse
    {
        $simplification->makePrivate();
        
        return back()->with('success', 'Simplification is now private.');
    }

    /**
     * Display a public simplification.
     */
    public function public(string $shareToken): View
    {
        $simplification = Simplification::where('share_token', $shareToken)
            ->where('is_public', true)
            ->where('status', Simplification::STATUS_COMPLETED)
            ->with(['document', 'user'])
            ->firstOrFail();
        
        return view('simplifications.public', compact('simplification'));
    }

    /**
     * Display user's favorite simplifications.
     */
    public function favorites(Request $request): View
    {
        $user = $request->user();
        
        $search = $request->get('search');
        $sort = $request->get('sort', 'updated_at');
        $direction = $request->get('direction', 'desc');
        
        $query = $user->favoriteSimplifications()->with(['document']);
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('simplified_title', 'like', "%{$search}%")
                ->orWhere('cat_story', 'like', "%{$search}%")
                ->orWhereHas('document', function ($docQuery) use ($search) {
                    $docQuery->where('title', 'like', "%{$search}%");
                });
            });
        }
        
        $allowedSorts = ['updated_at', 'created_at', 'user_rating', 'readability_score'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }
        
        $favorites = $query->paginate(12)->withQueryString();
        
        // Calculate statistics for the view
        $favoriteSimplifications = $user->favoriteSimplifications();
        
        // Calculate average rating for favorites
        $averageRating = $favoriteSimplifications
            ->whereNotNull('user_rating')
            ->avg('user_rating');
        
        // Calculate total downloads for favorites
        $totalDownloads = $favoriteSimplifications
            ->sum('download_count');
        
        // Count public favorites
        $publicCount = $favoriteSimplifications
            ->where('is_public', true)
            ->count();
        
        return view('simplifications.favorites', compact(
            'favorites', 
            'search', 
            'sort', 
            'direction',
            'averageRating',
            'totalDownloads',
            'publicCount'
        ));
    }

    /**
     * Get simplification processing status via AJAX.
     */
    public function status(Simplification $simplification): array
    {
        return [
            'id' => $simplification->id,
            'status' => $simplification->status,
            'is_pending' => $simplification->isPending(),
            'is_processing' => $simplification->isProcessing(),
            'is_completed' => $simplification->isCompleted(),
            'has_failed' => $simplification->hasFailed(),
            'processing_error' => $simplification->processing_error,
            'processed_at' => $simplification->processed_at?->toISOString(),
            'tokens_used' => $simplification->tokens_used,
            'processing_cost' => $simplification->getFormattedCost(),
            'processing_time' => $simplification->getFormattedProcessingTime(),
            'readability_score' => $simplification->readability_score,
            'word_count' => $simplification->getWordCount(),
            'estimated_reading_time' => $simplification->getEstimatedReadingTime(),
        ];
    }

    /**
     * Regenerate a failed simplification.
     */
    public function regenerate(Simplification $simplification): RedirectResponse
    {
        if (!$simplification->hasFailed()) {
            return back()->withErrors(['regenerate' => 'Only failed simplifications can be regenerated.']);
        }
        
        // Reset simplification status and dispatch job
        $simplification->update([
            'status' => Simplification::STATUS_PENDING,
            'processing_error' => null,
        ]);
        
        GenerateSimplification::dispatch($simplification);
        
        return back()->with('success', 'Simplification regeneration started.');
    }

    /**
     * Build processing parameters from request.
     */
    protected function buildProcessingParameters(CreateSimplificationRequest $request): array
    {
        $complexity = $request->input('complexity_level');
        $model = $request->input('ai_model');
        
        // Set parameters based on complexity level
        $baseParams = match($complexity) {
            Simplification::COMPLEXITY_BASIC => [
                'temperature' => 0.6,
                'max_tokens' => 1500,
                'top_p' => 0.9,
            ],
            Simplification::COMPLEXITY_INTERMEDIATE => [
                'temperature' => 0.7,
                'max_tokens' => 2000,
                'top_p' => 0.9,
            ],
            Simplification::COMPLEXITY_ADVANCED => [
                'temperature' => 0.8,
                'max_tokens' => 2500,
                'top_p' => 0.95,
            ],
            default => [
                'temperature' => 0.7,
                'max_tokens' => 2000,
                'top_p' => 0.9,
            ],
        };
        
        // Adjust for model type
        if ($model === Simplification::MODEL_GPT_4) {
            $baseParams['max_tokens'] = min($baseParams['max_tokens'] * 1.2, 4000);
        }
        
        return array_merge($baseParams, [
            'frequency_penalty' => 0.1,
            'presence_penalty' => 0.1,
        ]);
    }

    /**
     * Format simplification content for download.
     */
    protected function formatForDownload(Simplification $simplification): string
    {
        $content = [];
        
        // Header
        $content[] = "Cat Document Simplifier";
        $content[] = str_repeat("=", 50);
        $content[] = "";
        
        // Metadata
        $content[] = "Title: " . ($simplification->simplified_title ?? 'Untitled Cat Story');
        $content[] = "Original Document: " . $simplification->document->title;
        $content[] = "Complexity Level: " . $simplification->getComplexityDisplayName();
        $content[] = "AI Model: " . $simplification->getModelDisplayName();
        $content[] = "Generated: " . $simplification->processed_at?->format('F j, Y g:i A');
        $content[] = "Word Count: " . $simplification->getWordCount();
        $content[] = "Reading Time: " . $simplification->getEstimatedReadingTime() . " minutes";
        
        if ($simplification->readability_score) {
            $content[] = "Readability Score: " . $simplification->readability_score . "/10";
        }
        
        if ($simplification->user_rating) {
            $content[] = "Your Rating: " . str_repeat("★", $simplification->user_rating) . str_repeat("☆", 5 - $simplification->user_rating);
        }
        
        $content[] = "";
        $content[] = str_repeat("-", 50);
        $content[] = "";
        
        // Summary (if available)
        if ($simplification->summary) {
            $content[] = "Summary:";
            $content[] = $simplification->summary;
            $content[] = "";
            $content[] = str_repeat("-", 30);
            $content[] = "";
        }
        
        // Cat Story
        $content[] = "Cat Story:";
        $content[] = "";
        $content[] = $simplification->cat_story;
        $content[] = "";
        
        // Key Concepts (if available)
        if ($simplification->key_concepts) {
            $content[] = str_repeat("-", 30);
            $content[] = "";
            $content[] = "Key Concepts:";
            foreach ($simplification->key_concepts as $concept) {
                $content[] = "• " . ucfirst($concept);
            }
            $content[] = "";
        }
        
        // User Notes (if available)
        if ($simplification->user_notes) {
            $content[] = str_repeat("-", 30);
            $content[] = "";
            $content[] = "Your Notes:";
            $content[] = $simplification->user_notes;
            $content[] = "";
        }
        
        // Footer
        $content[] = str_repeat("=", 50);
        $content[] = "Generated by Cat Document Simplifier";
        $content[] = "Making complex documents purr-fectly simple!";
        
        return implode("\n", $content);
    }
}