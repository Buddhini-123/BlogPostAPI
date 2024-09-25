<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_fails_on_missing_email_and_password()
    {
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(403)
                 ->assertJsonStructure([
                    'status',
                    'error' => [
                        'email',
                        'password'
                    ]
                 ]);
    }

    public function test_invalid_email_format_fails_validation()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
                 ->assertJsonStructure([
                    'status',
                    'error' => ['email']
                 ]);
    }

    public function test_invalid_credentials_return_error()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct_password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(403)
                 ->assertJson(['status' => 403, 'error' => 'Invalid username or password']);
    }

    public function test_successful_login_returns_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct_password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'correct_password',
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
