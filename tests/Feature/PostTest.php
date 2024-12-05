<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;

class PostTest extends TestCase
{

    use RefreshDatabase;

    //Postモデルのインスタンスが正しく作成され、データベースに保存されるかを確認
    public function test_create_post(): void
    {
        $user = User::factory()->create(); //ユーザーを作成
        $post = Post::factory()->create([
            'title' => 'My first post',
            'body' => 'This is the body of my first post',
            'image' => 'https://via.placeholder.com/150',
            'user_id' => $user->id,
            'tweet_id' => 1,
            'viewcount' => 0,
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'My first post',
            'body' => 'This is the body of my first post',
            'image' => 'https://via.placeholder.com/150',
            'user_id' => $user->id, //ユーザーIDが一致しているか
            'tweet_id' => 1,
            'viewcount' => 0,
        ]);
    }

    //PostモデルがUserモデルと正しくリレーションを持っているかを確認
    public function test_post_belongs_to_user(): void
    {
        $user = User::factory()->create(); //ユーザーを作成
        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $post->user); // リレーションがUserモデルのインスタンスを返すことを確認
        $this->assertTrue($post->user->is($user)); // リレーションが正しいユーザーを返すことを確認
    }

    //PostモデルがCommentモデルと正しくリレーションを持っているかを確認
    public function test_post_has_many_comments(): void
    {
        $user = User::factory()->create(); //ユーザーを作成
        $post = Post::factory()->create(); //ポストを作成
        //コメントを2つ作成
        $comment1 = $post->comments()->create([
            'body' => 'This is the first comment',
            'user_id' => $user->id,
        ]);
        $comment2 = $post->comments()->create([
            'body' => 'This is the second comment',
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $post->comments); // リレーションがIlluminate\Database\Eloquent\Collectionを返すことを確認
        $this->assertInstanceOf('App\Models\Comment', $post->comments->random()); // リレーションがCommentモデルのインスタンスを返すことを確認
        $this->assertEquals(2, $post->comments->count()); // リレーションが正しいコメントを返すことを確認
    }

}
