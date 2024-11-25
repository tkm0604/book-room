<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Role;
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $roles = Role::all();
        $admin = $request->user()->can('admin'); // 'admin' は Gate または Policy で定義

        return view('profile.edit', [
            'user' => $request->user(),
            'roles' => $roles,
            'admin' => $admin,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // アバター画像の保存
        if ($request->validated('avatar')) {
            // 既存のアバター画像を削除
            $user = User::find(auth()->user()->id);
            if ($user->avatar !== 'default.jpg') {
                $oldavatar = 'avatar/' . $user->avatar;
                Storage::disk('public')->delete($oldavatar);
            }

            $name = request()->file('avatar')->getClientOriginalName();
            $avatar = date('Ymd_His') . '_' . $name;
            request()->file('avatar')->storeAs('/avatar', $avatar, 'public');
            $request->user()->avatar = $avatar;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function index(): View
    {
        $users = User::all();
        return view('profile.index', compact('users'));
    }

    // 管理者用のプロフィール編集画面
    public function adedit(User $user): View
    {
        $admin = true;
        $roles = Role::all();
        return view('profile.edit', [
            'user' => $user,
            'admin' => $admin,
            'roles' => $roles,
        ]);
    }

    public function adupdate(User $user, Request $request): RedirectResponse
    {
        $inputs = $request->validate([
            'name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255', Rule::unique(User::class)->ignore($user)],
            'avatar' => ['file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        //avatarの保存
        if (request()->hasFile('avatar')) {
            // 既存のアバター画像を削除
            if ($user->avatar !== 'default.jpg') {
                $oldavatar = 'avatar/' . $user->avatar;
                Storage::disk('public')->delete($oldavatar);
            }
            $name = request()->file('avatar')->getClientOriginalName();
            $avatar = date('Ymd_His') . '_' . $name;
            request()->file('avatar')->storeAs('/avatar', $avatar, 'public');
            //avatarをデータに追加
            $user->avatar = $avatar;
        }
        $user->name = $inputs['name'];
        $user->email = $inputs['email'];
        $user->save();

        return redirect::route('profile.adedit',compact('user'))->with('status', 'profile-updated');
    }

public function addestroy(User $user){
    // ユーザーのアバターを削除
    if($user->avatar !== 'user_default.jpg'){
        $oldavatar = 'avatar/' . $user->avatar;
        Storage::disk('public')->delete($oldavatar);
    }
    $user->roles()->detach();
    $user->delete();
    return back()->with('message', 'ユーザーを削除しました');
}
}
