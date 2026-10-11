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

    public function test_nested_cover_image_is_used_instead_of_provider_metadata(): void
    {
        $this->configureProvider();
        $cover = 'https://scontent.fbcdn.net/cover.jpg?a=1&b=2';
        Http::fake(['*' => Http::response(['success' => true, 'profile' => [
            'name' => 'Example Profile', 'username' => 'example',
            'cover_photo' => ['id' => 'photo-123', 'photo' => ['image' => ['uri' => str_replace('&', '&amp;', $cover)]]],
        ]])]);
        $html = $this->postJson('/search', $this->searchInput())->assertOk()->json('html');
        $this->assertStringContainsString('data-profile-cover', $html);
        $this->assertStringContainsString(e(route('viewer.media', ['url' => $cover])), $html);
        $this->assertStringNotContainsString('url=photo-123', $html);
        $this->assertStringContainsString('Open profile', $html);
    }

    public function test_missing_cover_uses_a_compact_fallback_without_cover_download_controls(): void
    {
        $this->configureProvider();
        Http::fake(['*' => Http::response(['success' => true, 'profile' => ['name' => 'No cover', 'username' => 'example']])]);
        $html = $this->postJson('/search', $this->searchInput())->assertOk()->json('html');
        $this->assertStringContainsString('is-missing-cover', $html);
        $this->assertStringNotContainsString('data-cover-actions', $html);
    }

    public function test_photo_viewer_opens_the_original_image_instead_of_the_thumbnail(): void
    {
        $original = 'https://scontent.fbcdn.net/full-photo.jpg';
        $thumbnail = 'https://scontent.fbcdn.net/small-preview.jpg';
        foreach (['partials.ig-tile', 'partials.media-tile'] as $partial) {
            $html = view($partial, [
                'item' => ['url' => $original, 'thumb' => $thumbnail, 'is_video' => false],
                'img' => fn ($url) => $url,
                'dl' => fn ($url) => $url,
            ])->render();
            $this->assertStringContainsString('src="'.$thumbnail.'"', $html);
            $this->assertStringContainsString('data-media-url="'.$original.'"', $html);
            $this->assertStringNotContainsString('data-media-url="'.$thumbnail.'"', $html);
        }
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
