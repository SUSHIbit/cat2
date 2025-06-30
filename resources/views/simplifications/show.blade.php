<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <a href="{{ route('simplifications.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🐱 {{ $simplification->simplified_title ?: 'Cat Story' }}
                </h2>
                @if($simplification->is_favorite)
                    <span class="ml-3 text-red-500 text-xl">❤️</span>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                @if($simplification->isCompleted())
                    <!-- Favorite Toggle -->
                    <form method="POST" action="{{ route('simplifications.toggle-favorite', $simplification) }}" class="inline">
                        @csrf
                        <button 
                            type="submit"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            @if($simplification->is_favorite)
                                <span class="mr-1">💔</span>
                                Remove Favorite
                            @else
                                <span class="mr-1">❤️</span>
                                Add to Favorites
                            @endif
                        </button>
                    </form>

                    <!-- Download Button -->
                    <a 
                        href="{{ route('simplifications.download', $simplification) }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Story Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex-1">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                    <span class="text-2xl">🐱</span>
                                </div>
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">{{ $simplification->simplified_title ?: 'Cat Story' }}</h1>
                                    <p class="text-gray-600">Based on: <a href="{{ route('documents.show', $simplification->document) }}" class="text-blue-600 hover:text-blue-800">{{ $simplification->document->title }}</a></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-6 text-sm text-gray-500">
                                <span class="capitalize">{{ $simplification->getComplexityDisplayName() }} Level</span>
                                <span>{{ $simplification->getModelDisplayName() }}</span>
                                <span>Created {{ $simplification->created_at->diffForHumans() }}</span>
                                @if($simplification->processed_at)
                                    <span>Processed in {{ $simplification->processing_time_seconds }}s</span>
                                @endif
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="flex-shrink-0">
                            @switch($simplification->status)
                                @case('completed')
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        ✅ Completed
                                    </span>
                                    @break
                                @case('processing')
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        ⏳ Processing
                                    </span>
                                    @break
                                @case('failed')
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        ❌ Failed
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        📝 Pending
                                    </span>
                            @endswitch
                        </div>
                    </div>

                    <!-- Key Concepts -->
                    @if($simplification->key_concepts && count($simplification->key_concepts) > 0)
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Key Concepts Covered</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($simplification->key_concepts as $concept)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $concept }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Processing Status -->
                    @if($simplification->isProcessing())
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-yellow-600 animate-spin mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-yellow-800">Creating Your Cat Story</h4>
                                    <p class="text-sm text-yellow-600">Our AI is working on transforming your document into an engaging cat story. This usually takes 1-3 minutes.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if($simplification->hasFailed())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-red-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-red-800">Story Creation Failed</h4>
                                    @if($simplification->processing_error)
                                        <p class="text-sm text-red-600 mt-1">{{ $simplification->processing_error }}</p>
                                    @endif
                                    <div class="mt-3">
                                        <form method="POST" action="{{ route('simplifications.regenerate', $simplification) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                                Try Again
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cat Story Content -->
            @if($simplification->isCompleted() && $simplification->cat_story)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <div class="prose prose-lg max-w-none">
                            <!-- Story Text -->
                            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-purple-400 p-6 rounded-r-lg mb-8">
                                <div class="text-gray-800 leading-relaxed whitespace-pre-line">{{ $simplification->cat_story }}</div>
                            </div>

                            <!-- Summary -->
                            @if($simplification->summary)
                                <div class="border-t border-gray-200 pt-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">📝 Summary</h3>
                                    <p class="text-gray-700 leading-relaxed">{{ $simplification->summary }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Rating & Feedback -->
            @if($simplification->isCompleted())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rate This Cat Story</h3>
                        
                        <!-- Current Rating Display -->
                        @if($simplification->user_rating)
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">Your current rating:</p>
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $simplification->user_rating)
                                            <span class="text-yellow-400 text-xl">⭐</span>
                                        @else
                                            <span class="text-gray-300 text-xl">⭐</span>
                                        @endif
                                    @endfor
                                    <span class="text-sm text-gray-500 ml-2">{{ $simplification->user_rating }}/5</span>
                                </div>
                            </div>
                        @endif

                        <!-- Rating Form -->
                        <form method="POST" action="{{ route('simplifications.rate', $simplification) }}" x-data="{ rating: {{ $simplification->user_rating ?? 0 }}, notes: '{{ $simplification->user_notes ?? '' }}' }">
                            @csrf
                            
                            <!-- Star Rating -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">How would you rate this cat story?</label>
                                <div class="flex items-center space-x-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button 
                                            type="button"
                                            @click="rating = {{ $i }}"
                                            class="text-2xl transition-colors"
                                            :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-300'"
                                        >
                                            ⭐
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" x-model="rating">
                            </div>

                            <!-- Notes -->
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Notes (Optional)
                                </label>
                                <textarea 
                                    id="notes" 
                                    name="notes" 
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="What did you like about this story? Any suggestions for improvement?"
                                    x-model="notes"
                                >{{ $simplification->user_notes }}</textarea>
                            </div>

                            <button 
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                :disabled="rating === 0"
                            >
                                Save Rating
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Story Statistics -->
            @if($simplification->isCompleted())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Story Statistics</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $simplification->getWordCount() }}</div>
                                <div class="text-sm text-gray-500">Words</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $simplification->getEstimatedReadingTime() }} min</div>
                                <div class="text-sm text-gray-500">Reading Time</div>
                            </div>
                            @if($simplification->readability_score)
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ $simplification->readability_score }}/10</div>
                                    <div class="text-sm text-gray-500">Readability</div>
                                </div>
                            @endif
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ $simplification->download_count }}</div>
                                <div class="text-sm text-gray-500">Downloads</div>
                            </div>
                        </div>

                        <!-- Quality Metrics -->
                        @if($simplification->quality_metrics)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Quality Metrics</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @foreach($simplification->quality_metrics as $metric => $score)
                                        <div class="text-center">
                                            <div class="text-lg font-semibold text-gray-900">{{ number_format($score * 100) }}%</div>
                                            <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $metric)) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Sharing Options -->
            @if($simplification->isCompleted())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sharing Options</h3>
                        
                        @if($simplification->is_public)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-green-800">Story is Public</h4>
                                        <p class="text-sm text-green-600 mt-1">Anyone with the link can view this story</p>
                                        <div class="mt-3 flex items-center space-x-3">
                                            <input 
                                                type="text" 
                                                value="{{ route('simplifications.public', $simplification->share_token) }}"
                                                class="flex-1 text-sm border-green-200 rounded bg-green-50"
                                                readonly
                                                onclick="this.select()"
                                            >
                                            <button 
                                                onclick="copyToClipboard('{{ route('simplifications.public', $simplification->share_token) }}')"
                                                class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700"
                                            >
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <form method="POST" action="{{ route('simplifications.make-private', $simplification) }}" class="inline">
                                @csrf
                                <button 
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Make Private
                                </button>
                            </form>
                        @else
                            <p class="text-gray-600 mb-4">Share this cat story with others by making it public</p>
                            <form method="POST" action="{{ route('simplifications.make-public', $simplification) }}" class="inline">
                                @csrf
                                <button 
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                    </svg>
                                    🌍 Make Public
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Related Documents -->
            @if($relatedSimplifications->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Other Stories from Same Document</h3>
                        <div class="space-y-3">
                            @foreach($relatedSimplifications as $related)
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-sm">🐱</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $related->simplified_title ?: 'Cat Story' }}</p>
                                            <p class="text-sm text-gray-500">{{ $related->getComplexityDisplayName() }} • {{ $related->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if($related->is_favorite)
                                            <span class="text-red-500">❤️</span>
                                        @endif
                                        @if($related->user_rating)
                                            <div class="flex">
                                                @for($i = 1; $i <= $related->user_rating; $i++)
                                                    <span class="text-yellow-400 text-sm">⭐</span>
                                                @endfor
                                            </div>
                                        @endif
                                        <a href="{{ route('simplifications.show', $related) }}" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Auto-refresh for processing simplifications -->
    <script>
        @if($simplification->isProcessing())
        // Auto-refresh page every 15 seconds while processing
        setTimeout(function() {
            window.location.reload();
        }, 15000);
        @endif

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success message
                const message = document.createElement('div');
                message.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
                message.textContent = 'Link copied to clipboard!';
                document.body.appendChild(message);
                setTimeout(() => document.body.removeChild(message), 3000);
            });
        }
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