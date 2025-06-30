<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <a href="{{ route('documents.show', $document) }}" class="mr-4 text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    ✏️ Edit Document
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Document Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center mb-6">
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
                            <h3 class="text-xl font-bold text-gray-900">{{ $document->original_filename }}</h3>
                            <div class="flex items-center text-sm text-gray-500 space-x-4 mt-2">
                                <span>{{ $document->getFormattedFileSize() }}</span>
                                <span>Uploaded {{ $document->created_at->diffForHumans() }}</span>
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
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            📄 Uploaded
                                        </span>
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('documents.update', $document) }}" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Document Title -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Document Title <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter a descriptive title for your document"
                            value="{{ old('title', $document->title) }}"
                            required
                        >
                        <p class="mt-1 text-sm text-gray-500">This title helps you identify and organize your documents</p>
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="4"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Add a detailed description of the document content, key topics, or context that might help with simplification"
                        >{{ old('description', $document->description) }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">A good description helps our AI create better cat stories</p>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content Statistics (if available) -->
                    @if($document->content_statistics)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Document Statistics</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-lg">
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-gray-900">{{ number_format($document->content_statistics['word_count'] ?? 0) }}</div>
                                    <div class="text-xs text-gray-500">Words</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-gray-900">{{ number_format($document->content_statistics['character_count'] ?? 0) }}</div>
                                    <div class="text-xs text-gray-500">Characters</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-gray-900">{{ number_format($document->content_statistics['paragraph_count'] ?? 0) }}</div>
                                    <div class="text-xs text-gray-500">Paragraphs</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-gray-900">{{ $document->content_statistics['estimated_reading_time_minutes'] ?? 0 }} min</div>
                                    <div class="text-xs text-gray-500">Reading Time</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Simplification History -->
                    @if($document->simplifications->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Cat Stories Created</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                                    <div>
                                        <div class="text-lg font-semibold text-purple-600">{{ $document->simplifications->count() }}</div>
                                        <div class="text-xs text-gray-500">Total Stories</div>
                                    </div>
                                    <div>
                                        <div class="text-lg font-semibold text-green-600">{{ $document->simplifications->where('status', 'completed')->count() }}</div>
                                        <div class="text-xs text-gray-500">Completed</div>
                                    </div>
                                    <div>
                                        <div class="text-lg font-semibold text-red-600">{{ $document->simplifications->where('is_favorite', true)->count() }}</div>
                                        <div class="text-xs text-gray-500">Favorites</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Warning for Processing Documents -->
                    @if($document->isProcessing())
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-yellow-800">Document Currently Processing</h4>
                                    <p class="text-sm text-yellow-700 mt-1">This document is currently being processed. You can still update the title and description, but content extraction is in progress.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            <p>💡 Changes will be saved immediately and won't affect existing cat stories</p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('documents.show', $document) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-red-900 mb-4">Danger Zone</h3>
                    
                    <div class="space-y-4">
                        <!-- Archive Document -->
                        @if($document->status !== 'archived')
                            <div class="flex items-center justify-between p-4 border border-yellow-200 rounded-lg bg-yellow-50">
                                <div>
                                    <h4 class="font-medium text-yellow-900">Archive Document</h4>
                                    <p class="text-sm text-yellow-700">Hide this document from your main list. You can restore it later.</p>
                                </div>
                                <form method="POST" action="{{ route('documents.archive', $document) }}" class="inline">
                                    @csrf
                                    <button 
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-yellow-300 rounded-md font-semibold text-xs text-yellow-700 uppercase tracking-widest shadow-sm hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        onclick="return confirm('Are you sure you want to archive this document?')"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6m0 0l6-6m-6 6V3"></path>
                                        </svg>
                                        Archive
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex items-center justify-between p-4 border border-blue-200 rounded-lg bg-blue-50">
                                <div>
                                    <h4 class="font-medium text-blue-900">Restore Document</h4>
                                    <p class="text-sm text-blue-700">Restore this document to your active documents list.</p>
                                </div>
                                <form method="POST" action="{{ route('documents.restore', $document) }}" class="inline">
                                    @csrf
                                    <button 
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-md font-semibold text-xs text-blue-700 uppercase tracking-widest shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                        </svg>
                                        Restore
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Delete Document -->
                        <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-red-50">
                            <div>
                                <h4 class="font-medium text-red-900">Delete Document</h4>
                                <p class="text-sm text-red-700">Permanently delete this document and all associated cat stories. This action cannot be undone.</p>
                                @if($document->simplifications->count() > 0)
                                    <p class="text-xs text-red-600 mt-1">⚠️ This will also delete {{ $document->simplifications->count() }} cat {{ Str::plural('story', $document->simplifications->count()) }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('documents.destroy', $document) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest shadow-sm hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    onclick="return confirm('Are you absolutely sure you want to delete this document? This will permanently delete the document and all {{ $document->simplifications->count() }} associated cat stories. This action cannot be undone.')"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete Permanently
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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