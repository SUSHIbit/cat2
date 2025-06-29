<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📄 My Documents
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters & Search -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('documents.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input 
                                    type="text" 
                                    id="search" 
                                    name="search" 
                                    value="{{ $search }}"
                                    placeholder="Search documents..."
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
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Type Filter -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">File Type</label>
                                <select 
                                    id="type" 
                                    name="type"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">All Types</option>
                                    @foreach($typeOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $type === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Sort -->
                            <div>
                                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <div class="flex space-x-2">
                                    <select 
                                        id="sort" 
                                        name="sort"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="created_at" {{ $sort === 'created_at' ? 'selected' : '' }}>Date</option>
                                        <option value="title" {{ $sort === 'title' ? 'selected' : '' }}>Title</option>
                                        <option value="file_size" {{ $sort === 'file_size' ? 'selected' : '' }}>Size</option>
                                        <option value="status" {{ $sort === 'status' ? 'selected' : '' }}>Status</option>
                                    </select>
                                    <select 
                                        name="direction"
                                        class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="desc" {{ $direction === 'desc' ? 'selected' : '' }}>↓</option>
                                        <option value="asc" {{ $direction === 'asc' ? 'selected' : '' }}>↑</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <button 
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Apply Filters
                            </button>
                            
                            @if($search || $status || $type)
                                <a 
                                    href="{{ route('documents.index') }}"
                                    class="text-sm text-gray-500 hover:text-gray-700"
                                >
                                    Clear Filters
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div x-data="{ selectedDocuments: [], showBulkActions: false }" class="space-y-4">
                
                <!-- Bulk Action Bar -->
                <div x-show="selectedDocuments.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-blue-800">
                            <span x-text="selectedDocuments.length"></span> document(s) selected
                        </span>
                        <div class="flex space-x-2">
                            <button 
                                @click="bulkAction('archive')"
                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200"
                            >
                                Archive
                            </button>
                            <button 
                                @click="bulkAction('delete')"
                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Documents Grid/List -->
                @if($documents->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($documents as $document)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <!-- Document Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            :value="'{{ $document->id }}'"
                                            x-model="selectedDocuments"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 mr-3"
                                        >
                                        <div class="flex-shrink-0">
                                            @switch($document->getFileExtension())
                                                @case('pdf')
                                                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                                        <span class="text-sm font-semibold text-red-600">PDF</span>
                                                    </div>
                                                    @break
                                                @case('docx')
                                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                        <span class="text-sm font-semibold text-blue-600">DOC</span>
                                                    </div>
                                                    @break
                                                @case('pptx')
                                                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                                        <span class="text-sm font-semibold text-orange-600">PPT</span>
                                                    </div>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                    
                                    <!-- Status Badge -->
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
                                            @case('archived')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    📦 Archived
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    📄 Uploaded
                                                </span>
                                        @endswitch
                                    </div>
                                </div>

                                <!-- Document Info -->
                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                        {{ $document->title }}
                                    </h3>
                                    
                                    @if($document->description)
                                        <p class="text-sm text-gray-600 mb-2 line-clamp-2">
                                            {{ $document->description }}
                                        </p>
                                    @endif
                                    
                                    <div class="flex items-center text-xs text-gray-500 space-x-4">
                                        <span>{{ $document->getFormattedFileSize() }}</span>
                                        <span>{{ $document->created_at->format('M j, Y') }}</span>
                                        @if($document->isProcessed())
                                            <span>{{ $document->simplifications->count() }} stories</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Processing Progress -->
                                @if($document->isProcessing())
                                    <div class="mb-4">
                                        <div class="flex items-center text-sm text-yellow-600 mb-2">
                                            <svg class="w-4 h-4 animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Processing document...
                                        </div>
                                        <div class="w-full bg-yellow-200 rounded-full h-2">
                                            <div class="bg-yellow-600 h-2 rounded-full animate-pulse" style="width: 65%"></div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Error Message -->
                                @if($document->hasFailed() && $document->processing_error)
                                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded">
                                        <p class="text-sm text-red-600">{{ $document->processing_error }}</p>
                                    </div>
                                @endif

                                <!-- Actions -->
                                <div class="flex items-center justify-between">
                                    <div class="flex space-x-2">
                                        @if($document->isProcessed())
                                            <a 
                                                href="{{ route('documents.show', $document) }}"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
                                            >
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View
                                            </a>
                                            <a 
                                                href="{{ route('simplifications.create', ['document_id' => $document->id]) }}"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200"
                                            >
                                                <span class="mr-1">🐱</span>
                                                Simplify
                                            </a>
                                        @elseif($document->hasFailed())
                                            <form method="POST" action="{{ route('documents.reprocess', $document) }}" class="inline">
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
                                    
                                    <!-- Document Menu -->
                                    <div x-data="{ open: false }" class="relative">
                                        <button 
                                            @click="open = !open"
                                            class="p-1 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                            </svg>
                                        </button>
                                        
                                        <div 
                                            x-show="open" 
                                            @click.outside="open = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200"
                                        >
                                            <div class="py-1">
                                                <a 
                                                    href="{{ route('documents.download', $document) }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    Download
                                                </a>
                                                <a 
                                                    href="{{ route('documents.edit', $document) }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    Edit
                                                </a>
                                                @if($document->status !== 'archived')
                                                    <form method="POST" action="{{ route('documents.archive', $document) }}" class="inline w-full">
                                                        @csrf
                                                        <button 
                                                            type="submit"
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                        >
                                                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6m0 0l6-6m-6 6V3"></path>
                                                            </svg>
                                                            Archive
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('documents.restore', $document) }}" class="inline w-full">
                                                        @csrf
                                                        <button 
                                                            type="submit"
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                        >
                                                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                                            </svg>
                                                            Restore
                                                        </button>
                                                    </form>
                                                @endif
                                                <div class="border-t border-gray-100"></div>
                                                <form 
                                                    method="POST" 
                                                    action="{{ route('documents.destroy', $document) }}" 
                                                    class="inline w-full"
                                                    onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.')"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button 
                                                        type="submit"
                                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                                    >
                                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $documents->appends(request()->query())->links() }}
                    </div>

                @else
                    <!-- Empty State -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-12 text-center">
                            <div class="text-6xl mb-4">📄</div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No documents found</h3>
                            
                            @if($search || $status || $type)
                                <p class="text-gray-500 mb-4">Try adjusting your filters or search terms</p>
                                <a 
                                    href="{{ route('documents.index') }}"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Clear Filters
                                </a>
                            @else
                                <p class="text-gray-500 mb-4">Upload your first document to get started with cat simplifications!</p>
                                <a 
                                    href="{{ route('documents.create') }}"
                                    class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    Upload Your First Document
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Bulk Action Form -->
                <form x-ref="bulkForm" method="POST" style="display: none;">
                    @csrf
                    <input x-ref="bulkAction" name="action" type="hidden">
                    <input x-ref="bulkDocuments" name="documents" type="hidden">
                </form>
            </div>
        </div>
    </div>

    <!-- Auto-refresh for processing documents -->
    <script>
        // Auto-refresh page every 30 seconds if there are documents being processed
        @php
            $processingCount = $documents->where('status', 'processing')->count();
        @endphp
        
        @if($processingCount > 0)
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        @endif

        // Bulk actions functionality
        function bulkAction(action) {
            if (this.selectedDocuments.length === 0) return;
            
            const actionText = action === 'delete' ? 'delete' : 'archive';
            const confirmMessage = `Are you sure you want to ${actionText} ${this.selectedDocuments.length} document(s)?`;
            
            if (!confirm(confirmMessage)) return;
            
            this.$refs.bulkAction.value = action;
            this.$refs.bulkDocuments.value = JSON.stringify(this.selectedDocuments);
            this.$refs.bulkForm.action = '{{ route("documents.bulk-action") }}';
            this.$refs.bulkForm.submit();
        }
    </script>

    <!-- Enhanced Search -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit search after typing delay
            const searchInput = document.getElementById('search');
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 3 || this.value.length === 0) {
                        this.form.submit();
                    }
                }, 500);
            });
            
            // Auto-submit on filter changes
            ['status', 'type', 'sort'].forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    field.addEventListener('change', function() {
                        this.form.submit();
                    });
                }
            });
        });
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