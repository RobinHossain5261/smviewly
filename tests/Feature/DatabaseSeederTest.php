<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseeding_preserves_existing_content_settings_and_passwords(): void
    {
        $this->seed();
        $post = Post::firstOrFail();
        $post->update(['title' => 'Edited article']);
        SiteSetting::set('brand_name', 'Edited brand');
        $user = User::firstOrFail();
        $user->update(['password' => 'changed-password']);
        $password = $user->password;
        $count = Post::count();

        $this->seed();

        $this->assertSame($count, Post::count());
        $this->assertSame('Edited article', $post->fresh()->title);
        $this->assertSame('Edited brand', SiteSetting::get('brand_name'));
        $this->assertSame($password, $user->fresh()->password);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('categories', 3);
        $this->assertDatabaseCount('faqs', 4);
        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'changed-password',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get('/admin/dashboard')->assertOk();
        $this->get('/blog')->assertOk();
    }
}
