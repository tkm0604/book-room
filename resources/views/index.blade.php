<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            投稿の一覧
        </h2>
        <x-message :message="session('message')" />
    </x-slot>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-4 sm:p-8">
            {{-- //ログインユーザーの時 --}}
            @if (auth()->check())
            <p class="mx-1 my-1">{{ $user->name }}さん</p>
        @else
            {{-- ゲストの時 --}}
            <p class="mx-1 my-1 font-bold">ゲストユーザー</p>
            <p class="text-base text-sm">Xからのアカウント登録で投稿の作成が可能です。
                <span class="font-bold text-sky-700">投稿の作成にはアカウント登録が必須です。</span>
            </p>
        @endif

            @foreach ($posts as $post )
            <div class="mt-4">
                <div class="bg-white w-full  rounded-2xl px-10 py-8 shadow-lg hover:shadow-2xl transition duration-500">
                    <div class="mt-4">
                        <div class="flex">
                            <div class="rounded-full w-12 h-12">
                                {{-- アバター表示 --}}
                                <img src="{{$post->user->avatar !== 'user_default.jpg' ? asset($user->avatar) : asset('storage/avatar/user_default.jpg') }}">
                                {{-- <img src="{{asset($post->user->avatar ?? 'storage/avatar/user_default.jpg')}}"> --}}
                            </div>
                            <h1 class="text-lg text-gray-700 font-semibold hover:underline cursor-pointer float-left pt-4">
                                <a href="{{route('post.show', $post)}}">{{ $post->title }}</a>
                            </h1>
                        </div>
                        <hr class="w-full">
                        <p class="mt-4 text-gray-600 py-4 break-words">{{ Str::limit($post->body,100,'...') }}</p>
                        <div class="text-sm font-semibold flex flex-row-reverse">
                            <p>{{ $post->user->name }} • {{ $post->created_at->diffForHumans() }}</p>
                        </div>
                        <hr class="w-full mb-2">
                        @if($post->comments->count())
                        <span class="badge">
                            返信{{ $post->comments->count() }}件
                        </span>
                        @else
                        <span>コメントはまだありません。</span>
                        @endif
                        <a href="{{route('post.show', $post)}}" style="color:white;">
                            <x-primary-button class="float-right">コメントする</x-primary-button>
                     </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    </div>
</x-app-layout>
