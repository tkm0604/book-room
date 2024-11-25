<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            投稿の個別表示
        </h2>

        <x-message :message="session('message')" />

    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="sm:p-8 sm:mx-4">
            <div class="sm:px-10 mt-4">
                <div class="bg-white w-full  rounded-2xl px-10 py-8 shadow-lg hover:shadow-2xl transition duration-500">
                    <div class="mt-4">
                        <div class="bg-white w-full  rounded-2xl px-10 pt-2 pb-8 shadow-lg hover:shadow-2xl transition duration-500">
                            <div class="flex justify-between items-center mt-4">
                                <div class="flex">
                                    <div class="rounded-full w-20">
                                        {{-- アバター表示 --}}
                                        <img class="rounded-full " src="{{asset($post->user->avatar ?? 'user_default.jpg')}}">
                                    </div>
                                    <h1 class="text-lg text-gray-700 font-semibold float-left pt-4">
                                        {{ $post->title }}
                                    </h1>
                                </div>
                            <div class="flex gap-x-2">
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
                        </div>
                        <hr class="w-full">
                        <p  class="mt-4 text-gray-600 py-4 break-words">{{ $post->body }}</p>
                        @if ($post->image)
                            <div>
                              <p> {{ $post->image }}</p>
                            </div>
                            <div>
                                <img src="{{ $post->image }}" alt="画像">
                            </div>
                        @endif
                        <div class="text-sm font-semibold flex flex-row-reverse">
                            <p> {{ $post->user->name }} • {{ $post->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
                {{-- コメント部分 --}}
                <div class="mt-4 mb-12">
                    <form method="post" action="{{ route('comment.store') }}">
                        @csrf
                        <input type="hidden" name='post_id' value="{{ $post->id }}">
                        <textarea name="body"
                            class="bg-white w-full  rounded-2xl px-4 mt-4 py-4 shadow-lg hover:shadow-2xl transition duration-500"
                            id="body" cols="30" rows="3" placeholder="コメントを入力してください">{{ old('body') }}</textarea>
                        <x-primary-button class="float-right mr-4 mb-12">コメントする</x-primary-button>
                    </form>
                </div>
                {{-- コメント部分終わり --}}

                {{-- コメント表示 --}}
                @foreach ($post->comments as $comment)
                <div class="bg-white w-full  rounded-2xl px-10 py-8 shadow-lg mt-8 whitespace-pre-line">
                    {{$comment->body}}
                    <div class="text-sm font-semibold flex flex-row-reverse">
                        <p class="float-left pt-4"> {{ $comment->user->name }} • {{$comment->created_at->diffForHumans()}}</p>
                        {{-- アバター追加 --}}
                        <span class="rounded-full w-12 h-12">
                        <img src="{{asset($comment->user->avatar ?? 'user_default.jpg')}}">
                        </span>
                    </div>
                </div>
                @endforeach
                {{-- コメント表示ここまで --}}
            </div>
        </div>
    </div>
</x-app-layout>
