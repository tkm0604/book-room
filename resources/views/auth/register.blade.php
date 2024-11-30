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
            {{-- プレビュー用の画像がここに表示されます --}}
            <img id="avatar-preview" class="rounded-full w-24 h-24 object-cover"
                src="{{ asset('storage/avatar/user_default.jpg') }}" alt="">
            <x-input-label for="avatar" :value="__('プロフィール画像（任意・5MBまで）')" />

            <x-text-input id="avatar" class="block mt-1 w-full rounded-none" type="file" name="avatar"
                :value="old('avatar')" onchange="previewAvatar(event)" />
        </div>
        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('パスワード')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />
            <!-- パスワードルールの説明 -->
            <p class="text-sm text-gray-600 mt-1">
                パスワードは8文字以上で、大文字と小文字を含む必要があります。
            </p>
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
                <a href="{{ route('twitter.redirect') }}" class="btn btn-primary flex items-center">
                    <img style="width:40px" src="{{ asset('storage/common/x_logo.png') }}" alt="">
                    Xアカウントでログイン
                </a>
            </span>
        </label>
    </div>
</x-guest-layout>
<script>
    function previewAvatar(event) {
        const input = event.target;
        const preview = document.getElementById('avatar-preview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                // プレビュー用の画像のsrcをセット
                preview.src = e.target.result;
                // hiddenクラスを削除して表示
                preview.classList.remove('hidden');
            };

            reader.readAsDataURL(input.files[0]);
        } else {
            // ファイルが選択されていない場合、プレビューを隠す
            preview.src = "#";
            preview.classList.add('hidden');
        }
    }
</script>
