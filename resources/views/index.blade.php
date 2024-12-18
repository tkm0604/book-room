<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            投稿の一覧
        </h2>
        <x-message :message="session('message')" />
    </x-slot>
    <div class="max-w-7xl my-4 mx-auto px-4 pb-12 sm:px-6 lg:px-8">
        <div class="my-1 sm:p-4">
            {{-- //ログインユーザーの時 --}}
            @if (auth()->check())
                <p class="my-1">{{ $user->name }}さん</p>
            @else
                {{-- ゲストの時 --}}
                <p class="mx-1 my-1 font-bold">ゲストユーザー</p>
                <p class="text-base text-sm">Xからのアカウント登録で投稿の作成が可能です。<br>
                    <span class="font-bold text-sky-700">投稿の作成にはアカウント登録が必須です。</span>
                </p>
            @endif
            <div class="card-wrap my-1">
                {{-- 並び替えリンク --}}
                <div style="max-width:150px;" class="ml-auto mt-2 justify-between flex gap-y-1">
                    <a href="{{ route('post.index', ['sort' => 'desc']) }}"
                        class="mr-2 {{ request('sort') === 'desc' ? 'font-bold' : '' }}">
                        新しい順
                    </a>
                    <a href="{{ route('post.index', ['sort' => 'asc']) }}"
                        class="{{ request('sort') === 'asc' ? 'font-bold' : '' }}">
                        古い順
                    </a>
                </div>
            </div>
            <div class="card-wrap my-4">
                @foreach ($posts as $post)
                    <div class="card s.shadow-lg">
                        <a class="card-link" href="{{ route('post.show', $post) }}">
                            <div class="card-user">
                                <img class="card-user__avatar mr-1"
                                    src="{{ isset($post->user) && $post->user->avatar !== 'user_default.jpg' ? asset($post->user->avatar) : asset('storage/avatar/user_default.jpg') }}"
                                    alt="">
                                <p class="card-user__name">{{ $post->user->name }}</p>
                            </div>
                            <div class="card-content">
                                <p class="card-content__date">投稿日:{{ $post->created_at->diffForHumans() }}</p>
                                <p class="card-content__title">{{ $post->title }}</p>
                                <p class="card-content__body">
                                    {{ Str::limit($post->body, 50, '...') }}
                                </p>
                                <img class="card-content__img mx-auto" src="{{ $post->image }}" alt="">
                            </div>
                        </a>
                        <div class="card-bottom">
                            @if (auth()->check())
                                <a class="card-button" href="{{ route('post.show', $post) }}">
                                    <div class="">コメントする</div>
                                </a>
                            @else
                                {{-- 未ログインの場合はアラートを表示して遷移を防ぐ --}}
                                <a href="javascript:void(0);" class="card-button" onclick="return showAlert();">
                                    <div class="">コメントする</div>
                                </a>
                            @endif
                            <div class="card-content__badges">
                                @if ($post->comments->count())
                                    <span class="badge">
                                        返信{{ $post->comments->count() }}件
                                    </span>
                                @endif
                                @if ($post->viewcount)
                                    <span class="badge-visits">
                                        閲覧回数{{ $post->viewcount }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                <!-- ページネーションリンク -->
                <div class="mx-auto w-full mb-4">
                    {{ $posts->links('vendor.pagination.tailwind') }}
                    <p class="text-center mt-2">
                        {{ $posts->firstItem() }}〜{{ $posts->lastItem() }}件を表示（全{{ $posts->total() }}件）</p>
                </div>
            </div>

        </div>
</x-app-layout>
