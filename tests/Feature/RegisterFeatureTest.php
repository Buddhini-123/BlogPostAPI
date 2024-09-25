<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_with_missing_fields()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => '',
            'password' => '',
            'c_password' => '',
            'role' => ''
        ]);

        $response->assertStatus(403)
                 ->assertJson(['status' => 403])
                 ->assertJsonStructure([
                    'error' => [
                        'name',
                        'email',
                        'password',
                        'c_password',
                        'role'
                    ]
                 ]);
    }

    public function test_register_with_password_mismatch()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'c_password' => 'WrongPassword!', 
            'role' => 'user'
        ]);

        $response->assertStatus(403)
                 ->assertJsonStructure([
                    'status',
                    'error' => ['c_password']
                 ]);
    }

    public function test_register_with_existing_email()
    {
        User::factory()->create(['email' => 'test@example.com',]);

        $response = $this->postJson('/api/register', [
            'name' => 'Another User',
            'email' => 'test@example.com', // Existing email
            'password' => 'Password123!',
            'c_password' => 'Password123!',
            'role' => 'user'
        ]);

        // Assert that the response status is 403
        $response->assertStatus(403);
        $response->assertJsonStructure(['status', 'error' => ['email']]);
    }

    public function test_successful_registration_returns_token()
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
