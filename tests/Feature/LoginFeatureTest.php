<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_missing_fields()
    {
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => ''
        ]);

        $response->assertStatus(403)
                 ->assertJson(['status' => 403])
                 ->assertJsonStructure(['error' => ['email', 'password']]);
    }

    public function test_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('correct_password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'wrong_password'
        ]);

        $response->assertStatus(403)
                 ->assertJson(['status' => 403, 'error' => 'Invalid username or password']);
    }

    public function test_login_successfully_returns_token()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('correct_password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'correct_password'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'status',
                    'success',
                    'data' => [
                        'remember_token',
                        'name'
                    ]
                 ]);
    }
}
