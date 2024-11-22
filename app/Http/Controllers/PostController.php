<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->get();
        $user = auth()->user();
        return view('post.index', compact('posts', 'user'));
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
        //画像の保存
        $original = $request->file('image')->getClientOriginalName(); //画像の名前を取得
        $name = date('YmdHis') . '_' . $original; //画像の名前を変更
        request()->file('image')->storeAs('public/images', $name); //画像を保存
        $post->image = $name;

        $post->save();
        return redirect()->route('post.create')->with('message', '投稿を作成しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
