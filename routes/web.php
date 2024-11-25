<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\RoleController;

// Route::get('/', function () {
//     return view('welcome');
// });



// TOPページを投稿一覧に設定
Route::get('/', [PostController::class, 'index'])->name('home');
//自分の投稿のみ表示
Route::get('post/mypost',[PostController::class, 'mypost'])->name('post.mypost');
//自分のコメント投稿のみ表示
Route::get('post/mycomment',[PostController::class, 'mycomment'])->name('post.mycomment');
// 投稿リソース用のルート
Route::resource('post', PostController::class);

// コメント用のルート
Route::post('/post/comment/store', [CommentController::class, 'store'])->name('comment.store');

//お問い合わせ
Route::get('contact/create', [ContactController::class, 'create'])->name('contact.create');
Route::post('contact/store', [ContactController::class, 'store'])->name('contact.store');

Route::middleware(['auth','can:admin'])->group(function(){
    Route::get('profile/index', [ProfileController::class, 'index'])->name('profile.index');
});


// 一般ユーザー用のプロフィールルート
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// 管理者用のプロフィールルート
Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/profile/adedit/{user}', [ProfileController::class, 'adedit'])->name('profile.adedit');
    Route::patch('/profile/adupdate/{user}', [ProfileController::class, 'adupdate'])->name('profile.adupdate');

    Route::patch('role/{user}',[RoleController::class,'attach'])->name('role.attach');
    ROute::patch('role/{user}/detach',[RoleController::class,'detach'])->name('role.detach');

    Route::delete('profile/{user}',[ProfileController::class,'addestroy'])->name('profile.addestroy');
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


require __DIR__.'/auth.php';
