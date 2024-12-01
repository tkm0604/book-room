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
        return [
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'user_id' => '80',
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
            'image' => 'https://book-room.s3.ap-northeast-1.amazonaws.com/images/20241125163314_logo.png',
        ];
    }
}
