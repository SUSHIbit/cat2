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
                    ❤️ Favorite Cat Stories
                </h2>
            </div>
            <a href="{{ route('simplifications.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <span class="mr-2">🐱</span>
                Create New Story
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <span class="text-lg">⭐</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Average Rating</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    @if($averageRating)
                                        {{ number_format($averageRating, 1) }}/5
                                    @else
                                        -
                                    @endif
                                </p>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Downloads</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalDownloads }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Public Stories</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $publicCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Options -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('simplifications.favorites') }}" class="flex items-center space-x-4">
                        <div class="flex-1">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}"
                                placeholder="Search your favorite stories..."
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                            >
                        </div>
                        <div>
                            <select 
                                name="sort" 
                                class="border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                            >
                                <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Recently Added</option>
                                <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Highest Rated</option>
                                <option value="downloads" {{ request('sort') === 'downloads' ? 'selected' : '' }}>Most Downloaded</option>
                                <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>Title A-Z</option>
                            </select>
                        </div>
                        <button 
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Filter
                        </button>
                        @if(request('search') || request('sort'))
                            <a 
                                href="{{ route('simplifications.favorites') }}"
                                class="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Favorites Grid -->
            @if($favorites->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($favorites as $simplification)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow border-l-4 border-red-400">
                        <div class="p-6">
                            <!-- Header with Heart -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-lg">❤️</span>
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
                                
                                <!-- Status -->
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ✅ Complete
                                </span>
                            </div>

                            <!-- Meta Information -->
                            <div class="flex items-center text-xs text-gray-500 space-x-4 mb-3">
                                <span class="capitalize">{{ $simplification->getComplexityDisplayName() }}</span>
                                <span>{{ $simplification->created_at->diffForHumans() }}</span>
                                @if($simplification->is_public)
                                    <span class="text-purple-600">🌍 Public</span>
                                @endif
                            </div>

                            <!-- Story Preview -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 line-clamp-3">
                                    {{ Str::limit($simplification->cat_story, 150) }}
                                </p>
                            </div>

                            <!-- Key Concepts -->
                            @if($simplification->key_concepts)
                                <div class="flex flex-wrap gap-1 mb-4">
                                    @foreach(array_slice($simplification->key_concepts, 0, 2) as $concept)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $concept }}
                                        </span>
                                    @endforeach
                                    @if(count($simplification->key_concepts) > 2)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            +{{ count($simplification->key_concepts) - 2 }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Rating Display -->
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

                            <!-- User Notes -->
                            @if($simplification->user_notes)
                                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                    <p class="text-sm text-yellow-800 italic">"{{ Str::limit($simplification->user_notes, 100) }}"</p>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex space-x-2">
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
                                    
                                    <!-- Remove from Favorites -->
                                    <form method="POST" action="{{ route('simplifications.toggle-favorite', $simplification) }}" class="inline">
                                        @csrf
                                        <button 
                                            type="submit"
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200"
                                            title="Remove from Favorites"
                                        >
                                            💔
                                        </button>
                                    </form>
                                </div>

                                <!-- Stats -->
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
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $favorites->appends(request()->query())->links() }}
                </div>

            @else
                <!-- Empty State -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <div class="text-6xl mb-4">💔</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No favorite stories yet</h3>
                        
                        @if(request('search'))
                            <p class="text-gray-500 mb-4">No favorites match your search for "{{ request('search') }}"</p>
                            <a 
                                href="{{ route('simplifications.favorites') }}"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Clear Search
                            </a>
                        @else
                            <p class="text-gray-500 mb-4">Start adding cat stories to your favorites by clicking the ❤️ button on any story you love!</p>
                            <div class="space-y-3">
                                <a 
                                    href="{{ route('simplifications.index') }}"
                                    class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <span class="mr-2">🐱</span>
                                    Browse Cat Stories
                                </a>
                                <div class="text-sm text-gray-400">
                                    <p>or <a href="{{ route('simplifications.create') }}" class="text-green-600 hover:text-green-800">create a new story</a></p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tips for Managing Favorites -->
            @if($favorites->count() > 0)
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">💡 Managing Your Favorites</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Click the 💔 button to remove a story from favorites</li>
                                    <li>Rate your favorites to help you remember which ones you loved most</li>
                                    <li>Add notes to your favorites to remember why you liked them</li>
                                    <li>Make your best favorites public to share them with others</li>
                                    <li>Download your favorites to keep them offline</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
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