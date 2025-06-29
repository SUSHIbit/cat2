<?php

namespace App\Http\Middleware;

use App\Models\Document;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check Document Ownership Middleware
 * 
 * Ensures that users can only access documents they own.
 * Protects document routes from unauthorized access.
 */
class CheckDocumentOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the document from route parameters
        $document = $request->route('document');
        
        // If no document in route, continue (might be index route)
        if (!$document) {
            return $next($request);
        }
        
        // If document is not a Document model instance, try to resolve it
        if (!$document instanceof Document) {
            $document = Document::findOrFail($document);
        }
        
        // Check if user owns the document
        if ($document->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to access this document.');
        }
        
        // Bind the document back to the route for consistency
        $request->route()->setParameter('document', $document);
        
        return $next($request);
    }
}