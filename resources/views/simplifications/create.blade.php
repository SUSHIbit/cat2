<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <a href="{{ $document ? route('documents.show', $document) : route('simplifications.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🐱 Create Cat Story
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Process Overview -->
            <div class="bg-gradient-to-r from-green-50 to-blue-50 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="text-4xl">🐱</div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">How Cat Story Creation Works</h3>
                            <p class="text-gray-600 mb-3">Our AI transforms complex documents into engaging cat stories that anyone can understand!</p>
                            <div class="flex items-center space-x-6 text-sm">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                        <span class="text-xs font-semibold text-blue-600">1</span>
                                    </div>
                                    <span class="text-gray-600">Choose complexity</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                        <span class="text-xs font-semibold text-blue-600">2</span>
                                    </div>
                                    <span class="text-gray-600">AI processes</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                        <span class="text-xs font-semibold text-blue-600">3</span>
                                    </div>
                                    <span class="text-gray-600">Get cat story</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Selection (if no specific document) -->
            @if(!$document && $documents->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Document</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($documents as $doc)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition-colors" onclick="selectDocument('{{ $doc->id }}')">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 mr-3">
                                            @switch($doc->getFileExtension())
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
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ $doc->title }}</h4>
                                            <p class="text-sm text-gray-500">{{ $doc->getFormattedFileSize() }} • {{ $doc->created_at->diffForHumans() }}</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <input type="radio" name="selected_document" value="{{ $doc->id }}" class="text-blue-600">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @elseif($document)
                <!-- Selected Document Display -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Selected Document</h3>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 mr-4">
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
                            <div>
                                <h4 class="font-medium text-gray-900 text-lg">{{ $document->title }}</h4>
                                @if($document->description)
                                    <p class="text-gray-600 mb-1">{{ $document->description }}</p>
                                @endif
                                <div class="flex items-center text-sm text-gray-500 space-x-4">
                                    <span>{{ $document->getFormattedFileSize() }}</span>
                                    @if($document->content_statistics)
                                        <span>{{ number_format($document->content_statistics['word_count'] ?? 0) }} words</span>
                                        <span>{{ $document->content_statistics['estimated_reading_time_minutes'] ?? 0 }} min read</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Creation Form -->
            @if($document || $documents->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form method="POST" action="{{ route('simplifications.store') }}" class="p-6">
                        @csrf
                        
                        @if($document)
                            <input type="hidden" name="document_id" value="{{ $document->id }}">
                        @else
                            <input type="hidden" name="document_id" id="selected_document_id">
                        @endif

                        <!-- Complexity Level -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Complexity Level <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($complexityLevels as $value => $label)
                                    <div class="relative">
                                        <input 
                                            type="radio" 
                                            id="complexity_{{ $value }}" 
                                            name="complexity_level" 
                                            value="{{ $value }}"
                                            class="sr-only" 
                                            {{ old('complexity_level', 'basic') === $value ? 'checked' : '' }}
                                            required
                                        >
                                        <label 
                                            for="complexity_{{ $value }}" 
                                            class="block p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors complexity-option"
                                            data-complexity="{{ $value }}"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="font-medium text-gray-900">{{ $label }}</div>
                                                    <div class="text-sm text-gray-500 mt-1">
                                                        @switch($value)
                                                            @case('basic')
                                                                @break
                                                            @case('intermediate')
                                                                Great for middle schoolers (ages 9-14). Clear language with moderate vocabulary.
                                                                @break
                                                            @case('advanced')
                                                                Suitable for high school and adults. Sophisticated but accessible language.
                                                                @break
                                                        @endswitch
                                                    </div>
                                                </div>
                                                <div class="complexity-check hidden">
                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('complexity_level')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- AI Model Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                AI Model <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($aiModels as $value => $label)
                                    <div class="relative">
                                        <input 
                                            type="radio" 
                                            id="model_{{ str_replace(['.', '-'], '_', $value) }}" 
                                            name="ai_model" 
                                            value="{{ $value }}"
                                            class="sr-only" 
                                            {{ old('ai_model', 'gpt-3.5-turbo') === $value ? 'checked' : '' }}
                                            required
                                        >
                                        <label 
                                            for="model_{{ str_replace(['.', '-'], '_', $value) }}" 
                                            class="block p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors model-option"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="font-medium text-gray-900">{{ $label }}</div>
                                                    <div class="text-sm text-gray-500 mt-1">
                                                        @if($value === 'gpt-3.5-turbo')
                                                            Fast and efficient. Great for most documents. ⚡
                                                        @else
                                                            More sophisticated understanding. Better for complex content. 🧠
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        @if($value === 'gpt-3.5-turbo')
                                                            ~$0.002 per simplification
                                                        @else
                                                            ~$0.03 per simplification
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="model-check hidden">
                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('ai_model')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Custom Instructions (Optional) -->
                        <div class="mb-6">
                            <label for="custom_instructions" class="block text-sm font-medium text-gray-700 mb-2">
                                Custom Instructions (Optional)
                            </label>
                            <textarea 
                                id="custom_instructions" 
                                name="custom_instructions" 
                                rows="3"
                                maxlength="500"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Any specific instructions for the cat story? (e.g., 'Focus on the environmental aspects' or 'Include more examples')"
                            >{{ old('custom_instructions') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">
                                <span id="char_count">0</span>/500 characters. 
                                Give the AI specific guidance on what to emphasize in your cat story.
                            </p>
                            @error('custom_instructions')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Preview Box -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h4 class="font-medium text-blue-900 mb-2">What to Expect</h4>
                            <div id="preview_content">
                                <p class="text-sm text-blue-800">Select your preferences above to see what kind of cat story will be generated.</p>
                            </div>
                        </div>

                        <!-- Estimated Cost & Time -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-lg font-semibold text-gray-900" id="estimated_cost">~$0.002</div>
                                <div class="text-sm text-gray-500">Estimated Cost</div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-lg font-semibold text-gray-900">1-3 min</div>
                                <div class="text-sm text-gray-500">Processing Time</div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="text-lg font-semibold text-gray-900" id="estimated_length">~500 words</div>
                                <div class="text-sm text-gray-500">Story Length</div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                <p>💡 You can create multiple cat stories with different settings for the same document</p>
                            </div>
                            <div class="flex space-x-3">
                                <a href="{{ $document ? route('documents.show', $document) : route('documents.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Cancel
                                </a>
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    id="submit_button"
                                >
                                    <span class="mr-2">🐱</span>
                                    <span id="submit_text">Create Cat Story</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            @else
                <!-- No Documents Available -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <div class="text-6xl mb-4">📄</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No processed documents available</h3>
                        <p class="text-gray-500 mb-6">You need to upload and process a document before creating cat stories.</p>
                        <a href="{{ route('documents.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Upload Your First Document
                        </a>
                    </div>
                </div>
            @endif

            <!-- Recent Simplifications -->
            @if(auth()->user()->simplifications()->completed()->limit(3)->count() > 0)
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Recent Cat Stories</h3>
                        <div class="space-y-3">
                            @foreach(auth()->user()->simplifications()->completed()->latest()->limit(3)->get() as $simplification)
                                <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                    <div class="flex-shrink-0 mr-3">
                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                            <span class="text-lg">🐱</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ $simplification->simplified_title }}</p>
                                        <p class="text-sm text-gray-500">{{ $simplification->getComplexityDisplayName() }} • {{ $simplification->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('simplifications.show', $simplification) }}" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Enhanced JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Character counter
            const customInstructions = document.getElementById('custom_instructions');
            const charCount = document.getElementById('char_count');
            
            if (customInstructions && charCount) {
                customInstructions.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
                // Initial count
                charCount.textContent = customInstructions.value.length;
            }

            // Document selection (if multiple documents)
            window.selectDocument = function(documentId) {
                document.getElementById('selected_document_id').value = documentId;
                document.querySelectorAll('input[name="selected_document"]').forEach(radio => {
                    radio.checked = radio.value === documentId;
                });
                updatePreview();
            }

            // Complexity and model selection styling
            function updateOptionStyles() {
                // Complexity options
                document.querySelectorAll('input[name="complexity_level"]').forEach(radio => {
                    const label = radio.parentElement.querySelector('label');
                    const check = label.querySelector('.complexity-check');
                    
                    if (radio.checked) {
                        label.classList.add('border-green-500', 'bg-green-50');
                        label.classList.remove('border-gray-200');
                        check.classList.remove('hidden');
                    } else {
                        label.classList.remove('border-green-500', 'bg-green-50');
                        label.classList.add('border-gray-200');
                        check.classList.add('hidden');
                    }
                });

                // Model options
                document.querySelectorAll('input[name="ai_model"]').forEach(radio => {
                    const label = radio.parentElement.querySelector('label');
                    const check = label.querySelector('.model-check');
                    
                    if (radio.checked) {
                        label.classList.add('border-green-500', 'bg-green-50');
                        label.classList.remove('border-gray-200');
                        check.classList.remove('hidden');
                    } else {
                        label.classList.remove('border-green-500', 'bg-green-50');
                        label.classList.add('border-gray-200');
                        check.classList.add('hidden');
                    }
                });
            }

            // Update preview based on selections
            function updatePreview() {
                const complexity = document.querySelector('input[name="complexity_level"]:checked')?.value;
                const model = document.querySelector('input[name="ai_model"]:checked')?.value;
                const customInstructions = document.getElementById('custom_instructions')?.value;
                
                const previewContent = document.getElementById('preview_content');
                const estimatedCost = document.getElementById('estimated_cost');
                const estimatedLength = document.getElementById('estimated_length');
                
                if (complexity && model) {
                    let preview = '';
                    let cost = '$0.002';
                    let length = '500';
                    
                    // Update based on complexity
                    switch(complexity) {
                        case 'basic':
                            preview = `Your cat story will use very simple words and short sentences, perfect for young children. Think "Kitty wants to learn about..." with lots of "meows" and simple explanations.`;
                            length = '300-600';
                            break;
                        case 'intermediate':
                            preview = `Your cat story will have clear explanations with moderate vocabulary, great for middle schoolers. It will include creative cat analogies and educational elements.`;
                            length = '600-1000';
                            break;
                        case 'advanced':
                            preview = `Your cat story will use sophisticated language while maintaining the cat theme. It will include nuanced concepts and extended metaphors for deeper understanding.`;
                            length = '800-1200';
                            break;
                    }
                    
                    // Update based on model
                    if (model === 'gpt-4') {
                        cost = '$0.03';
                        preview += ' Using GPT-4 will provide more sophisticated and creative cat analogies.';
                    }
                    
                    // Add custom instructions note
                    if (customInstructions) {
                        preview += ` Your custom instructions will guide the AI to focus on specific aspects you've mentioned.`;
                    }
                    
                    previewContent.innerHTML = `<p class="text-sm text-blue-800">${preview}</p>`;
                    estimatedCost.textContent = `~${cost}`;
                    estimatedLength.textContent = `~${length} words`;
                } else {
                    previewContent.innerHTML = '<p class="text-sm text-blue-800">Select your preferences above to see what kind of cat story will be generated.</p>';
                }
            }

            // Event listeners
            document.querySelectorAll('input[name="complexity_level"], input[name="ai_model"]').forEach(input => {
                input.addEventListener('change', function() {
                    updateOptionStyles();
                    updatePreview();
                });
            });

            if (customInstructions) {
                customInstructions.addEventListener('input', updatePreview);
            }

            // Form submission handling
            const form = document.querySelector('form');
            const submitButton = document.getElementById('submit_button');
            const submitText = document.getElementById('submit_text');
            
            if (form && submitButton) {
                form.addEventListener('submit', function(e) {
                    // Validate document selection if needed
                    @if(!$document)
                    const selectedDoc = document.getElementById('selected_document_id').value;
                    if (!selectedDoc) {
                        e.preventDefault();
                        alert('Please select a document first.');
                        return;
                    }
                    @endif
                    
                    // Show loading state
                    submitButton.disabled = true;
                    submitText.textContent = 'Creating Story...';
                    submitButton.innerHTML = `
                        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Creating Story...
                    `;
                });
            }

            // Initial setup
            updateOptionStyles();
            updatePreview();
        });
    </script>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 7000)" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
            <div class="font-medium">Please correct the following errors:</div>
            <ul class="mt-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</x-app-layout>