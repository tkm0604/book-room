<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Comment;
use App\Models\User;
use App\Models\Post;

class CommentTest extends TestCase
{

    //Commentモデルのインスタンスが正しく作成され、データベースに保存されるかを確認
    public function test_create_comment(): void
    {
        $user = User::factory()->create(); //ユーザーを作成
        $post = Post::factory()->create(); //ポストを作成
        $comment = Comment::factory()->create([
            'body' => 'This is the first comment',
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'This is the first comment',
            'user_id' => $user->id, //ユーザーIDが一致しているか
            'post_id' => $post->id, //ポストIDが一致しているか
        ]);
    }

    //CommentモデルがPostモデルと正しくリレーションを持っているかを確認
    public function test_comment_belongs_to_post(): void
    {
        $user = User::factory()->create(); //ユーザーを作成
        $post = Post::factory()->create(); //ポストを作成
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->assertInstanceOf(Post::class, $comment->post); // リレーションがPostモデルのインスタンスを返すことを確認
        $this->assertTrue($comment->post->is($post)); // リレーションが正しいポストを返すことを確認
    }

    //CommentモデルがUserモデルと正しくリレーションを持っているかを確認
    public function test_comment_belongs_to_user(): void
    {
        $user = User::factory()->create(); //ユーザーを作成
        $post = Post::factory()->create(); //ポストを作成
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->assertInstanceOf(User::class, $comment->user); // リレーションがUserモデルのインスタンスを返すことを確認
        $this->assertTrue($comment->user->is($user)); // リレーションが正しいユーザーを返すことを確認
    }
}
