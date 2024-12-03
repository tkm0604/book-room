<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        // 使用する画像のリスト
        $images =[
            'https://book-room.s3.ap-northeast-1.amazonaws.com/images/post_images/20241203083126_sample-book-1.jpg',
            'https://book-room.s3.ap-northeast-1.amazonaws.com/images/post_images/20241203083159_sample-book-2.jpg',
            'https://book-room.s3.ap-northeast-1.amazonaws.com/images/post_images/20241203083224_sample-book-3.jpg',
            'https://book-room.s3.ap-northeast-1.amazonaws.com/images/post_images/20241203083241_sample-book-4.jpg',

        ];
        return [
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'user_id' => '1',
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
            'image' => $this->faker->randomElement($images),
        ];
    }
}
