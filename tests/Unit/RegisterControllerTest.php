<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_fails_on_missing_fields()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => '',
            'password' => '',
            'c_password' => '',
            'role' => ''
        ]);

        $response->assertStatus(403)
                 ->assertJsonStructure([
                    'status',
                    'error' => [
                        'name',
                        'email',
                        'password',
                        'c_password',
                        'role'
                    ]
                 ]);
    }

    public function test_registration_fails_on_password_regex()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'abc123', 
            'c_password' => 'abc123',
            'role' => 'user'
        ]);

        $response->assertStatus(403)
                 ->assertJsonStructure([
                    'status',
                    'error' => ['password']
                 ]);
        $response->assertJsonFragment(['password' => ['The password must contain at least one letter, one number, and one special character (!, $, #, %).']]);
    }

    public function test_registration_fails_on_email_already_exists()
    {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com', // Already exists
            'password' => 'Password123!',
            'c_password' => 'Password123!',
            'role' => 'user'
        ]);

        $response->assertStatus(403)
                 ->assertJsonStructure([
                    'status',
                    'error' => ['email']
                 ]);
    }

    public function test_successful_registration()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'c_password' => 'Password123!',
            'role' => 'user'
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
