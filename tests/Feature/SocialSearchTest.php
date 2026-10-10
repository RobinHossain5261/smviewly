<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Services\SocialApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialSearchTest extends TestCase
{
    use RefreshDatabase;

    private function searchInput(): array
    {
        $key = Hash::make('abcd');
        Cache::put('captcha_'.md5($key), true, 60);
        $this->withSession(['captcha' => ['key' => $key, 'sensitive' => false, 'encrypt' => false]]);

        return ['username' => 'facebook', 'platform' => 'facebook', 'tool' => 'page-viewer', 'captcha' => 'abcd'];
    }

    private function configureProvider(): void
    {
        SiteSetting::set('social_api_base_url', 'https://social.example');
        SiteSetting::set('social_api_token', 'test-token');
        Http::preventStrayRequests();
    }

    public function test_homepage_includes_search_handler(): void
    {
        $this->get('/')->assertOk()->assertSee("fetch(searchForm.action", false);
    }

    public function test_homepage_displays_redirected_errors(): void
    {
        // JSON sessions hydrate error bags from their serialized representation.
        $errors = ['default' => ['messages' => ['search_error' => ['Provider is unavailable.']], 'format' => ':message']];
        $this->withSession(['errors' => $errors])->get('/')->assertSee('Provider is unavailable.');
    }

    public function test_invalid_captcha_is_reported_without_contacting_provider(): void
    {
        Http::fake();
        $this->postJson('/search', [
            'username' => 'facebook', 'platform' => 'facebook', 'tool' => 'page-viewer', 'captcha' => 'wrong',
        ])->assertUnprocessable()->assertJsonValidationErrors('captcha');
        Http::assertNothingSent();
    }

    public function test_provider_connection_failure_is_returned_as_visible_search_error(): void
    {
        $this->configureProvider();
        Http::fake(['*' => Http::failedConnection()]);
        $this->postJson('/search', $this->searchInput())
            ->assertUnprocessable()
            ->assertJson(['success' => false, 'message' => 'Could not reach the social data API. Please try again.']);
    }

    public function test_successful_search_returns_rendered_results(): void
    {
        $this->configureProvider();
        Http::fake(['*' => Http::response(['success' => true, 'profile' => ['name' => 'Example Page', 'username' => 'facebook']])]);
        $response = $this->postJson('/search', $this->searchInput())
            ->assertOk()->assertJson(['success' => true]);
        $this->assertStringContainsString('Example Page', $response->json('html'));
    }

    public function test_parallel_sections_keep_names_and_skip_failed_connections(): void
    {
        $this->configureProvider();
        Http::fake(fn ($request) => $request['content'] === 'photos'
            ? Http::failedConnection()
            : Http::response(['success' => true, 'posts' => [['id' => '123']]]));

        $result = app(SocialApiClient::class)->lookupMany('facebook', [
            'posts' => ['content' => 'posts'], 'photos' => ['content' => 'photos'],
        ]);

        $this->assertSame(['posts'], array_keys($result));
        $this->assertSame('123', $result['posts']['posts'][0]['id']);
    }
}
