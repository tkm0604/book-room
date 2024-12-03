<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('プロフィール情報') }}

        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('アカウントのプロフィール情報とメールアドレスを更新してください。') }}
        </p>
    </header>
    <form
    id="{{ $admin ? 'admin-update-profile' : 'update-profile' }}"
    method="post"
    action="{{ $admin ? route('profile.adupdate', $user) : route('profile.update', $user) }}"
    class="mt-6 space-y-6"
    enctype="multipart/form-data">

    @csrf
    @method('patch')

    {{-- 共通フォーム要素 --}}
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    {{-- 通常のユーザーにのみメールアドレスを表示 --}}
    @if (auth()->check() && auth()->user()->twitter_id == '')
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            {{-- 確認メール送信フォーム --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('あなたのメールアドレスは未確認です。') }}
                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('確認メールを再送するには、こちらをクリック。') }}
                        </button>
                    </p>
                </div>
            @endif
        </div>
    @endif

    {{-- アバター更新 --}}
    {{-- @php
        dd($user->avatar);
    @endphp --}}
    <div>
        <img id="avatar-preview" class="rounded-full w-24 h-24 object-cover"
    src="{{ isset($user->avatar) && $user->avatar !== 'user_default.jpg'
        ? asset( $user->avatar)
        : asset('storage/avatar/user_default.jpg') }}"
    alt="プロフィール画像">

        <x-input-label for="avatar" :value="__('プロフィール画像（任意・5MBまで）')" />
        <x-text-input id="avatar" name="avatar" type="file" class="mt-1 block w-full" onchange="previewAvatar(event)" />
        <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('保存') }}</x-primary-button>
        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ __('Saved.') }}</p>
        @endif
    </div>
</form>

</section>
