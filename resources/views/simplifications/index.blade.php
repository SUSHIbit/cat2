<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🐱 My Cat Stories
            </h2>
            <a href="{{ route('simplifications.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <span class="mr-2">🐱</span>
                Create New Story
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filter & Search Bar -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('simplifications.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <!-- Search -->
                            <div class="md:col-span-2">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Stories</label>
                                <input 
                                    type="text" 
                                    id="search" 
                                    name="search" 
                                    value="{{ $search }}"
                                    placeholder="Search by title, content, or document..."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>
                            
                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select 
                                    id="status" 
                                    name="status"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">All Status</option>
                                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="processing" {{ $status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                            
                            <!-- Complexity Filter -->
                            <div>
                                <label for="complexity" class="block text-sm font-medium text-gray-700 mb-1">Complexity</label>
                                <select 
                                    id="complexity" 
                                    name="complexity"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">All Levels</option>
                                    <option value="basic" {{ $complexity === 'basic' ? 'selected' : '' }}>Basic</option>
                                    <option value="intermediate" {{ $complexity === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                    <option value="advanced" {{ $complexity === 'advanced' ? 'selected' : '' }}>Advanced</option>
                                </select>
                            </div>
                            
                            <!-- Sort -->
                            <div>
                                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select 
                                    id="sort" 
                                    name="sort"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="created_at" {{ $sort === 'created_at' ? 'selected' : '' }}>Date Created</option>
                                    <option value="title" {{ $sort === 'title' ? 'selected' : '' }}>Title</option>
                                    <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Rating</option>
                                    <option value="downloads" {{ $sort === 'downloads' ? 'selected' : '' }}>Downloads</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex space-x-2">
                                <button 
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Apply Filters
                                </button>
                                
                                @if($search || $status || $complexity)
                                    <a 
                                        href="{{ route('simplifications.index') }}"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Clear Filters
                                    </a>
                                @endif
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="flex space-x-2">
                                <a 
                                    href="{{ route('simplifications.favorites') }}"
                                    class="inline-flex items-center px-3 py-2 border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <span class="mr-1">❤️</span>
                                    Favorites
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Simplifications Grid -->
            @if($simplifications->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($simplifications as $simplification)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow" x-data="{ showPreview: false }">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-lg">🐱</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">
                                            {{ $simplification->simplified_title ?: 'Cat Story' }}
                                        </h3>
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ $simplification->document->title }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Status & Favorite -->
                                <div class="flex items-center space-x-2">
                                    @if($simplification->is_favorite)
                                        <span class="text-red-500 text-lg">❤️</span>
                                    @endif
                                    
                                    @switch($simplification->status)
                                        @case('completed')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ✅ Complete
                                            </span>
                                            @break
                                        @case('processing')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                ⏳ Processing
                                            </span>
                                            @break
                                        @case('failed')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                ❌ Failed
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                📝 Pending
                                            </span>
                                    @endswitch
                                </div>
                            </div>

                            <!-- Meta Information -->
                            <div class="flex items-center text-xs text-gray-500 space-x-4 mb-3">
                                <span class="capitalize">{{ $simplification->getComplexityDisplayName() }}</span>
                                <span>{{ $simplification->getModelDisplayName() }}</span>
                                <span>{{ $simplification->created_at->diffForHumans() }}</span>
                            </div>

                            <!-- Story Preview -->
                            @if($simplification->isCompleted())
                                <div class="mb-4">
                                    <div class="text-sm text-gray-600 line-clamp-3" x-show="!showPreview">
                                        {{ Str::limit($simplification->cat_story, 150) }}
                                    </div>
                                    <div x-show="showPreview" class="text-sm text-gray-600">
                                        {{ Str::limit($simplification->cat_story, 400) }}
                                    </div>
                                    @if(strlen($simplification->cat_story) > 150)
                                        <button 
                                            @click="showPreview = !showPreview"
                                            class="text-blue-600 hover:text-blue-800 text-xs mt-2"
                                            x-text="showPreview ? 'Show less' : 'Show more'"
                                        ></button>
                                    @endif
                                </div>

                                <!-- Key Concepts -->
                                @if($simplification->key_concepts)
                                    <div class="flex flex-wrap gap-1 mb-4">
                                        @foreach(array_slice($simplification->key_concepts, 0, 3) as $concept)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $concept }}
                                            </span>
                                        @endforeach
                                        @if(count($simplification->key_concepts) > 3)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                +{{ count($simplification->key_concepts) - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <!-- Rating -->
                                @if($simplification->user_rating)
                                    <div class="flex items-center mb-4">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $simplification->user_rating)
                                                <span class="text-yellow-400">⭐</span>
                                            @else
                                                <span class="text-gray-300">⭐</span>
                                            @endif
                                        @endfor
                                        <span class="text-xs text-gray-500 ml-2">{{ $simplification->user_rating }}/5</span>
                                    </div>
                                @endif
                            @endif

                            <!-- Error Message -->
                            @if($simplification->hasFailed() && $simplification->processing_error)
                                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-600">
                                    {{ $simplification->processing_error }}
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex space-x-2">
                                    @if($simplification->isCompleted())
                                        <a 
                                            href="{{ route('simplifications.show', $simplification) }}"
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Read
                                        </a>
                                        <a 
                                            href="{{ route('simplifications.download', $simplification) }}"
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Download
                                        </a>
                                    @elseif($simplification->hasFailed())
                                        <form method="POST" action="{{ route('simplifications.regenerate', $simplification) }}" class="inline">
                                            @csrf
                                            <button 
                                                type="submit"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200"
                                            >
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                                Retry
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Stats -->
                                @if($simplification->isCompleted())
                                    <div class="flex items-center space-x-3 text-xs text-gray-500">
                                        @if($simplification->readability_score)
                                            <span title="Readability Score">📊 {{ $simplification->readability_score }}/10</span>
                                        @endif
                                        @if($simplification->getWordCount())
                                            <span title="Word Count">📝 {{ number_format($simplification->getWordCount()) }}</span>
                                        @endif
                                        @if($simplification->download_count > 0)
                                            <span title="Downloads">⬇️ {{ $simplification->download_count }}</span>
                                        @endif
                                        @if($simplification->is_public)
                                            <span title="Public" class="text-purple-600">🌍</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $simplifications->appends(request()->query())->links() }}
                </div>

            @else
                <!-- Empty State -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <div class="text-6xl mb-4">🐱</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No cat stories found</h3>
                        
                        @if($search || $status || $complexity)
                            <p class="text-gray-500 mb-4">Try adjusting your filters or search terms</p>
                            <a 
                                href="{{ route('simplifications.index') }}"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Clear Filters
                            </a>
                        @else
                            <p class="text-gray-500 mb-4">Create your first cat story to transform complex documents into simple, engaging narratives!</p>
                            <div class="space-y-3">
                                <a 
                                    href="{{ route('simplifications.create') }}"
                                    class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <span class="mr-2">🐱</span>
                                    Create Your First Cat Story
                                </a>
                                <div class="text-sm text-gray-400">
                                    <p>or <a href="{{ route('documents.create') }}" class="text-blue-600 hover:text-blue-800">upload a document</a> first</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Auto-refresh for processing simplifications -->
    <script>
        @php
            $processingCount = $simplifications->where('status', 'processing')->count();
        @endphp
        
        @if($processingCount > 0)
        // Auto-refresh page every 30 seconds if there are simplifications being processed
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        @endif
    </script>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
            {{ session('error') }}
        </div>
    @endif
</x-app-layout>