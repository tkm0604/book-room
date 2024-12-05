<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Role;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewVerifyEmail;

class UserTest extends TestCase
{
    use RefreshDatabase;
    // ユーザーモデルのインスタンスを作成
    public function test_CreateUser()
    {
        /**
         * A basic feature test example.
         */ $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),//パスワードをハッシュ化
            'avatar' => 'test.jpg',
            'twitter_id' => 'test',
            'twitter_token' => 'test',
            'twitter_token_secret' =>'test',
        ]);

        //データーベースに正しく保存されているか確認
        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@test.com',
            'avatar' => 'test.jpg',
            'twitter_id' => 'test',
            'twitter_token' => 'test',
            'twitter_token_secret' =>'test',
        ]);
         // パスワードのチェック
        $this->assertTrue(Hash::check('password', $user->password));
    }

    //Postモデルとのリレーションを確認
    public function test_user_has_many_posts(): void
    {
        // ユーザーモデルのインスタンスを作成
        $user = User::factory()->create();
        // ポストモデルのインスタンスを作成
        $post = Post::factory()->create(['user_id'=>$user->id]);
        //ユーザーがPOSTを持っているか確認
        $this->assertTrue($user->posts->contains($post));
    }

    // commentsリレーションのテスト
    public function test_user_has_many_comments(): void
    {
        // ユーザーモデルのインスタンスを作成
        $user = User::factory()->create();
        // コメントモデルのインスタンスを作成
        $comment = Comment::factory()->create(['user_id'=>$user->id]);
        //ユーザーがコメントを持っているか確認
        $this->assertTrue($user->comments->contains($comment));
    }

    //rolesリレーションのテスト
    public function test_user_has_many_roles(): void
    {
            // ユーザーモデルのインスタンスを作成
            $user = User::factory()->create();
            // ロールモデルのインスタンスを作成
            $role = Role::factory()->create();

            // ユーザーにロールをアタッチ
            $user->roles()->attach($role);
            $this->assertTrue($user->roles->contains($role));
    }

    //sendEmailVerificationNotificationのテスト
    public function test_sendEmailVerificationNotification(): void
    {
        // ユーザーモデルのインスタンスを作成
        $user = User::factory()->create();
        //メール通知をモック
        Notification::fake();

        //メソッドを呼び出す
        $user->sendEmailVerificationNotification();

        //メール通知が送信されたか確認
        Notification::assertSentTo($user, NewVerifyEmail::class);

    }

}
