<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🐱 Cat Document Simplifier Dashboard
            </h2>
            <a href="{{ route('documents.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Upload Document
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-100 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="text-4xl mr-4">🐱</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Welcome back, {{ auth()->user()->name }}!</h3>
                            <p class="text-gray-600">Turn your complex documents into purr-fectly simple cat stories</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Documents</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_documents'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Cat Stories</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_simplifications'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Favorites</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['favorites_count'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Success Rate</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['success_rate'] }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="{{ route('documents.create') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Upload Document</p>
                                    <p class="text-sm text-gray-500">Add a new document</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('documents.index') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">My Documents</p>
                                    <p class="text-sm text-gray-500">Manage your files</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('simplifications.index') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Cat Stories</p>
                                    <p class="text-sm text-gray-500">View simplifications</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('simplifications.favorites') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Favorites</p>
                                    <p class="text-sm text-gray-500">Loved cat stories</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Documents -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Documents</h3>
                            <a href="{{ route('documents.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                        </div>
                        
                        @if($recentDocuments->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentDocuments as $document)
                                <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                    <div class="flex-shrink-0">
                                        @switch($document->getFileExtension())
                                            @case('pdf')
                                                <div class="w-8 h-8 bg-red-100 rounded flex items-center justify-center">
                                                    <span class="text-xs font-semibold text-red-600">PDF</span>
                                                </div>
                                                @break
                                            @case('docx')
                                                <div class="w-8 h-8 bg-blue-100 rounded flex items-center justify-center">
                                                    <span class="text-xs font-semibold text-blue-600">DOC</span>
                                                </div>
                                                @break
                                            @case('pptx')
                                                <div class="w-8 h-8 bg-orange-100 rounded flex items-center justify-center">
                                                    <span class="text-xs font-semibold text-orange-600">PPT</span>
                                                </div>
                                                @break
                                        @endswitch
                                    </div>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $document->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $document->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        @switch($document->status)
                                            @case('completed')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ✅ Ready
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
                                                    📄 Uploaded
                                                </span>
                                        @endswitch
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <div class="text-4xl mb-2">📄</div>
                                <p class="text-gray-500">No documents yet</p>
                                <a href="{{ route('documents.create') }}" class="text-blue-600 hover:text-blue-800 text-sm">Upload your first document</a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Simplifications -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Cat Stories</h3>
                            <a href="{{ route('simplifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                        </div>
                        
                        @if($recentSimplifications->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentSimplifications as $simplification)
                                <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                            <span class="text-lg">🐱</span>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $simplification->simplified_title }}</p>
                                        <p class="text-xs text-gray-500">{{ $simplification->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="flex-shrink-0 flex items-center space-x-2">
                                        @if($simplification->is_favorite)
                                            <span class="text-red-500">❤️</span>
                                        @endif
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
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <div class="text-4xl mb-2">🐱</div>
                                <p class="text-gray-500">No cat stories yet</p>
                                <p class="text-gray-400 text-sm">Upload a document and create your first simplification</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Processing Status -->
            @if($documentsInProcessing->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Documents Being Processed</h3>
                    <div class="space-y-3">
                        @foreach($documentsInProcessing as $document)
                        <div class="flex items-center p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-yellow-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $document->title }}</p>
                                <p class="text-xs text-gray-600">Processing content... This usually takes 1-2 minutes.</p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Processing
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Usage Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Activity</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $stats['total_file_size'] }}</div>
                            <div class="text-sm text-gray-500">Total file size</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $stats['total_cost'] }}</div>
                            <div class="text-sm text-gray-500">AI processing cost</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">
                                @if($stats['average_rating'])
                                    {{ $stats['average_rating'] }}/5
                                @else
                                    -
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">Average rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-refresh for processing status -->
    <script>
        // Auto-refresh page every 30 seconds if there are documents being processed
        @if($documentsInProcessing->count() > 0)
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        @endif
    </script>
</x-app-layout>