<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            投稿の個別表示
        </h2>

        <x-message :message="session('message')" />

    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white w-full  rounded-2xl px-4 py-2 shadow-lg hover:shadow-2xl transition duration-500">
                        <div class="card post-show s.shadow-lg">
                            <a class="card-link" href="{{ route('post.show', $post) }}">
                                <div class="card-user">
                                    <img class="card-user__avatar mr-1"
                                        src="{{ isset($post->user) && $post->user->avatar !== 'user_default.jpg' ? asset($post->user->avatar) : asset('storage/avatar/user_default.jpg') }}">
                                    <p class="card-user__name">{{ $post->user->name }}</p>
                                </div>
                                <div class="card-content">
                                    <p class="card-content__date">投稿日:{{ $post->created_at->diffForHumans() }}</p>
                                    <p class="card-content__title">タイトル:{{ $post->title }}</p>

                                    {{-- 投稿削除編集ボタン  ここから --}}
                                    <div class="flex justify-end gap-x-2 mb-2">
                                        @can('delete',$post)
                                        <form method="post" action="{{ route('post.destroy', $post) }}">
                                            @csrf
                                            @method('delete')
                                            <x-primary-button class="bg-red-700 float-right"
                                                onClick="return confirm('本当に削除しますか？');">削除</x-primary-button>
                                        </form>
                                        @endcan
                                        @can('update',$post)
                                        <a href="{{ route('post.edit', $post) }}"><x-primary-button
                                                class="bg-teal-700 float-right">編集</x-primary-button></a>
                                        @endcan
                                    </div>
                                    {{-- 投稿削除編集ボタン  ここまで --}}

                                    <div class="card-content__wrapImg">
                                        <img class="card-content__img" src="{{ $post->image }}" alt="">
                                    </div>

                                    <p class="card-content__body">
                                        {{ removeBookRoomTag($post->body) }}</p>
                                </div>
                            </a>
                        </div>
                </div>

                {{-- コメント表示 --}}
                @foreach ($post->comments as $comment)
                <div
                    class="balloon6
                    @if (auth()->check() && $comment->user->id === auth()->user()->id) reverse @endif
                    ">
                    <div class="faceicon">
                        @if (auth()->check() && $comment->user->id === auth()->user()->id)
                            {{-- ログインユーザーのアバター --}}
                            <img class="card-user__avatar mr-1" src="{{ isset($comment->user) && $comment->user->avatar !== 'user_default.jpg' ? asset($comment->user->avatar) : asset('storage/avatar/user_default.jpg') }}" alt="">
                        @else
                            {{-- 返信者（他のユーザー）のアバター --}}
                            <img class="card-user__avatar mr-1" src="{{ isset($comment->user->avatar) && $comment->user->avatar !== 'user_default.jpg' ? asset($comment->user->avatar) : asset('storage/avatar/user_default.jpg') }}" alt="">
                        @endif
                    </div>
                    <div class="chatting">
                        <div class="says">
                            <p> {{ $comment->body }}</p>
                        </div>
                        <p class="says-name">{{ $comment->user->name }}</p>
                        <p class="says-date">{{ $comment->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @endforeach
            {{-- コメント表示終わり --}}


                {{-- コメント作成部分 --}}
                <div class="mt-4 mb-12">
                    <form method="post" action="{{ route('comment.store') }}"
                        @if (!auth()->check()) onsubmit="return showAlert();" @endif>
                        @csrf
                        <input type="hidden" name='post_id' value="{{ $post->id }}">
                        <textarea name="body"
                            class="bg-white w-full  rounded-2xl px-4  py-4 shadow-lg hover:shadow-2xl transition duration-500"
                            id="body" cols="30" rows="3" placeholder="コメントを入力してください">{{ old('body') }}</textarea>
                        <x-primary-button class="float-right mr-4 mb-12">コメントする</x-primary-button>
                    </form>
                </div>
                {{-- コメント部分終わり --}}
</x-app-layout>
