<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user
        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_create_post()
    {
        $response = $this->actingAs($this->user)->postJson('/api/posts', [
            'title' => 'Test Title',
            'body' => 'Test Body',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => 'Post created successfully']);
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Title',
            'body' => 'Test Body',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_post()
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Test Title',
            'body' => 'Test Body',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Unauthorized. Invalid or missing token.']);
    }

    public function test_authenticated_user_can_update_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
            'body' => 'Updated Body',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => 'Post updated successfully']);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_authenticated_user_can_delete_own_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => 'Post deleted successfully']);
        $this->assertDeleted($post);
    }

    public function test_user_cannot_delete_others_post()
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403)
                 ->assertJson(['error' => 'You are not authorized to delete this post']);
    }

    public function test_admin_can_delete_any_post()
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($adminUser)->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => 'Post deleted successfully']);
        $this->assertDeleted($post);
    }

    public function test_can_get_all_posts_for_authenticated_user()
    {
        $post1 = Post::factory()->create(['user_id' => $this->user->id]);
        $post2 = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/posts');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'posts');
    }

    public function test_can_get_post_by_id()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
                 ->assertJson(['post' => [
                     'id' => $post->id,
                     'title' => $post->title,
                     'body' => $post->body,
                 ]]);
    }

    public function test_can_get_published_posts()
    {
        Post::factory()->create(['status' => 'published']);
        Post::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($this->user)->getJson('/api/posts/published');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'posts');
    }

    public function test_can_search_posts()
    {
        Post::factory()->create(['title' => 'Searchable Title']);
        Post::factory()->create(['title' => 'Other Title']);

        $response = $this->actingAs($this->user)->getJson('/api/posts/search?title=Searchable');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'posts');
    }
}
