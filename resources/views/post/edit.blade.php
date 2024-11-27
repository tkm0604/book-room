<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            投稿の編集
        </h2>
        <x-validation-errors class="mb-4 mt-4" :errorslist="$errors" />
        {{-- 投稿時のメッセージを表示 --}}
        <x-message :message="session('message')" />
    </x-slot>
    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mx-4 sm:p-8">
        <div id="app">
            <post-edit :post-id="{{ $post->id }}"></post-edit>
          </div>
    </div>
</x-app-layout>
