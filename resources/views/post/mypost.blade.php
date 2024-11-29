<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            投稿の一覧
        </h2>
        <x-message :message="session('message')" />
    </x-slot>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-4 sm:p-8">
            @if (count($posts) == 0)
                <p class="mt-4">
                    あなたはまだ投稿していません。
                </p>
            @else
            <div class="card-wrap my-8">
            @foreach ($posts as $post)
                <div class="card s.shadow-lg">
                    <a class="card-link" href="{{ route('post.show', $post )}}">
                        <div class="card-user">
                            <img class="card-user__avatar" src="{{ isset($post->user) && $post->user->avatar !== 'user_default.jpg' ? asset($post->user->avatar) : asset('storage/avatar/user_default.jpg')}}">
                            <p class="card-user__name">{{ $post->user->name }}</p>
                        </div>
                        <div class="card-content">
                            <p class="card-content__date">投稿日:{{ $post->created_at->diffForHumans() }}</p>
                            <p class="card-content__title">{{ $post->title }}</p>
                            <p class="card-content__body">{{ removeBookRoomTag(Str::limit($post->body, 50, '...')) }}</p>
                            <img class="card-content__img" src="{{ $post->image }}" alt="">
                        </div>
                    </a>
                    <div class="card-bottom">
                        @if (auth()->check())
                            <a class="card-button" href="{{ route('post.show', $post) }}">
                                <button class="">コメントする</button>
                            </a>
                        @else
                            {{-- 未ログインの場合はアラートを表示して遷移を防ぐ --}}
                            <a href="javascript:void(0);" class="card-button" onclick="return showAlert();">
                                <button class="">コメントする</button>
                            </a>
                        @endif
                        <div class="card-content__badges">
                            @if ($post->comments->count())
                                <span class="badge">
                                    返信{{ $post->comments->count() }}件
                                </span>
                            @endif
                            <span class="badge-visits">
                                閲覧回数10
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

    </div>
</x-app-layout>
