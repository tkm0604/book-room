<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //未ログインユーザーの場合はエラーを返す
        if(!auth()->check()){
            return back()->with('error','ログインしてください');
        }

        $inputs=request()->validate([
            'body'=>'required|max:1000',
        ]);

        $comment=Comment::create([
            'body'=>$inputs['body'],
            'post_id'=>$request->post_id,
            'user_id'=>auth()->user()->id,
        ]);

        //処理後は元のページに戻る
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
