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
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="text-4xl mr-4">🐱</div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Welcome back, {{ auth()->user()->name }}!</h3>
                                <p class="text-gray-600">Turn your complex documents into purr-fectly simple cat stories</p>
                            </div>
                        </div>
                        
                        <!-- Quick Action Buttons -->
                        <div class="hidden md:flex space-x-3">
                            <a href="{{ route('documents.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Upload
                            </a>
                            <a href="{{ route('simplifications.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <span class="mr-2">🐱</span>
                                Create Story
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-400">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Documents</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_documents'] }}</p>
                                <p class="text-xs text-gray-400">{{ $stats['total_file_size'] }} total size</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-400">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-lg">🐱</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Cat Stories</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_simplifications'] }}</p>
                                <p class="text-xs text-gray-400">{{ $stats['completed_simplifications'] }} completed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-red-400">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Favorites</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['favorites_count'] }}</p>
                                <p class="text-xs text-gray-400">Most loved stories</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-400">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Success Rate</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['success_rate'] }}%</p>
                                <p class="text-xs text-gray-400">Processing success</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Processing Status Alert -->
            @if($documentsInProcessing->count() > 0 || $simplificationsInProcessing->count() > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-600 animate-spin mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-yellow-800">Currently Processing</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                @if($documentsInProcessing->count() > 0)
                                    <p>📄 {{ $documentsInProcessing->count() }} document(s) being processed</p>
                                @endif
                                @if($simplificationsInProcessing->count() > 0)
                                    <p>🐱 {{ $simplificationsInProcessing->count() }} cat story(ies) being created</p>
                                @endif
                                <p class="text-xs mt-1">This page will auto-refresh in 30 seconds</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('documents.create') }}" class="block p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Upload Document</p>
                            <p class="text-sm text-gray-500">Add a new document</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('simplifications.create') }}" class="block p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <span class="text-2xl">🐱</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Create Cat Story</p>
                            <p class="text-sm text-gray-500">Simplify a document</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('documents.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">My Documents</p>
                            <p class="text-sm text-gray-500">Manage your files</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('simplifications.favorites') }}" class="block p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Favorites</p>
                            <p class="text-sm text-gray-500">Loved cat stories</p>
                        </div>
                    </div>
                </a>
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
                                <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="flex-shrink-0">
                                        @switch($document->getFileExtension())
                                            @case('pdf')
                                                <div class="w-10 h-10 bg-red-100 rounded flex items-center justify-center">
                                                    <span class="text-xs font-semibold text-red-600">PDF</span>
                                                </div>
                                                @break
                                            @case('docx')
                                                <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center">
                                                    <span class="text-xs font-semibold text-blue-600">DOC</span>
                                                </div>
                                                @break
                                            @case('pptx')
                                                <div class="w-10 h-10 bg-orange-100 rounded flex items-center justify-center">
                                                    <span class="text-xs font-semibold text-orange-600">PPT</span>
                                                </div>
                                                @break
                                        @endswitch
                                    </div>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $document->title }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $document->created_at->diffForHumans() }} • {{ $document->getFormattedFileSize() }}
                                        </p>
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
                            <div class="text-center py-8">
                                <div class="text-4xl mb-2">📄</div>
                                <p class="text-gray-500 mb-2">No documents yet</p>
                                <a href="{{ route('documents.create') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Upload your first document</a>
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
                                <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                            <span class="text-lg">🐱</span>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $simplification->simplified_title ?: 'Cat Story' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $simplification->created_at->diffForHumans() }} • {{ $simplification->getComplexityDisplayName() }}
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 flex items-center space-x-2">
                                        @if($simplification->is_favorite)
                                            <span class="text-red-500">❤️</span>
                                        @endif
                                        @if($simplification->user_rating)
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $simplification->user_rating)
                                                        <span class="text-yellow-400 text-xs">⭐</span>
                                                    @else
                                                        <span class="text-gray-300 text-xs">⭐</span>
                                                    @endif
                                                @endfor
                                            </div>
                                        @endif
                                        @if($simplification->isCompleted())
                                            <a href="{{ route('simplifications.show', $simplification) }}" class="text-blue-600 hover:text-blue-800 text-xs">View</a>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="text-4xl mb-2">🐱</div>
                                <p class="text-gray-500 mb-2">No cat stories yet</p>
                                <a href="{{ route('simplifications.create') }}" class="text-green-600 hover:text-green-800 text-sm font-medium">Create your first story</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Usage Analytics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Your Activity Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- File Types Chart -->
                        <div class="text-center">
                            <h4 class="text-sm font-medium text-gray-500 mb-3">Document Types</h4>
                            <div class="space-y-2">
                                @foreach($stats['file_types'] as $type => $count)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 uppercase">{{ $type }}</span>
                                        <div class="flex items-center">
                                            <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $stats['total_documents'] > 0 ? ($count / $stats['total_documents']) * 100 : 0 }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Complexity Levels -->
                        <div class="text-center">
                            <h4 class="text-sm font-medium text-gray-500 mb-3">Story Complexity</h4>
                            <div class="space-y-2">
                                @foreach($stats['complexity_levels'] as $level => $count)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 capitalize">{{ $level }}</span>
                                        <div class="flex items-center">
                                            <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stats['total_simplifications'] > 0 ? ($count / $stats['total_simplifications']) * 100 : 0 }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Monthly Activity -->
                        <div class="text-center">
                            <h4 class="text-sm font-medium text-gray-500 mb-3">This Month</h4>
                            <div class="space-y-2">
                                <div class="bg-blue-50 p-3 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ $stats['this_month_documents'] }}</div>
                                    <div class="text-xs text-blue-500">Documents Uploaded</div>
                                </div>
                                <div class="bg-green-50 p-3 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">{{ $stats['this_month_simplifications'] }}</div>
                                    <div class="text-xs text-green-500">Stories Created</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips and Help -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-lg font-medium text-purple-800">💡 Pro Tips for Better Cat Stories</h3>
                            <div class="mt-2 text-sm text-purple-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Upload clear, well-structured documents for best results</li>
                                    <li>Try different complexity levels to see which works best for your audience</li>
                                    <li>Add detailed descriptions to help our AI understand your content better</li>
                                    <li>Rate and favorite your best stories to keep track of what works</li>
                                    <li>Share your favorite stories publicly to help others learn</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-refresh for processing items -->
    <script>
        @if($documentsInProcessing->count() > 0 || $simplificationsInProcessing->count() > 0)
        // Auto-refresh page every 30 seconds if there are items being processed
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        @endif
    </script>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50 shadow-lg">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50 shadow-lg">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif
</x-app-layout>