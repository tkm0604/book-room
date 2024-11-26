<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        //userテーブルのデータ
        $attr =[
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

    // アバターの保存
    if ($request->hasFile('avatar')) {
        $name = $request->file('avatar')->getClientOriginalName(); // オリジナルファイル名
        $avatar = date('Ymd_His') . '_' . $name; // 日時を加えたユニークなファイル名
        $path = $request->file('avatar')->storeAs('images/avatar', $avatar, 's3'); // S3に保存
        $url = Storage::disk('s3')->url($path); // S3のURLを取得

        // アバターURLをデータに追加
        $attr['avatar'] = $url;
    } else {
        // アバターがアップロードされなかった場合はデフォルトの画像を設定
        $attr['avatar'] = 'storage/avatar/user_default.jpg';
    }


        $user = User::create($attr);

        event(new Registered($user));

        //役割付与
        $user->roles()->attach(2);

        Auth::login($user);

        return redirect(route('home'));
    }
}
