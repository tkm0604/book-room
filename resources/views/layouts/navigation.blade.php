<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        {{-- <x-application-logo class="block h-9 w-auto fill-current text-gray-800" /> --}}
                        <img src="{{ asset('storage/common/logo.png') }}" class="block h-9 w-auto fill-current text-gray-800" alt="book-room">

                    </a>
                </div>

                @if (auth()->user() && (auth()->user()->email_verified_at || auth()->user()->twitter_token))
                    <!-- Navigation Links -->
                    <div class="hidden  sm:-my-px sm:ml-10 sm:flex ml-2.5">
                        {{-- <x-nav-link :href="route('post.index')" :active="request()->routeIs('post.index')">
                        HOME
                    </x-nav-link> --}}
                        <x-nav-link :href="route('post.create')" :active="request()->routeIs('post.create')">
                            新規投稿
                        </x-nav-link>
                        <x-nav-link :href="route('post.mypost')" :active="request()->routeIs('post.mypost')">
                            自分の投稿
                        </x-nav-link>
                        <x-nav-link :href="route('post.mycomment')" :active="request()->routeIs('post.mycomment')">
                            自分のコメントした投稿
                        </x-nav-link>
                        {{-- <x-nav-link :href="route('contact.create')">
                            お問い合わせ
                        </x-nav-link> --}}
                        @can('admin')
                            <x-nav-link :href="route('profile.index')" :active="request()->routeIs('profile.index')">
                                ユーザー一覧
                            </x-nav-link>
                        @endcan
                    </div>
                    @else
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <x-nav-link :href="route('register')" :active="request()->routeIs('post.mypost')">
                            アカウントの作成
                        </x-nav-link>
                        <x-nav-link :href="route('login')">
                            ログイン
                        </x-nav-link>
                    </div>
                @endif
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            @if (auth()->check())
                                <span>{{ Auth::user()->name }}</span>
                            @else
                                <span class="mx-1 my-1">ゲストユーザー</span>
                            @endif
                            <span class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if (auth()->check())
                            {{-- ログインユーザーの時 --}}
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('プロフィール') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('ログアウト') }}
                                </x-dropdown-link>
                            </form>
                        @else
                            {{-- ゲストユーザーの時 --}}
                            <x-dropdown-link :href="route('login')">
                                {{ __('ログイン') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('register')">
                                {{ __('アカウントを作成') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('contact.create')">
                                {{ __('お問い合わせ') }}
                            </x-dropdown-link>
                        @endif
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        {{-- <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('post.index')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div> --}}

        <!-- Responsive Settings Options -->
        <div class="py-4 px-2 border-t border-gray-200">
            @if (auth()->check())
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ auth()->user()->name }}さん</div>
                    {{-- <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div> --}}
                </div>
            @else
                <p class="mx-1 my-1">ゲストさん</p>
            @endif

            <div class="mt-3 space-y-1">
                @if (auth()->check())
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('プロフィール') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('post.create')">
                        {{ __('新規投稿') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('post.mypost')">
                        {{ __('自分の投稿') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('post.mycomment')">
                        {{ __('自分のコメントした投稿') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('site-policy')">
                        {{ __('サイトポリシー') }}
                    </x-responsive-nav-link>
                    @can('admin')
                    <x-responsive-nav-link :href="route('profile.index')">
                        {{ __('ユーザー一覧') }}
                    </x-responsive-nav-link>
                    @endcan
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            {{ __('ログアウト') }}
                        </x-responsive-nav-link>
                    </form>
                @else
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('ログイン') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('アカウントを作成') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('site-policy')">
                        {{ __('サイトポリシー') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('contact.create')">
                        {{ __('お問い合わせ') }}
                    </x-responsive-nav-link>
                @endif
            </div>
        </div>
    </div>
</nav>
