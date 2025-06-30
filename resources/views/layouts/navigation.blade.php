<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-2">
                            <span class="text-lg">🐱</span>
                        </div>
                        <span class="font-semibold text-gray-800 hidden sm:block">Cat Document Simplifier</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.*')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ __('Documents') }}
                        @php
                            $processingCount = auth()->user()->documents()->where('status', 'processing')->count();
                        @endphp
                        @if($processingCount > 0)
                            <span class="ml-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ $processingCount }}
                            </span>
                        @endif
                    </x-nav-link>
                    
                    <x-nav-link :href="route('simplifications.index')" :active="request()->routeIs('simplifications.*') && !request()->routeIs('simplifications.favorites')">
                        <span class="mr-2">🐱</span>
                        {{ __('Cat Stories') }}
                        @php
                            $simplificationProcessingCount = auth()->user()->simplifications()->where('status', 'processing')->count();
                        @endphp
                        @if($simplificationProcessingCount > 0)
                            <span class="ml-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ $simplificationProcessingCount }}
                            </span>
                        @endif
                    </x-nav-link>
                    
                    <x-nav-link :href="route('simplifications.favorites')" :active="request()->routeIs('simplifications.favorites')">
                        <span class="mr-2">❤️</span>
                        {{ __('Favorites') }}
                        @php
                            $favoritesCount = auth()->user()->simplifications()->where('is_favorite', true)->count();
                        @endphp
                        @if($favoritesCount > 0)
                            <span class="ml-1 text-xs text-gray-500">({{ $favoritesCount }})</span>
                        @endif
                    </x-nav-link>
                </div>
            </div>

            <!-- Right Side Navigation -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Quick Upload Button -->
                <a href="{{ route('documents.create') }}" class="mr-4 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Upload
                </a>

                <!-- Notifications Dropdown -->
                @php
                    $notifications = [
                        'processing_docs' => auth()->user()->documents()->where('status', 'processing')->count(),
                        'processing_simplifications' => auth()->user()->simplifications()->where('status', 'processing')->count(),
                        'failed_docs' => auth()->user()->documents()->where('status', 'failed')->count(),
                        'failed_simplifications' => auth()->user()->simplifications()->where('status', 'failed')->count(),
                    ];
                    $totalNotifications = array_sum($notifications);
                @endphp

                @if($totalNotifications > 0)
                    <div class="relative mr-4" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400"></span>
                        </button>

                        <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-50 border border-gray-200">
                            <div class="py-2">
                                <div class="px-4 py-2 text-sm font-medium text-gray-900 border-b border-gray-200">
                                    Notifications
                                </div>
                                
                                @if($notifications['processing_docs'] > 0)
                                    <a href="{{ route('documents.index', ['status' => 'processing']) }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-yellow-400 rounded-full mr-3"></div>
                                            <div>
                                                <p class="font-medium">{{ $notifications['processing_docs'] }} document(s) processing</p>
                                                <p class="text-xs text-gray-500">Content extraction in progress</p>
                                            </div>
                                        </div>
                                    </a>
                                @endif

                                @if($notifications['processing_simplifications'] > 0)
                                    <a href="{{ route('simplifications.index', ['status' => 'processing']) }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-yellow-400 rounded-full mr-3"></div>
                                            <div>
                                                <p class="font-medium">{{ $notifications['processing_simplifications'] }} cat story(ies) being created</p>
                                                <p class="text-xs text-gray-500">AI is working on your stories</p>
                                            </div>
                                        </div>
                                    </a>
                                @endif

                                @if($notifications['failed_docs'] > 0)
                                    <a href="{{ route('documents.index', ['status' => 'failed']) }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-red-400 rounded-full mr-3"></div>
                                            <div>
                                                <p class="font-medium">{{ $notifications['failed_docs'] }} document(s) failed</p>
                                                <p class="text-xs text-gray-500">Click to retry processing</p>
                                            </div>
                                        </div>
                                    </a>
                                @endif

                                @if($notifications['failed_simplifications'] > 0)
                                    <a href="{{ route('simplifications.index', ['status' => 'failed']) }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-red-400 rounded-full mr-3"></div>
                                            <div>
                                                <p class="font-medium">{{ $notifications['failed_simplifications'] }} cat story(ies) failed</p>
                                                <p class="text-xs text-gray-500">Click to retry creation</p>
                                            </div>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-2">
                                    <span class="text-sm font-medium text-gray-600">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <span>{{ Auth::user()->name }}</span>
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 text-sm text-gray-500 border-b border-gray-100">
                            <div class="font-medium">{{ Auth::user()->name }}</div>
                            <div class="text-xs">{{ Auth::user()->email }}</div>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('simplifications.favorites')">
                            <span class="mr-2">❤️</span>
                            {{ __('My Favorites') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-100"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.*')">
                {{ __('Documents') }}
                @if($processingCount ?? 0 > 0)
                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ $processingCount }}
                    </span>
                @endif
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('simplifications.index')" :active="request()->routeIs('simplifications.*') && !request()->routeIs('simplifications.favorites')">
                🐱 {{ __('Cat Stories') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('simplifications.favorites')" :active="request()->routeIs('simplifications.favorites')">
                ❤️ {{ __('Favorites') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('documents.create')">
                📤 {{ __('Upload Document') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 flex items-center">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                        <span class="text-sm font-medium text-gray-600">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    {{ Auth::user()->name }}
                </div>
                <div class="font-medium text-sm text-gray-500 ml-11">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>