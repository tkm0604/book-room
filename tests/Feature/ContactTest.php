<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Contact;

class ContactTest extends TestCase
{

    use RefreshDatabase;

    //Contactモデルのインスタンスが正しく作成され、データベースに保存されるかを確認
    public function test_create_contact(): void
    {
        $contact = Contact::factory()->create([
            'title' => 'John Doe',
            'email' => 'sample@samole.com',
            'body' => 'This is the body of my first post',
        ]);

        $this->assertDatabaseHas('contacts', [
            'title' => 'John Doe',
            'email' => 'sample@samole.com',
            'body' => 'This is the body of my first post',
        ]);
    }

    //必須フィールドが正しくバリデーションされるかを確認
    public function test_required_fields(): void
    {
        $response = $this->post('/contact/store', [
            'title' => '',
            'email' => '',
            'body' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'email', 'body']);
    }

    //titleフィールドが255文字以内でバリデーションされるかを確認
    public function test_title_max_length(): void
    {
        $response = $this->post('/contact/store', [
            'title' => str_repeat('a', 256), // 256文字のタイトル
            'email' => 'sample@samole.com',
            'body' => 'This is the body of my first post',
        ]);
        $response->assertSessionHasErrors(['title']);
    }

    //emailフィールドが255文字以内でバリデーションされるかを確認
    public function test_email_max_length(): void
    {
        $response = $this->post('/contact/store', [
            'title' => 'John Doe',
            'email' => str_repeat('a', 256) . '@example.com', // 256文字のemail
            'body' => 'This is the body of my first post',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    //bodyフィールドが1000文字以内でバリデーションされるかを確認
    public function test_body_max_length(): void
    {
        $response = $this->post('/contact/store', [
            'title' => 'John Doe',
            'email' => 'sample@sample.com',
            'body' => str_repeat('a', 1001), // 1001文字のbody
        ]);
        $response->assertSessionHasErrors(['body']);
    }
}
