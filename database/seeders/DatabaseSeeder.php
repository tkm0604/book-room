<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment; 
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // 管理者ユーザーを追加 (投稿なし)
        User::factory()->create([
            'id' => 1,
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => hash::make('password1982'),
        ]);

        // ダミーユーザー3人を作成
        $users = User::factory(3)
            ->count(3)
            ->sequence(
                ['name' => 'Mike', 'email' => 'test01@test.com'],
                ['name' => 'たくや', 'email' => 'test02@test.com'],
                ['name' => 'Hiroko', 'email' => 'test03@test.com'],
            )->create();

        // 各ダミーユーザーに5件ずつ投稿を作成
        foreach ($users as $user) {
            Post::factory(5)->create(['user_id' => $user->id]);
        }

        // 各投稿にランダムなダミーコメントを作成 (合計3件のコメント)
        Comment::factory(15)->create();

        // $this->call(PostSeeder::class);
    }
}
