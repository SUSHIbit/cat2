<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Jobs\ProcessDocumentContent;
use App\Models\Document;
use App\Services\DocumentParsingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Document Controller
 * 
 * Handles document upload, management, processing, and file operations
 * for the Cat Document Simplifier application.
 */
class DocumentController extends Controller
{
    /**
     * DocumentController constructor.
     */
    public function __construct(
        protected DocumentParsingService $documentParsingService
    ) {
        $this->middleware('auth');
        $this->middleware('document.ownership')->only(['show', 'edit', 'update', 'destroy', 'download']);
    }

    /**
     * Display a listing of the user's documents.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get filter parameters
        $status = $request->get('status');
        $type = $request->get('type');
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        // Build query
        $query = $user->documents()->with(['simplifications' => function ($query) {
            $query->where('status', 'completed')->latest()->limit(1);
        }]);
        
        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($type) {
            $mimeType = Document::SUPPORTED_TYPES[$type] ?? null;
            if ($mimeType) {
                $query->where('mime_type', $mimeType);
            }
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $allowedSorts = ['created_at', 'title', 'file_size', 'status', 'processed_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }
        
        $documents = $query->paginate(12)->withQueryString();
        
        // Get filter options
        $statusOptions = [
            'uploaded' => 'Uploaded',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'archived' => 'Archived',
        ];
        
        $typeOptions = [
            'pdf' => 'PDF',
            'docx' => 'Word Document',
            'pptx' => 'PowerPoint',
        ];
        
        return view('documents.index', compact(
            'documents',
            'statusOptions',
            'typeOptions',
            'status',
            'type',
            'search',
            'sort',
            'direction'
        ));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create(): View
    {
        return view('documents.create', [
            'maxFileSize' => config('cat-simplifier.uploads.max_file_size'),
            'supportedFormats' => config('cat-simplifier.uploads.supported_formats'),
        ]);
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Check if user has reached document limit
        if ($user->hasReachedDocumentLimit()) {
            return back()->withErrors([
                'file' => 'You have reached your document upload limit. Please delete some documents or upgrade your account.'
            ]);
        }
        
        $file = $request->file('file');
        $title = $request->input('title');
        $description = $request->input('description');
        
        try {
            // Generate file hash for duplicate detection
            $fileHash = hash_file('sha256', $file->getRealPath());
            
            // Check for duplicate files
            $existingDocument = $user->documents()->where('file_hash', $fileHash)->first();
            if ($existingDocument) {
                return back()->withErrors([
                    'file' => 'This file has already been uploaded. Document: ' . $existingDocument->title
                ]);
            }
            
            // Generate unique filename
            $storedFilename = Document::generateStoredFilename($file->getClientOriginalName());
            $filePath = $file->storeAs(config('cat-simplifier.uploads.storage_path'), $storedFilename);
            
            // Create document record
            $document = Document::create([
                'user_id' => $user->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'file_hash' => $fileHash,
                'title' => $title ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'description' => $description,
                'status' => Document::STATUS_UPLOADED,
            ]);
            
            // Dispatch job to process document content
            ProcessDocumentContent::dispatch($document);
            
            return redirect()
                ->route('documents.show', $document)
                ->with('success', 'Document uploaded successfully! Content extraction is in progress.');
                
        } catch (\Exception $e) {
            // Clean up uploaded file if database operation fails
            if (isset($filePath) && Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            
            \Log::error('Document upload failed', [
                'user_id' => $user->id,
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['file' => 'Upload failed. Please try again.']);
        }
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document): View
    {
        $document->load([
            'simplifications' => function ($query) {
                $query->latest();
            }
        ]);
        
        return view('documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified document.
     */
    public function edit(Document $document): View
    {
        return view('documents.edit', compact('document'));
    }

    /**
     * Update the specified document.
     */
    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $document->update($request->validated());
        
        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document): RedirectResponse
    {
        try {
            // Delete the physical file
            $document->deleteFile();
            
            // Delete the database record (will cascade to simplifications)
            $document->delete();
            
            return redirect()
                ->route('documents.index')
                ->with('success', 'Document deleted successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Document deletion failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['delete' => 'Failed to delete document. Please try again.']);
        }
    }

    /**
     * Download the original document file.
     */
    public function download(Document $document): Response
    {
        if (!Storage::exists($document->file_path)) {
            abort(404, 'File not found.');
        }
        
        return Storage::download(
            $document->file_path,
            $document->original_filename,
            ['Content-Type' => $document->mime_type]
        );
    }

    /**
     * Reprocess a failed document.
     */
    public function reprocess(Document $document): RedirectResponse
    {
        if (!$document->canBeProcessed()) {
            return back()->withErrors(['reprocess' => 'Document cannot be reprocessed in its current state.']);
        }
        
        // Mark document as uploaded and dispatch processing job
        $document->update(['status' => Document::STATUS_UPLOADED]);
        ProcessDocumentContent::dispatch($document);
        
        return back()->with('success', 'Document reprocessing started.');
    }

    /**
     * Archive the specified document.
     */
    public function archive(Document $document): RedirectResponse
    {
        $document->update(['status' => Document::STATUS_ARCHIVED]);
        
        return back()->with('success', 'Document archived successfully.');
    }

    /**
     * Restore an archived document.
     */
    public function restore(Document $document): RedirectResponse
    {
        if ($document->status !== Document::STATUS_ARCHIVED) {
            return back()->withErrors(['restore' => 'Only archived documents can be restored.']);
        }
        
        $status = $document->extracted_content ? Document::STATUS_COMPLETED : Document::STATUS_UPLOADED;
        $document->update(['status' => $status]);
        
        return back()->with('success', 'Document restored successfully.');
    }

    /**
     * Get document processing status via AJAX.
     */
    public function status(Document $document): array
    {
        return [
            'id' => $document->id,
            'status' => $document->status,
            'is_processing' => $document->isProcessing(),
            'is_completed' => $document->isProcessed(),
            'has_failed' => $document->hasFailed(),
            'processing_error' => $document->processing_error,
            'processed_at' => $document->processed_at?->toISOString(),
            'content_statistics' => $document->content_statistics,
            'simplifications_count' => $document->simplifications()->where('status', 'completed')->count(),
        ];
    }

    /**
     * Bulk actions for multiple documents.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,archive,restore',
            'documents' => 'required|array|min:1',
            'documents.*' => 'exists:documents,id',
        ]);
        
        $user = $request->user();
        $action = $request->input('action');
        $documentIds = $request->input('documents');
        
        // Get user's documents that match the IDs
        $documents = $user->documents()->whereIn('id', $documentIds)->get();
        
        if ($documents->isEmpty()) {
            return back()->withErrors(['bulk' => 'No valid documents selected.']);
        }
        
        $count = 0;
        
        try {
            foreach ($documents as $document) {
                switch ($action) {
                    case 'delete':
                        $document->deleteFile();
                        $document->delete();
                        $count++;
                        break;
                        
                    case 'archive':
                        if ($document->status !== Document::STATUS_ARCHIVED) {
                            $document->update(['status' => Document::STATUS_ARCHIVED]);
                            $count++;
                        }
                        break;
                        
                    case 'restore':
                        if ($document->status === Document::STATUS_ARCHIVED) {
                            $status = $document->extracted_content ? Document::STATUS_COMPLETED : Document::STATUS_UPLOADED;
                            $document->update(['status' => $status]);
                            $count++;
                        }
                        break;
                }
            }
            
            $actionText = match($action) {
                'delete' => 'deleted',
                'archive' => 'archived',
                'restore' => 'restored',
            };
            
            return back()->with('success', "{$count} documents {$actionText} successfully.");
            
        } catch (\Exception $e) {
            \Log::error('Bulk action failed', [
                'user_id' => $user->id,
                'action' => $action,
                'document_ids' => $documentIds,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['bulk' => 'Bulk action failed. Please try again.']);
        }
    }
}