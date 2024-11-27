<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            投稿の作成
        </h2>
        <x-validation-errors class="mb-4 mt-4" :errorslist="$errors" />
        {{-- 投稿時のメッセージを表示 --}}
        <x-message :message="session('message')" />
    </x-slot>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-4 my-4 sm:p-8">
            <div id="app"></div> <!-- Vue.jsをマウントするポイント -->
        </div>
</x-app-layout>
