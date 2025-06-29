<?php

namespace App\Http\Middleware;

use App\Models\Simplification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check Simplification Ownership Middleware
 * 
 * Ensures that users can only access simplifications they own.
 * Protects simplification routes from unauthorized access.
 */
class CheckSimplificationOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the simplification from route parameters
        $simplification = $request->route('simplification');
        
        // If no simplification in route, continue (might be index route)
        if (!$simplification) {
            return $next($request);
        }
        
        // If simplification is not a Simplification model instance, try to resolve it
        if (!$simplification instanceof Simplification) {
            $simplification = Simplification::findOrFail($simplification);
        }
        
        // Check if user owns the simplification
        if ($simplification->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to access this simplification.');
        }
        
        // Bind the simplification back to the route for consistency
        $request->route()->setParameter('simplification', $simplification);
        
        return $next($request);
    }
}