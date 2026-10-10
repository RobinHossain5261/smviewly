<?php

namespace App\Http\Controllers;

use App\Exceptions\SocialLookupException;
use App\Models\Faq;
use App\Models\Post;
use App\Services\SocialLookupService;
use App\Support\PlatformCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    public function __construct(protected SocialLookupService $lookup)
    {
    }

    public function index()
    {
        $posts = Post::published()->with('category')->latest()->take(3)->get();
        $faqs = Faq::orderBy('order_index')->get();
        $platforms = PlatformCatalog::all();
        $featuredTools = PlatformCatalog::featured();
        $platformMeta = [];
        foreach ($platforms as $slug => $meta) {
            $featured = collect($meta['tools'])->first(fn ($t) => ! empty($t['featured']));
            $platformMeta[$slug] = [
                'placeholder' => $meta['placeholder'],
                'default_tool' => $featured['slug'] ?? ($meta['tools'][0]['slug'] ?? 'profile-viewer'),
            ];
        }

        return view('welcome', compact('posts', 'faqs', 'platforms', 'featuredTools', 'platformMeta'));
    }

    public function tool($platform, $tool = null)
    {
        $platform = strtolower($platform);
        $meta = PlatformCatalog::get($platform);

        if (! $meta) {
            abort(404);
        }

        $faqs = Faq::orderBy('order_index')->get();

        if (is_null($tool)) {
            $tools = PlatformCatalog::hubTools($platform);

            return view('platform_hub', compact('platform', 'tools', 'faqs', 'meta'));
        }

        $toolConfig = PlatformCatalog::tool($platform, $tool);
        if (! $toolConfig) {
            abort(404);
        }

        $title = $meta['name'].' '.$toolConfig['name'];
        $description = trim(($toolConfig['desc'] ?? 'Browse public '.$meta['name'].' content privately.').' Free on smviewly (Social Media Viewer). No login required.');
        $seoTitle = $title.' | smviewly';
        $icon = $toolConfig['icon'] ?? 'ph-eye';
        $slug = $tool;
        $placeholder = PlatformCatalog::placeholder($platform, $tool);
        $similar = PlatformCatalog::similar($platform, $tool);

        return view('tool_detail', compact(
            'platform', 'slug', 'title', 'description', 'seoTitle', 'icon', 'similar', 'faqs', 'meta', 'toolConfig', 'placeholder'
        ));
    }

    public function search(Request $request)
    {
        $platforms = PlatformCatalog::slugs();

        $data = $request->validate([
            'username' => 'required|string|max:500',
            'platform' => ['required', 'string', Rule::in($platforms)],
            'tool' => 'required|string|max:80',
            'captcha' => 'required|captcha',
        ], [
            'captcha.captcha' => 'The security check result is incorrect.',
        ]);

        $platform = strtolower($data['platform']);
        $toolSlug = $data['tool'];

        if (! PlatformCatalog::tool($platform, $toolSlug)) {
            $toolSlug = PlatformCatalog::defaultTool($platform)['slug'] ?? $toolSlug;
        }

        $query = trim($data['username']);
        $query = ltrim($query, '@');

        try {
            $result = $this->lookup->lookup($platform, $toolSlug, $query);
        } catch (SocialLookupException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['search_error' => $e->getMessage()])->withInput();
        }

        $html = view('partials.live-results', ['result' => $result])->render();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        }

        return view('tools.results', ['result' => $result]);
    }

    public function searchPreview(Request $request)
    {
        return $this->search($request);
    }

    public function download(Request $request)
    {
        return $this->proxyRemoteMedia($request, asDownload: true);
    }

    public function media(Request $request)
    {
        return $this->proxyRemoteMedia($request, asDownload: false);
    }

    private function proxyRemoteMedia(Request $request, bool $asDownload)
    {
        $url = $request->input('url');

        if ($url) {
            $url = html_entity_decode((string) $url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $url = str_replace('&amp;', '&', $url);
        }

        if (! $url || (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://'))) {
            abort(400, 'Invalid URL');
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowed = [
            'fbcdn.net', 'fbsbx.com', 'fb.com', 'facebook.com',
            'twimg.com', 'x.com', 'twitter.com', 'threads.net', 'threads.com',
            'licdn.com', 'linkedin.com', 'unsplash.com', 'images.unsplash.com',
        ];
        $ok = false;
        foreach ($allowed as $suffix) {
            if ($host === $suffix || str_ends_with($host, '.'.$suffix)) {
                $ok = true;
                break;
            }
        }
        if (! $ok) {
            abort(400, 'URL host is not allowed');
        }

        $referer = 'https://www.facebook.com/';
        if (str_contains($host, 'twitter') || str_contains($host, 'twimg') || $host === 'x.com' || str_ends_with($host, '.x.com')) {
            $referer = 'https://x.com/';
        } elseif (str_contains($host, 'threads')) {
            $referer = 'https://www.threads.net/';
        } elseif (str_contains($host, 'linkedin') || str_contains($host, 'licdn')) {
            $referer = 'https://www.linkedin.com/';
        }

        try {
            $upstreamHeaders = [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                'Accept' => '*/*',
                'Referer' => $referer,
            ];

            if (! $asDownload && $request->headers->has('Range')) {
                $upstreamHeaders['Range'] = $request->header('Range');
            }

            $response = Http::timeout(60)
                ->withHeaders($upstreamHeaders)
                ->withOptions(['stream' => true])
                ->get($url);

            if ($response->successful()) {
                $contentType = $response->header('Content-Type') ?? 'image/jpeg';
                $filename = $this->guessMediaFilename($contentType);
                $headers = $this->mediaProxyHeaders($response, $contentType);
                $stream = $response->toPsrResponse()->getBody();

                $emitStream = function () use ($stream) {
                    while (! $stream->eof()) {
                        echo $stream->read(8192);

                        if (connection_aborted()) {
                            break;
                        }
                    }
                };

                if ($asDownload) {
                    return response()->streamDownload($emitStream, $filename, $headers);
                }

                return response()->stream($emitStream, $response->status(), $headers);
            }
        } catch (\Exception $e) {
            // fall through
        }

        if ($asDownload) {
            return redirect($url);
        }

        abort(502, 'Media could not be loaded');
    }

    private function guessMediaFilename(string $contentType): string
    {
        $stamp = time();

        if (str_contains($contentType, 'png')) {
            return 'viewer_media_'.$stamp.'.png';
        }

        if (str_contains($contentType, 'video') || str_contains($contentType, 'mp4')) {
            return 'viewer_media_'.$stamp.'.mp4';
        }

        if (str_contains($contentType, 'webp')) {
            return 'viewer_media_'.$stamp.'.webp';
        }

        return 'viewer_media_'.$stamp.'.jpg';
    }

    /**
     * @return array<string, string>
     */
    private function mediaProxyHeaders(\Illuminate\Http\Client\Response $response, string $contentType): array
    {
        $headers = [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=86400',
        ];

        foreach (['Content-Range', 'Content-Length', 'Accept-Ranges'] as $name) {
            $value = $response->header($name);

            if ($value) {
                $headers[$name] = $value;
            }
        }

        if (! isset($headers['Accept-Ranges']) && (str_contains($contentType, 'video') || str_contains($contentType, 'mp4'))) {
            $headers['Accept-Ranges'] = 'bytes';
        }

        return $headers;
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10|max:10000',
            'captcha' => 'required|string|max:20|captcha',
        ]);

        unset($data['captcha']);

        \App\Models\Contact::create($data);

        return back()->with('success', 'Your message has been received. Our team can now review it.');
    }

    public function reloadCaptcha()
    {
        return response()->json(['captcha' => captcha_img('easy')]);
    }
}
