<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <a href="{{ route('documents.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📄 {{ $document->title }}
                </h2>
            </div>
            <div class="flex space-x-2">
                @if($document->isProcessed())
                    <a href="{{ route('simplifications.create', ['document_id' => $document->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <span class="mr-2">🐱</span>
                        Create Cat Story
                    </a>
                @endif
                <a href="{{ route('documents.edit', $document) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Document Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 mr-4">
                                @switch($document->getFileExtension())
                                    @case('pdf')
                                        <div class="w-16 h-16 bg-red-100 rounded-lg flex items-center justify-center">
                                            <span class="text-lg font-semibold text-red-600">PDF</span>
                                        </div>
                                        @break
                                    @case('docx')
                                        <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <span class="text-lg font-semibold text-blue-600">DOC</span>
                                        </div>
                                        @break
                                    @case('pptx')
                                        <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center">
                                            <span class="text-lg font-semibold text-orange-600">PPT</span>
                                        </div>
                                        @break
                                @endswitch
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $document->title }}</h3>
                                @if($document->description)
                                    <p class="text-gray-600 mb-2">{{ $document->description }}</p>
                                @endif
                                <div class="flex items-center text-sm text-gray-500 space-x-4">
                                    <span>{{ $document->original_filename }}</span>
                                    <span>{{ $document->getFormattedFileSize() }}</span>
                                    <span>Uploaded {{ $document->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="flex-shrink-0">
                            @switch($document->status)
                                @case('completed')
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        ✅ Ready for Simplification
                                    </span>
                                    @break
                                @case('processing')
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        ⏳ Processing Content
                                    </span>
                                    @break
                                @case('failed')
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        ❌ Processing Failed
                                    </span>
                                    @break
                                @case('archived')
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        📦 Archived
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        📄 Uploaded
                                    </span>
                            @endswitch
                        </div>
                    </div>

                    <!-- Processing Status -->
                    @if($document->isProcessing())
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-yellow-600 animate-spin mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Document Statistics -->
            @if($document->isProcessed() && $document->content_statistics)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Document Statistics</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ number_format($document->content_statistics['word_count'] ?? 0) }}</div>
                                <div class="text-sm text-gray-500">Words</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ number_format($document->content_statistics['character_count'] ?? 0) }}</div>
                                <div class="text-sm text-gray-500">Characters</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ number_format($document->content_statistics['paragraph_count'] ?? 0) }}</div>
                                <div class="text-sm text-gray-500">Paragraphs</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ $document->content_statistics['estimated_reading_time_minutes'] ?? 0 }} min</div>
                                <div class="text-sm text-gray-500">Reading Time</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Cat Stories (Simplifications) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Cat Stories</h3>
                        @if($document->isProcessed())
                            <a href="{{ route('simplifications.create', ['document_id' => $document->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <span class="mr-2">🐱</span>
                                Create New Story
                            </a>
                        @endif
                    </div>

                    @if($document->simplifications->count() > 0)
                        <div class="space-y-4">
                            @foreach($document->simplifications as $simplification)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <h4 class="text-lg font-medium text-gray-900 mr-3">
                                                    {{ $simplification->simplified_title ?: 'Cat Story' }}
                                                </h4>
                                                @if($simplification->is_favorite)
                                                    <span class="text-red-500 text-lg">❤️</span>
                                                @endif
                                            </div>
                                            
                                            <div class="flex items-center text-sm text-gray-500 space-x-4 mb-3">
                                                <span class="capitalize">{{ $simplification->getComplexityDisplayName() }}</span>
                                                <span>{{ $simplification->getModelDisplayName() }}</span>
                                                <span>{{ $simplification->created_at->diffForHumans() }}</span>
                                                @if($simplification->user_rating)
                                                    <div class="flex">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $simplification->user_rating)
                                                                <span class="text-yellow-400">⭐</span>
                                                            @else
                                                                <span class="text-gray-300">⭐</span>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                @endif
                                            </div>

                                            @if($simplification->isCompleted())
                                                <p class="text-gray-600 line-clamp-3 mb-3">
                                                    {{ Str::limit($simplification->cat_story, 200) }}
                                                </p>
                                            @endif

                                            @if($simplification->key_concepts)
                                                <div class="flex flex-wrap gap-2 mb-3">
                                                    @foreach(array_slice($simplification->key_concepts, 0, 5) as $concept)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $concept }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-shrink-0 ml-4">
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

                                    <!-- Simplification Actions -->
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                        <div class="flex space-x-3">
                                            @if($simplification->isCompleted())
                                                <a href="{{ route('simplifications.show', $simplification) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    Read Story
                                                </a>
                                                <a href="{{ route('simplifications.download', $simplification) }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                    Download
                                                </a>
                                                @if($simplification->is_public)
                                                    <span class="text-purple-600 text-sm font-medium">🌍 Public</span>
                                                @endif
                                            @elseif($simplification->hasFailed())
                                                <form method="POST" action="{{ route('simplifications.regenerate', $simplification) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">
                                                        Retry
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        @if($simplification->isCompleted())
                                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                                @if($simplification->readability_score)
                                                    <span>📊 {{ $simplification->readability_score }}/10</span>
                                                @endif
                                                @if($simplification->getWordCount())
                                                    <span>📝 {{ number_format($simplification->getWordCount()) }} words</span>
                                                @endif
                                                @if($simplification->getEstimatedReadingTime())
                                                    <span>⏱️ {{ $simplification->getEstimatedReadingTime() }} min read</span>
                                                @endif
                                                @if($simplification->download_count > 0)
                                                    <span>⬇️ {{ $simplification->download_count }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-4xl mb-4">🐱</div>
                            @if($document->isProcessed())
                                <h4 class="text-lg font-medium text-gray-900 mb-2">No cat stories yet</h4>
                                <p class="text-gray-500 mb-4">Transform this document into an engaging cat story that simplifies complex concepts!</p>
                                <a href="{{ route('simplifications.create', ['document_id' => $document->id]) }}" class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <span class="mr-2">🐱</span>
                                    Create Your First Cat Story
                                </a>
                            @else
                                <h4 class="text-lg font-medium text-gray-900 mb-2">Document not ready</h4>
                                <p class="text-gray-500">Content must be processed before creating cat stories</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Document Metadata -->
            @if($document->metadata)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Document Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($document->metadata as $key => $value)
                                @if($value)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if(is_array($value))
                                                {{ implode(', ', $value) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Processing Timeline -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Processing Timeline</h3>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li class="relative">
                                <div class="flex space-x-3">
                                    <div class="flex-shrink-0">
                                        <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-medium text-gray-900">Document uploaded</div>
                                        <div class="text-sm text-gray-500">{{ $document->created_at->format('M j, Y g:i A') }}</div>
                                    </div>
                                </div>
                                @if($document->processed_at || $document->hasFailed() || $document->isProcessing())
                                    <div class="absolute top-8 left-4 -ml-px h-6 w-0.5 bg-gray-200"></div>
                                @endif
                            </li>

                            @if($document->isProcessing())
                                <li class="relative">
                                    <div class="flex space-x-3">
                                        <div class="flex-shrink-0">
                                            <span class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900">Processing content</div>
                                            <div class="text-sm text-gray-500">In progress...</div>
                                        </div>
                                    </div>
                                </li>
                            @elseif($document->processed_at)
                                <li class="relative">
                                    <div class="flex space-x-3">
                                        <div class="flex-shrink-0">
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900">Content processed successfully</div>
                                            <div class="text-sm text-gray-500">{{ $document->processed_at->format('M j, Y g:i A') }}</div>
                                        </div>
                                    </div>
                                </li>
                            @elseif($document->hasFailed())
                                <li class="relative">
                                    <div class="flex space-x-3">
                                        <div class="flex-shrink-0">
                                            <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900">Processing failed</div>
                                            <div class="text-sm text-gray-500">{{ $document->updated_at->format('M j, Y g:i A') }}</div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-refresh for processing documents -->
    <script>
        @if($document->isProcessing())
        // Auto-refresh page every 15 seconds while processing
        setTimeout(function() {
            window.location.reload();
        }, 15000);
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
</x-app-layout>="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-yellow-800">Processing Document</h4>
                                    <p class="text-sm text-yellow-600">We're extracting content from your document. This usually takes 1-3 minutes.</p>
                                </div>
                            </div>
                            <div class="mt-3 bg-yellow-200 rounded-full h-2">
                                <div class="bg-yellow-600 h-2 rounded-full animate-pulse" style="width: 65%"></div>
                            </div>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if($document->hasFailed())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-red-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-red-800">Processing Failed</h4>
                                    @if($document->processing_error)
                                        <p class="text-sm text-red-600 mt-1">{{ $document->processing_error }}</p>
                                    @endif
                                    <div class="mt-3">
                                        <form method="POST" action="{{ route('documents.reprocess', $document) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                                Retry Processing
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Document Actions -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('documents.download', $document) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download Original
                        </a>

                        @if($document->status !== 'archived')
                            <form method="POST" action="{{ route('documents.archive', $document) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6m0 0l6-6m-6 6V3"></path>
                                    </svg>
                                    Archive
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('documents.restore', $document) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                    </svg>
                                    Restore
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('documents.destroy', $document) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width