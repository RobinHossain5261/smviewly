<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ContactSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitted_message_is_saved_and_visible_to_authenticated_admin(): void
    {
        $key = Hash::make('abcd');
        Cache::put('captcha_'.md5($key), true, 60);
        $this->withSession(['captcha' => ['key' => $key, 'sensitive' => false, 'encrypt' => false]]);
        $message = 'The public page lookup returned no posts. Please review the page viewer tool.';
        $this->from('/contact')->post('/contact', [
            'name' => 'Test visitor', 'email' => 'visitor@example.com',
            'subject' => 'Page lookup question', 'message' => $message, 'captcha' => 'abcd',
        ])->assertRedirect('/contact')->assertSessionHas('success');
        $this->assertDatabaseHas('contacts', ['email' => 'visitor@example.com', 'message' => $message]);
        $this->actingAs(User::factory()->create(['email' => 'super@admin.com']));
        $this->get('/admin/contacts')->assertOk()->assertSee($message)->assertSee('visitor@example.com');
        $this->get('/admin/dashboard')->assertOk()->assertSee('Contact inbox')->assertSee($message);
    }

    public function test_invalid_security_code_does_not_save_a_message_and_inbox_requires_authentication(): void
    {
        $this->post('/contact', ['name' => 'Visitor', 'email' => 'visitor@example.com', 'message' => 'Please check the page tool.', 'captcha' => 'incorrect'])
            ->assertSessionHasErrors('captcha');
        $this->assertDatabaseCount('contacts', 0);
        $this->get('/admin/contacts')->assertRedirect('/admin/login');
    }
}

