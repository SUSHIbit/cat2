<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📄 Upload Document
            </h2>
            <a href="{{ route('documents.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Documents
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Upload Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">How it works</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p class="mb-2">🐱 Upload your complex document and our AI will transform it into a simple, engaging cat story!</p>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Supported formats: PDF, Word (.docx), PowerPoint (.pptx)</li>
                                <li>Maximum file size: {{ number_format($maxFileSize / 1024, 0) }}MB</li>
                                <li>Processing takes 1-3 minutes depending on document length</li>
                                <li>You can create multiple simplifications with different complexity levels</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="p-6">
                    @csrf

                    <!-- File Upload Area -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Document File <span class="text-red-500">*</span>
                        </label>
                        
                        <div x-data="fileUpload()" class="mt-1">
                            <div 
                                @drop.prevent="handleDrop($event)"
                                @dragover.prevent
                                @dragenter.prevent
                                @dragleave.prevent
                                class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors"
                                :class="{'border-blue-400 bg-blue-50': isDragging}"
                            >
                                <div class="space-y-3">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    
                                    <div class="text-sm text-gray-600">
                                        <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload a file</span>
                                            <input 
                                                id="file" 
                                                name="file" 
                                                type="file" 
                                                class="sr-only"
                                                accept=".pdf,.docx,.pptx"
                                                required
                                                @change="handleFileSelect($event)"
                                            >
                                        </label>
                                        <span class="ml-1">or drag and drop</span>
                                    </div>
                                    
                                    <p class="text-xs text-gray-500">
                                        PDF, DOCX, PPTX up to {{ number_format($maxFileSize / 1024, 0) }}MB
                                    </p>
                                    
                                    <!-- File Preview -->
                                    <div x-show="selectedFile" class="mt-4 p-3 bg-gray-50 rounded border">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-700" x-text="selectedFile?.name"></span>
                                            <span class="text-xs text-gray-500 ml-2" x-text="formatFileSize(selectedFile?.size)"></span>
                                            <button type="button" @click="clearFile()" class="ml-auto text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @error('file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Document Title -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Document Title
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter a descriptive title (optional - will use filename if empty)"
                            value="{{ old('title') }}"
                        >
                        <p class="mt-1 text-sm text-gray-500">If left empty, we'll use the filename as the title</p>
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
                            rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Add a brief description of the document content (optional)"
                        >{{ old('description') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">This helps you organize your documents</p>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Upload Progress -->
                    <div x-data="{ uploading: false, progress: 0 }" x-show="uploading" class="mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 animate-spin mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span class="text-sm text-blue-800">Uploading document...</span>
                            </div>
                            <div class="mt-2 bg-blue-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="`width: ${progress}%`"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            <p>💡 After upload, document processing will begin automatically</p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Upload Document
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Recent Uploads -->
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Uploads</h3>
                    
                    @php
                        $recentUploads = auth()->user()->documents()->latest()->limit(5)->get();
                    @endphp
                    
                    @if($recentUploads->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentUploads as $document)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @switch($document->getFileExtension())
                                            @case('pdf')
                                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                                    <span class="text-sm font-semibold text-red-600">PDF</span>
                                                </div>
                                                @break
                                            @case('docx')
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <span class="text-sm font-semibold text-blue-600">DOC</span>
                                                </div>
                                                @break
                                            @case('pptx')
                                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                                    <span class="text-sm font-semibold text-orange-600">PPT</span>
                                                </div>
                                                @break
                                        @endswitch
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $document->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $document->created_at->diffForHumans() }} • {{ $document->getFormattedFileSize() }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @switch($document->status)
                                        @case('completed')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ✅ Ready
                                            </span>
                                            <a href="{{ route('documents.show', $document) }}" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
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
                                            <a href="{{ route('documents.show', $document) }}" class="text-blue-600 hover:text-blue-800 text-sm">Retry</a>
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
                            <p class="text-gray-500">No documents uploaded yet</p>
                            <p class="text-gray-400 text-sm">Upload your first document to get started</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js File Upload Component -->
    <script>
        function fileUpload() {
            return {
                isDragging: false,
                selectedFile: null,
                
                handleDrop(e) {
                    this.isDragging = false;
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.setFile(files[0]);
                    }
                },
                
                handleFileSelect(e) {
                    const files = e.target.files;
                    if (files.length > 0) {
                        this.setFile(files[0]);
                    }
                },
                
                setFile(file) {
                    // Validate file type
                    const allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Please select a PDF, Word document (.docx), or PowerPoint presentation (.pptx)');
                        return;
                    }
                    
                    // Validate file size ({{ $maxFileSize }} KB)
                    if (file.size > {{ $maxFileSize }} * 1024) {
                        alert('File size must be less than {{ number_format($maxFileSize / 1024, 0) }}MB');
                        return;
                    }
                    
                    this.selectedFile = file;
                    
                    // Auto-fill title if empty
                    const titleInput = document.getElementById('title');
                    if (!titleInput.value) {
                        const nameWithoutExt = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
                        titleInput.value = nameWithoutExt;
                    }
                },
                
                clearFile() {
                    this.selectedFile = null;
                    document.getElementById('file').value = '';
                },
                
                formatFileSize(bytes) {
                    if (!bytes) return '';
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(1024));
                    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
                }
            }
        }
    </script>

    <!-- Form Enhancement Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitButton = form.querySelector('button[type="submit"]');
            
            form.addEventListener('submit', function(e) {
                // Show uploading state
                submitButton.disabled = true;
                submitButton.innerHTML = `
                    <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Uploading...
                `;
            });
        });
    </script>
</x-app-layout>