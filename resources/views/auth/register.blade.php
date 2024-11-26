<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('お名前')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <!-- Avatar -->
        <div class="mt-4">
            <x-input-label for="avatar" :value="__('プロフィール画像（任意・5MBまで）')" />

            <x-text-input id="avatar" class="block mt-1 w-full rounded-none" type="file" name="avatar"
                :value="old('avatar')" />
        </div>
        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('パスワード')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('パスワード（確認）')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}">
                {{ __('アカウントをお持ちですか？') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('アカウント作成') }}
            </x-primary-button>
        </div>
    </form>
            <!-- Xでログイン -->
            <div class="block mt-4">
                <label for="" class="inline-flex items-center">
                    <span class="ms-2 text-sm text-gray-600">
                        <a href="{{route('twitter.redirect') }}" class="btn btn-primary flex items-center">
                            <img style="width:40px" src="{{ asset('logo/x_logo.png') }}" alt="">
                            Xで登録
                        </a>
                    </span>
                </label>
            </div>
</x-guest-layout>
