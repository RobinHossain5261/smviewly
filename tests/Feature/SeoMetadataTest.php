<?php

namespace Tests\Feature;

use App\Support\PlatformCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_tool_has_unique_metadata_and_a_canonical_url(): void
    {
        $descriptions = [];

        foreach (PlatformCatalog::all() as $platform => $meta) {
            foreach ($meta['tools'] as $tool) {
                $url = route('viewer.tool', ['platform' => $platform, 'tool' => $tool['slug']]);
                $response = $this->get($url);
                $response->assertOk();
                $response->assertSee('<link rel="canonical" href="'.$url.'">', false);
                preg_match('/<meta name="description" content="([^"]+)"/', $response->getContent(), $matches);
                $this->assertNotEmpty($matches[1] ?? null, $url);
                $this->assertNotContains($matches[1], $descriptions, $url);
                $descriptions[] = $matches[1];
                $response->assertSee('content="'.e(strtolower($meta['name'].' '.$tool['name'])).', public ', false);
                $response->assertSee('Example:');
                $response->assertSee('What input does '.$meta['name'].' '.$tool['name'].' accept?');
                $response->assertSee('What does '.$meta['name'].' '.$tool['name'].' show?');
            }
        }
    }

    public function test_public_pages_have_metadata_and_auth_pages_are_not_indexable(): void
    {
        foreach (['/', '/tools', '/about', '/contact', '/faqs', '/blog', '/privacy-policy', '/terms-of-use'] as $path) {
            $response = $this->get($path);
            $response->assertOk();
            $response->assertSee('<meta name="description" content="', false);
            $response->assertSee('<meta name="keywords" content="', false);
            $response->assertSee('<link rel="canonical" href="', false);
        }

        foreach (['/signin', '/signup', '/admin/login'] as $path) {
            $this->get($path)->assertOk()->assertSee('content="noindex, nofollow"', false);
        }
    }

    public function test_blog_pagination_has_its_own_canonical_without_tracking_parameters(): void
    {
        $this->get('/blog?page=2&utm_source=test')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('blog.index', ['page' => 2]).'">', false);
    }
}
