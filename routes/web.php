<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\EnsureEmailIsVerified;

// Route::get('/', function () {
//     return view('welcome');
// });


// TOPページを投稿一覧に設定
Route::get('/', [PostController::class, 'index'])->name('home');
// 投稿表示（全てのユーザーが閲覧可能）
Route::get('post/{post}', [PostController::class, 'show'])->name('post.show');
// コメント用のルート
Route::post('/post/comment/store', [CommentController::class, 'store'])->name('comment.store');

// プライバシーポリシーページ
Route::view('/site-policy', 'site-policy')->name('site-policy');

//お問い合わせ
Route::get('contact/create', [ContactController::class, 'create'])->name('contact.create');
Route::post('contact/store', [ContactController::class, 'store'])->name('contact.store');

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('profile/index', [ProfileController::class, 'index'])->name('profile.index');
});


// 認証済みかつメール確認済みユーザー用ルート
Route::middleware(['auth', EnsureEmailIsVerified::class])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //自分の投稿のみ表示
    Route::get('post/mypost', [PostController::class, 'mypost'])->name('post.mypost');
    //自分のコメント投稿のみ表示
    Route::get('post/mycomment', [PostController::class, 'mycomment'])->name('post.mycomment');

    // 投稿データ取得用APIルート
    Route::get('/api/posts/{id}', [PostController::class, 'showApi']);

    // 投稿データ更新用APIルート
    Route::patch('/api/posts/{id}', [PostController::class, 'updateApi']);

    // 投稿リソース用のルート
    Route::resource('post', PostController::class)->except(['show']);
});


// 管理者用のプロフィールルート
Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/profile/adedit/{user}', [ProfileController::class, 'adedit'])->name('profile.adedit');
    Route::patch('/profile/adupdate/{user}', [ProfileController::class, 'adupdate'])->name('profile.adupdate');

    Route::patch('role/{user}', [RoleController::class, 'attach'])->name('role.attach');
    Route::patch('role/{user}/detach', [RoleController::class, 'detach'])->name('role.detach');

    Route::delete('profile/{user}', [ProfileController::class, 'addestroy'])->name('profile.addestroy');
});

// プロフィール用のルート
// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
//     Route::get('/profile/adedit/{user}', [ProfileController::class, 'adedit'])->name('profile.adedit');
//     Route::patch('/profile/adupdate/{user}',[ProfileController::class, 'adupdate'])->name('profile.adupdate');
//     // Route::get('profile/index', [ProfileController::class, 'index'])->name('profile.index');
// });

//Xのアカウント認証
// X認証のリダイレクト
Route::get('login/x', [SocialAuthController::class, 'redirectToProvider'])->name('twitter.redirect');
// X認証のコールバック
Route::get('login/x/callback', [SocialAuthController::class, 'handleProviderCallback'])->name('twitter.callback');

require __DIR__ . '/auth.php';
