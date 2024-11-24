<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->get();
        $user = auth()->user();
        return view('index', compact('posts', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('post.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $input = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max_mb_chars:130',//Xへの投稿の文字数制限
            'image' =>  ['required','file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],//画像のバリデーション
        ]);

        $post = new Post();

        $post->title = $request->title;
        $post->body = $request->body;
        $post->user_id = auth()->user()->id;
        //画像の名前を取得
        $original = $request->file('image')->getClientOriginalName();
        //画像の名前を変更
        $name = date('YmdHis') . '_' . $original;

        // S3に画像をアップロード
        $path = request()->file('image')->storeAs('images', $name, 's3');
         // S3のURLを取得してDBに保存
         $url = Storage::disk('s3')->url($path);
         $post->image = $url;

        // Postを保存
        $post->save();
        return redirect()->route('post.create')->with('message', '投稿を作成しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('post.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        return view('post.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $input = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max_mb_chars:130',//Xへの投稿の文字数制限
            'image' =>  ['required','file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],//画像のバリデーション
        ]);

        $post->title = $input['title'];
        $post->body = $input['body'];

        if($request->file('image')){
            $original = $request->file('image')->getClientOriginalName(); //画像の名前を取得
            $name = date('YmdHis') . '_' . $original; //画像の名前を変更
            // S3に画像をアップロード
            $path = request()->file('image')->storeAs('images', $name, 's3');
            // S3のURLを取得してDBに保存
            $url = Storage::disk('s3')->url($path);

            // 古い画像を削除
            if($post->image){
                 // S3のURLを画像パスに変換
                 $oldImagePath = parse_url($post->image, PHP_URL_PATH);
                 $oldImagePath = ltrim($oldImagePath, '/'); // パスの先頭にスラッシュがあれば削除
                // 古い画像をS3から削除
                Storage::disk('s3')->delete($oldImagePath);
            }

            // 新しい画像のURLを保存
            $post->image = $url;
        }

        $post->save();

        return redirect()->route('post.show', $post)->with('message', '投稿を更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // S3に画像が存在する場合、削除する
        if($post->image)
        {
            // S3のURLを画像パスに変換
            $imagePath = parse_url($post->image, PHP_URL_PATH);
            $imagePath = ltrim($imagePath, '/'); // パスの先頭にスラッシュがあれば削除
            // 画像をS3から削除
            Storage::disk('s3')->delete($imagePath);
        }
        $post->comments()->delete();
        $post->delete();
        return redirect()->route('home')->with('message', '投稿を削除しました');
    }

    public function mypost()
    {
        $user = auth()->user();
        $posts = Post::where('user_id',$user->id)->orderBy('created_at','desc')->get();
        return view('post.mypost', compact('posts'));
    }

    public function mycomment()
    {
        $user = auth()->user();
        $comments = Comment::where('user_id',$user->id)->orderBy('created_at','desc')->get();
        return view('post.mycomment', compact('comments'));
    }
}
