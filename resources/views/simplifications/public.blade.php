<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $simplification->simplified_title ?: 'Cat Story' }} - Cat Document Simplifier</title>

    <!-- Meta Tags for Social Sharing -->
    <meta name="description" content="A simplified cat story: {{ Str::limit(strip_tags($simplification->cat_story), 160) }}">
    <meta property="og:title" content="{{ $simplification->simplified_title ?: 'Cat Story' }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($simplification->cat_story), 160) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ request()->url() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-2xl">🐱</span>
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-gray-900">Cat Document Simplifier</h1>
                            <p class="text-sm text-gray-500">Making complex documents purr-fectly simple</p>
                        </div>
                    </div>
                    
                    <!-- Sign Up CTA -->
                    <div class="hidden sm:flex items-center space-x-3">
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white bg-blue-600 uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Get Started Free
                        </a>
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">Sign In</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Story Header -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
                    <div class="p-8">
                        <div class="text-center mb-8">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-3xl">🐱</span>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $simplification->simplified_title ?: 'Cat Story' }}</h1>
                            <p class="text-gray-600 mb-4">A simplified story based on: <span class="font-medium">{{ $simplification->document->title }}</span></p>
                            
                            <div class="flex items-center justify-center space-x-6 text-sm text-gray-500">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    {{ ucfirst($simplification->complexity_level) }} Level
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $simplification->getEstimatedReadingTime() }} min read
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2m0 0a2 2 0 012 2v2a2 2 0 01-2 2H8a2 2 0 01-2-2V6a2 2 0 012-2z M7 14l4-4 4 4"></path>
                                    </svg>
                                    {{ $simplification->getWordCount() }} words
                                </span>
                            </div>
                        </div>

                        <!-- Key Concepts -->
                        @if($simplification->key_concepts && count($simplification->key_concepts) > 0)
                            <div class="mb-8 text-center">
                                <h3 class="text-sm font-medium text-gray-700 mb-3">What You'll Learn</h3>
                                <div class="flex flex-wrap justify-center gap-2">
                                    @foreach($simplification->key_concepts as $concept)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            {{ $concept }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Story Content -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
                    <div class="p-8">
                        <div class="prose prose-lg max-w-none">
                            <!-- Story Text with Beautiful Styling -->
                            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-purple-400 p-8 rounded-r-lg mb-8">
                                <div class="text-gray-800 leading-relaxed text-lg whitespace-pre-line font-serif">{{ $simplification->cat_story }}</div>
                            </div>

                            <!-- Summary -->
                            @if($simplification->summary)
                                <div class="border-t border-gray-200 pt-8">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                                        <span class="mr-2">📝</span>
                                        Summary
                                    </h3>
                                    <p class="text-gray-700 leading-relaxed text-lg">{{ $simplification->summary }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Story Stats -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Story Quality</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @if($simplification->readability_score)
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ $simplification->readability_score }}/10</div>
                                    <div class="text-sm text-gray-500">Readability</div>
                                </div>
                            @endif
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $simplification->getWordCount() }}</div>
                                <div class="text-sm text-gray-500">Words</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $simplification->getEstimatedReadingTime() }} min</div>
                                <div class="text-sm text-gray-500">Reading Time</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ $simplification->download_count + 1 }}</div>
                                <div class="text-sm text-gray-500">Views</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-sm">
                    <div class="p-8 text-center">
                        <div class="text-white mb-6">
                            <div class="text-4xl mb-4">🐱✨</div>
                            <h2 class="text-2xl font-bold mb-2">Love this cat story?</h2>
                            <p class="text-blue-100 text-lg">Transform your own complex documents into simple, engaging stories!</p>
                        </div>
                        
                        <div class="space-y-4">
                            <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-3 bg-white text-blue-600 rounded-lg font-semibold text-lg hover:bg-gray-50 transition-colors shadow-lg">
                                <span class="mr-2">🚀</span>
                                Start Creating Free
                            </a>
                            <div class="text-blue-100 text-sm">
                                <p>✅ Upload PDF, Word, or PowerPoint files</p>
                                <p>✅ AI transforms them into cat stories</p>
                                <p>✅ Choose complexity levels for different audiences</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Share Options -->
                <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Share This Story</h3>
                        <div class="flex justify-center space-x-4">
                            <!-- Copy Link -->
                            <button 
                                onclick="copyToClipboard('{{ request()->url() }}')"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                Copy Link
                            </button>
                            
                            <!-- Twitter Share -->
                            <a 
                                href="https://twitter.com/intent/tweet?text={{ urlencode('Check out this amazing cat story that explains complex topics in simple terms! 🐱') }}&url={{ urlencode(request()->url()) }}"
                                target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                                Tweet
                            </a>
                            
                            <!-- Facebook Share -->
                            <a 
                                href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                                target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                Share
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-lg">🐱</span>
                        </div>
                        <span class="text-lg font-semibold">Cat Document Simplifier</span>
                    </div>
                    <p class="text-gray-400 mb-4">Making complex documents purr-fectly simple with AI-powered cat stories</p>
                    <div class="flex justify-center space-x-6 text-sm">
                        <a href="{{ route('register') }}" class="text-gray-300 hover:text-white">Get Started</a>
                        <a href="{{ route('login') }}" class="text-gray-300 hover:text-white">Sign In</a>
                        <span class="text-gray-500">•</span>
                        <span class="text-gray-500">© {{ date('Y') }} Cat Document Simplifier</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript for Sharing -->
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success message
                const message = document.createElement('div');
                message.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50 shadow-lg';
                message.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Link copied to clipboard!
                    </div>
                `;
                document.body.appendChild(message);
                setTimeout(() => document.body.removeChild(message), 3000);
            });
        }

        // Track view for analytics (increment view count)
        fetch('{{ route("simplifications.public", $simplification->share_token) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: 'view' })
        }).catch(() => {
            // Silently handle any errors
        });
    </script>

    <!-- Analytics/Tracking would go here -->
</body>
</html>