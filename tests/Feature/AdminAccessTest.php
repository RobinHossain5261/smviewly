<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_from_both_forms_and_open_management_pages(): void
    {
        $admin = User::factory()->create(['email' => 'super@admin.com', 'password' => 'test-admin-password']);
        foreach (['/signin', '/admin/login'] as $login) {
            $this->post($login, ['email' => 'SUPER@ADMIN.COM', 'password' => 'test-admin-password'])->assertRedirect(route('admin.dashboard'));
            foreach (['dashboard', 'contacts', 'posts', 'posts/create', 'categories', 'faqs', 'settings'] as $page) {
                $this->get('/admin/'.$page)->assertOk();
            }
            $this->post('/admin/logout');
        }
    }

    public function test_regular_users_go_home_even_with_an_admin_intended_url_and_cannot_mutate_admin_data(): void
    {
        $user = User::factory()->create(['email' => 'visitor@gmail.com', 'password' => 'test-user-password']);
        $contact = Contact::create(['name' => 'Visitor', 'email' => 'visitor@gmail.com', 'subject' => 'Help', 'message' => 'Please help with a lookup.']);
        foreach (['/signin', '/admin/login'] as $login) {
            $this->withSession(['url.intended' => route('admin.dashboard')])->post($login, ['email' => $user->email, 'password' => 'test-user-password'])->assertRedirect(route('home'));
            foreach (['dashboard', 'contacts', 'posts', 'categories', 'faqs', 'settings'] as $page) {
                $this->get('/admin/'.$page)->assertRedirect(route('home'));
            }
            $this->delete('/admin/contacts/'.$contact->id)->assertRedirect(route('home'));
            $this->post('/admin/settings', ['brand_name' => 'Unauthorized'])->assertRedirect(route('home'));
            $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
            $this->post('/admin/logout');
        }
    }

    public function test_wrong_admin_password_is_rejected_and_admin_email_cannot_be_registered(): void
    {
        User::factory()->create(['email' => 'super@admin.com', 'password' => 'test-admin-password']);
        $this->post('/signin', ['email' => 'super@admin.com', 'password' => 'incorrect'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/signup', ['name' => 'Impostor', 'email' => 'SUPER@ADMIN.COM', 'password' => 'test-password', 'password_confirmation' => 'test-password'])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_signup_logs_regular_user_in_and_inbox_search_filters_messages(): void
    {
        $this->post('/signup', ['name' => 'Visitor', 'email' => 'visitor@gmail.com', 'password' => 'test-password', 'password_confirmation' => 'test-password'])->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->get('/admin/dashboard')->assertRedirect(route('home'));
        $admin = User::factory()->create(['email' => 'super@admin.com']);
        Contact::create(['name' => 'Alice', 'email' => 'alice@example.com', 'subject' => 'Lookup issue', 'message' => 'Unique search match']);
        Contact::create(['name' => 'Bob', 'email' => 'bob@example.com', 'subject' => 'Other question', 'message' => 'Unrelated content']);
        $this->actingAs($admin)->get('/admin/contacts?q=Unique')->assertOk()->assertSee('alice@example.com')->assertDontSee('bob@example.com');
    }

    public function test_bulk_delete_removes_only_selected_messages(): void
    {
        $admin = User::factory()->create(['email' => 'super@admin.com']);
        $messages = collect(range(1, 3))->map(fn ($number) => Contact::create([
            'name' => 'Visitor '.$number, 'email' => 'visitor'.$number.'@example.com',
            'subject' => 'Question', 'message' => 'Please review my lookup.',
        ]));
        $this->actingAs($admin)->from('/admin/contacts?q=Question')->delete('/admin/contacts', [
            'ids' => [$messages[0]->id, $messages[1]->id],
        ])->assertRedirect('/admin/contacts?q=Question')->assertSessionHas('success');
        $this->assertDatabaseMissing('contacts', ['id' => $messages[0]->id]);
        $this->assertDatabaseMissing('contacts', ['id' => $messages[1]->id]);
        $this->assertDatabaseHas('contacts', ['id' => $messages[2]->id]);
    }

    public function test_bulk_delete_rejects_empty_or_invalid_selection_without_deleting_messages(): void
    {
        $admin = User::factory()->create(['email' => 'super@admin.com']);
        $contact = Contact::create(['name' => 'Visitor', 'email' => 'visitor@example.com', 'message' => 'Please help.']);
        $this->actingAs($admin)->delete('/admin/contacts', ['ids' => []])->assertSessionHasErrors('ids');
        $this->delete('/admin/contacts', ['ids' => [$contact->id, 999999]])->assertSessionHasErrors('ids.1');
        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
    }

    public function test_bulk_delete_requires_admin_access(): void
    {
        $contact = Contact::create(['name' => 'Visitor', 'email' => 'visitor@example.com', 'message' => 'Please help.']);
        $this->delete('/admin/contacts', ['ids' => [$contact->id]])->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->delete('/admin/contacts', ['ids' => [$contact->id]])->assertRedirect(route('home'));
        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
    }
}
