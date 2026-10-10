<?php

namespace App\Services;

use App\Exceptions\SocialLookupException;
use App\Support\PlatformCatalog;

class SocialLookupService
{
    public function __construct(protected SocialApiClient $client)
    {
    }

    public function lookup(string $platform, string $toolSlug, string $query): array
    {
        $meta = PlatformCatalog::get($platform);
        $tool = PlatformCatalog::tool($platform, $toolSlug);

        if (! $meta || ! $tool) {
            throw SocialLookupException::failed('Unknown platform or tool.');
        }

        $payload = $this->payload($meta, $tool, $query);
        $raw = $this->client->lookup($meta['api'], $payload);

        if (! empty($raw['requires_login'])) {
            throw SocialLookupException::failed(
                $raw['error'] ?? 'This section needs a logged-in session on the data API. Try a public profile, posts, or search tool instead.'
            );
        }

        if (isset($raw['success']) && $raw['success'] === false) {
            throw SocialLookupException::failed((string) ($raw['error'] ?? 'Nothing was found for that query.'));
        }

        if (($meta['api'] ?? '') === 'facebook' && ($tool['intent'] ?? '') === 'view' && ($tool['content'] ?? '') === 'profile') {
            $raw = $this->fetchFacebookSections($meta, $tool, $query, $raw);
        }

        if (($meta['api'] ?? '') === 'twitter' && ($tool['intent'] ?? '') === 'view' && ($tool['content'] ?? '') === 'profile') {
            $raw = $this->fetchTwitterSections($meta, $tool, $query, $raw);
        }

        return $this->normalize($platform, $tool, $query, $raw);
    }

    /**
     * X profiles need separate calls for the timeline tabs (tweets, media, replies).
     */
    private function fetchTwitterSections(array $meta, array $tool, string $query, array $raw): array
    {
        $basePayload = [
            $meta['query_key'] => $query,
            'entity' => $tool['entity'] ?? 'profile',
        ];

        $payloads = [];
        foreach (['tweets', 'media', 'replies', 'retweets'] as $section) {
            $payloads[$section] = $basePayload + ['content' => $section];
        }

        $responses = $this->client->lookupMany($meta['api'], $payloads);

        foreach ($responses as $section => $sectionRaw) {
            foreach ($sectionRaw as $key => $value) {
                if (in_array($key, ['success', 'entity', 'content', 'query', 'url', 'profile', 'pinned_tweet', 'job_id', 'method', 'authenticated', 'requires_login', 'bytes', 'egress', 'input', 'persisted', 'use_proxy', 'proxy_session', 'user_id', 'timeline_size', 'pages_fetched', 'channel'], true)) {
                    continue;
                }
                if ($key === 'tweets') {
                    $existing = [];
                    foreach ($raw['tweets'] ?? [] as $t) {
                        $existing[$this->postKey($t)] = true;
                    }
                    foreach (is_array($value) ? $value : [] as $t) {
                        $k = $this->postKey($t);
                        if (! isset($existing[$k])) {
                            $existing[$k] = true;
                            $raw['tweets'][] = $t;
                        }
                    }
                } elseif (! isset($raw[$key])) {
                    $raw[$key] = $value;
                }
            }
        }

        return $raw;
    }

    /**
     * Stable dedupe key for a raw post row.
     */
    private function postKey(array $post): string
    {
        return (string) ($post['url'] ?? $post['permalink'] ?? $post['id'] ?? $post['post_id'] ?? json_encode($post['text'] ?? null));
    }

    /**
     * A Facebook profile page needs one call per section. Fire the remaining
     * tabs in parallel and merge whatever comes back into the main payload.
     */
    private function fetchFacebookSections(array $meta, array $tool, string $query, array $raw): array
    {
        $basePayload = [
            $meta['query_key'] => $query,
            'entity' => $tool['entity'] ?? 'page',
        ];

        $sections = ['posts', 'photos', 'reels', 'videos', 'about', 'followers'];

        $payloads = [];
        foreach ($sections as $section) {
            $payloads[$section] = $basePayload + ['content' => $section];
        }

        $responses = $this->client->lookupMany($meta['api'], $payloads);

        foreach ($responses as $section => $sectionRaw) {
            foreach ($sectionRaw as $key => $value) {
                if (in_array($key, ['success', 'entity', 'content', 'query', 'url', 'profile', 'job_id', 'method', 'authenticated', 'requires_login', 'bytes', 'egress', 'input', 'persisted', 'use_proxy', 'use_floppydata', 'proxy_session'], true)) {
                    continue;
                }
                if ($key === 'posts' && isset($raw['posts'])) {
                    $existing = [];
                    foreach ($raw['posts'] as $post) {
                        $existing[$this->postKey($post)] = true;
                    }
                    foreach (is_array($value) ? $value : [] as $post) {
                        $k = $this->postKey($post);
                        if (! isset($existing[$k])) {
                            $existing[$k] = true;
                            $raw['posts'][] = $post;
                        }
                    }
                } elseif ($key === 'people' && isset($raw['people'])) {
                    $raw['people'] = array_merge($raw['people'], is_array($value) ? $value : []);
                } elseif (in_array($key, ['photos', 'reels', 'videos', 'about'], true)) {
                    // The profile call returns empty arrays for these tabs; the
                    // dedicated section call has the real items — replace.
                    if (is_array($value) && $value !== []) {
                        $raw[$key] = $value;
                    }
                } elseif (! isset($raw[$key])) {
                    $raw[$key] = $value;
                }
            }
        }

        return $raw;
    }

    private function payload(array $meta, array $tool, string $query): array
    {
        $content = $tool['content'] ?? 'profile';


        if (($meta['api'] ?? '') === 'linkedin' && ($tool['entity'] ?? '') === 'job' && $content === 'detail') {
            if (preg_match('/(\d{5,})(?:\/)?(?:\?.*)?$/', $query, $m)) {
                $query = $m[1];
            }
        }

        $payload = [
            $meta['query_key'] => $query,
            'content' => $content,
        ];

        if (! empty($tool['entity'])) {
            $payload['entity'] = $tool['entity'];
        }

        return $payload;
    }

    private function normalize(string $platform, array $tool, string $query, array $raw): array
    {
        $profile = is_array($raw['profile'] ?? null) ? $raw['profile'] : [];
        $company = is_array($raw['company'] ?? null) ? $raw['company'] : [];
        $job = is_array($raw['job'] ?? null) ? $raw['job'] : [];
        $media = is_array($raw['media'] ?? null) ? $raw['media'] : [];
        $tweet = is_array($raw['tweet'] ?? null) ? $raw['tweet'] : [];
        $author = is_array($raw['author'] ?? null) ? $raw['author'] : [];
        $owner = is_array($media['owner'] ?? null) ? $media['owner'] : [];
        $intent = $tool['intent'] ?? 'view';
        $focus = $tool['focus'] ?? 'media';
        $isMediaLookup = $this->isAssoc($media) && ($owner !== [] || isset($media['shortcode']) || isset($media['product_type']));
        $isTweetLookup = $tweet !== [];

        $handle = $this->firstString([
            $owner['username'] ?? null,
            $author['username'] ?? null,
            $tweet['author'] ?? null,
            $profile['username'] ?? null,
            $profile['handle'] ?? null,
            $profile['name'] ?? null,
            $company['name'] ?? null,
            $job['title'] ?? null,
            $raw['username'] ?? null,
            $raw['query'] ?? null,
            $query,
        ]);

        $title = $this->firstString([
            $owner['full_name'] ?? null,
            $author['name'] ?? null,
            $tweet['author_name'] ?? null,
            $profile['full_name'] ?? null,
            $profile['name'] ?? null,
            $company['name'] ?? null,
            $job['title'] ?? null,
            $handle,
        ]);

        $caption = $this->firstString([
            is_string($media['caption'] ?? null) ? $media['caption'] : null,
            $tweet['text'] ?? null,
        ]);

        $result = [
            'platform' => $platform,
            'tool' => $tool['slug'],
            'tool_name' => $tool['name'],
            'intent' => $intent,
            'focus' => $focus,
            'download_label' => $tool['download_label'] ?? 'Download',
            'query' => $query,
            'handle' => ltrim((string) $handle, '@'),
            'title' => $title,
            'caption' => $caption,
            'bio' => ($isMediaLookup || $isTweetLookup) ? '' : $this->firstString([
                $profile['biography'] ?? null,
                $profile['bio'] ?? null,
                $author['biography'] ?? null,
                $profile['description'] ?? null,
                $company['description'] ?? null,
                $job['description'] ?? null,
            ]),
            'avatar' => $this->firstMediaString([
                $owner['profile_pic'] ?? null,
                $author['profile_image'] ?? null,
                $profile['profile_pic'] ?? null,
                $profile['profile_pic_url'] ?? null,
                $profile['profile_picture'] ?? null,
                $profile['profile_image'] ?? null,
                $profile['image_url'] ?? null,
                $profile['picture'] ?? null,
                $profile['avatar'] ?? null,
                $profile['image'] ?? null,
                $company['logo'] ?? null,
                $company['logo_url'] ?? null,
                $company['image'] ?? null,
                $company['image_url'] ?? null,
            ]),
            'url' => $this->firstString([
                $raw['url'] ?? null,
                $tweet['url'] ?? null,
                $profile['url'] ?? null,
                $profile['external_url'] ?? null,
                $company['url'] ?? null,
                $job['url'] ?? null,
                $this->isSocialPermalink((string) ($media['url'] ?? '')) ? $media['url'] : null,
            ]),
            'verified' => (bool) ($owner['is_verified'] ?? $author['verified'] ?? $author['is_blue_verified'] ?? $profile['is_verified'] ?? $profile['verified'] ?? false),
            'private' => (bool) ($profile['is_private'] ?? false),
            'stats' => $this->stats($platform, $raw, $profile, $company, $media, $isMediaLookup, $tweet, $isTweetLookup),
            'media' => $this->collectMedia($raw, $tool),
            'posts' => $focus === 'avatar' ? [] : $this->collectPosts($raw),
            'people' => $this->collectPeople($raw),
            'items' => $this->collectItems($raw),
            'highlights' => $this->collectHighlights($raw),
            'detail' => ($intent === 'download' || $isMediaLookup)
                ? []
                : $this->collectDetail($platform, $raw, $profile, $company, $job, $media),
            'category' => $this->firstString([
                $profile['category'] ?? null,
                $profile['business_category'] ?? null,
                $company['industry'] ?? null,
                $job['company'] ?? null,
            ]),
            'bio_links' => $this->collectBioLinks($profile),
            'cover' => $this->firstMediaString([
                $profile['cover'] ?? null,
                $profile['cover_url'] ?? null,
                $profile['cover_photo'] ?? null,
                $profile['cover_picture'] ?? null,
                $profile['banner'] ?? null,
                $profile['profile_banner'] ?? null,
                $company['cover'] ?? null,
                $company['cover_url'] ?? null,
                $company['cover_picture'] ?? null,
                $company['banner'] ?? null,
            ]),
        ];

        if ($result['avatar'] === '' && $result['media'] !== []) {
            $result['avatar'] = $result['media'][0]['thumb'] ?? '';
        }

        $result['photos'] = array_values(array_filter($result['media'], fn (array $item) => empty($item['is_video'])));
        $result['reels'] = array_values(array_filter($result['media'], fn (array $item) => ! empty($item['is_reel'])));
        $result['videos'] = array_values(array_filter($result['media'], fn (array $item) => ! empty($item['is_video']) && empty($item['is_reel'])));
        $result['stories'] = $this->ingestRows($this->asList($raw['stories'] ?? []));

        if ($platform === 'facebook') {
            // Facebook pages repeat the same photo at multiple sizes (and the profile pic
            // shows up again in photos). Keep the largest copy of each photo.
            $photoBase = fn (array $item) => strtolower((string) basename(parse_url((string) ($item['thumb'] ?: $item['url']), PHP_URL_PATH)));
            $photoSize = function (array $item) use ($photoBase): int {
                $url = (string) ($item['thumb'] ?: $item['url']);
                parse_str((string) parse_url($url, PHP_URL_QUERY), $qs);
                $w = (int) ($qs['width'] ?? 0);
                $h = (int) ($qs['height'] ?? 0);
                if (preg_match('~[?&](?:ctp|stp)=[^&]*s(\d+)x(\d+)~', $url, $m)) {
                    $w = max($w, (int) $m[1]);
                    $h = max($h, (int) $m[2]);
                }

                return max($w * $h, 1);
            };
            usort($result['photos'], fn (array $a, array $b) => $photoSize($b) <=> $photoSize($a));
            $result['photos'] = $this->dedupeBy($result['photos'], $photoBase);
            $avatarBase = $result['avatar'] !== '' ? strtolower((string) basename(parse_url($result['avatar'], PHP_URL_PATH))) : '';
            if ($avatarBase !== '') {
                $result['photos'] = array_values(array_filter($result['photos'], fn (array $item) => $photoBase($item) !== $avatarBase));
            }
            $coverBase = $result['cover'] !== '' ? strtolower((string) basename(parse_url($result['cover'], PHP_URL_PATH))) : '';
            if ($coverBase !== '') {
                $result['photos'] = array_values(array_filter($result['photos'], fn (array $item) => $photoBase($item) !== $coverBase));
            }

            // The same video is often served from multiple CDN hosts; keep one copy.
            $videoKey = fn (array $item) => (string) parse_url((string) ($item['url'] ?: $item['thumb']), PHP_URL_PATH);
            $result['videos'] = $this->dedupeBy($result['videos'], $videoKey);
            $result['reels'] = $this->dedupeBy($result['reels'], $videoKey);
        }

        if ($result['cover'] === '' && $platform !== 'facebook' && $result['photos'] !== []) {
            $result['cover'] = $result['photos'][0]['thumb'] ?? $result['photos'][0]['url'] ?? '';
        }

        $result['counts'] = [
            'photos' => count($result['photos']),
            'videos' => count($result['videos']),
            'reels' => count($result['reels']),
            'posts' => max(count($result['posts']), (int) ($profile['posts_count'] ?? $profile['media_count'] ?? 0)),
            'followers' => 0,
        ];
        if ($platform === 'twitter' && isset($profile['tweets_count'])) {
            $result['counts']['posts'] = (int) $profile['tweets_count'];
        }
        foreach ($result['stats'] as $stat) {
            if (strtolower((string) $stat['label']) === 'followers') {
                $result['counts']['followers'] = (int) $stat['value'];
            }
        }

        $result['view'] = $this->resolveView($platform, $tool, $result);
        $result['sections'] = $this->collectSections($raw, $result);

        return $result;
    }

    /**
     * Pick the layout that best fits the tool: feed, people, trends, jobs,
     * events, courses, search, list, media, or the full profile.
     */
    private function resolveView(string $platform, array $tool, array $result): string
    {
        if (($result['focus'] ?? '') === 'avatar') {
            return 'avatar';
        }
        if (($result['intent'] ?? '') === 'download') {
            return 'download';
        }

        $content = (string) ($tool['content'] ?? '');

        if (in_array($content, ['profile', 'company', 'about'], true)) {
            return 'profile';
        }
        if ($content === 'trends') {
            return 'trends';
        }
        if ($content === 'locations') {
            return 'list';
        }
        if (in_array($content, ['jobs', 'detail'], true) && $platform === 'linkedin') {
            return $content === 'jobs' ? 'jobs' : 'list';
        }
        if ($content === 'events') {
            return 'events';
        }
        if ($content === 'courses') {
            return 'courses';
        }
        if (in_array($content, ['search', 'search_recent', 'search_users', 'search_tags', 'home'], true)) {
            // LinkedIn search tools carry the entity: job search → jobs, course search → courses.
            if ($platform === 'linkedin') {
                $entity = (string) ($tool['entity'] ?? '');
                if ($entity === 'job') {
                    return 'jobs';
                }
                if ($entity === 'learning') {
                    return 'courses';
                }
            }

            return 'search';
        }
        if (in_array($content, ['experience', 'education', 'activity', 'articles', 'featured'], true)) {
            return 'list';
        }
        if (in_array($content, ['reposts', 'quotes', 'retweets', 'replies', 'tweets', 'posts', 'post'], true)) {
            return 'feed';
        }
        if (in_array($content, ['photos', 'media', 'videos'], true)) {
            return 'media';
        }
        if (($result['focus'] ?? '') === 'people') {
            return 'people';
        }

        // Fall back to the best-populated collection.
        if (count($result['posts'] ?? [])) {
            return 'feed';
        }
        if (count($result['people'] ?? [])) {
            return 'people';
        }
        if (count($result['items'] ?? [])) {
            return 'list';
        }
        if (count($result['media'] ?? [])) {
            return 'media';
        }

        return 'profile';
    }

    /**
     * Snapshot the named raw lists so list panels (trends, jobs, events,
     * courses, locations, articles, comments, reposts, quotes) can render
     * type-specific cards instead of the generic flat items.
     */
    private function collectSections(array $raw, array $result): array
    {
        $labels = [
            'trends' => 'Trending',
            'jobs' => 'Jobs',
            'events' => 'Events',
            'courses' => 'Courses',
            'locations' => 'Locations',
            'articles' => 'Articles',
            'comments' => 'Comments',
            'reposts' => 'Reposts',
            'quotes' => 'Quotes',
            'experience' => 'Experience',
            'education' => 'Education',
            'activity' => 'Activity',
            'featured' => 'Featured',
        ];

        $out = [];
        foreach ($labels as $key => $label) {
            $rows = is_array($raw[$key] ?? null) ? $raw[$key] : [];
            if ($rows === []) {
                continue;
            }
            $items = [];
            foreach ($rows as $row) {
                if (is_string($row) && trim($row) !== '') {
                    $items[] = ['title' => $row, 'text' => '', 'url' => '', 'meta' => ''];
                    continue;
                }
                if (! is_array($row)) {
                    continue;
                }
                $items[] = $this->sectionItem($row);
            }
            if ($items !== []) {
                $out[] = ['key' => $key, 'label' => $label, 'items' => $items];
            }
        }

        return $out;
    }

    /**
     * Map one raw row of a named section to a display item.
     */
    private function sectionItem(array $row): array
    {
        $title = $this->firstString([
            $row['name'] ?? null,
            $row['title'] ?? null,
            $row['headline'] ?? null,
            $row['job_title'] ?? null,
            $row['company'] ?? null,
            $row['text'] ?? null,
            $row['hashtag'] ?? null,
        ]);

        $text = $this->firstString([
            $row['text'] ?? null,
            $row['description'] ?? null,
            $row['body'] ?? null,
            $row['about'] ?? null,
            $row['subtitle'] ?? null,
            $row['snippet'] ?? null,
        ]);

        $meta = $this->firstString([
            $row['location'] ?? null,
            $row['address'] ?? null,
            $row['company'] ?? null,
            $row['company_name'] ?? null,
            $row['author'] ?? null,
            $row['date'] ?? null,
            $row['start_date'] ?? null,
            $row['tweet_volume'] ?? null,
            $row['salary'] ?? null,
            $row['type'] ?? null,
            $row['provider'] ?? null,
            $row['institution'] ?? null,
        ]);

        $sub = '';
        foreach (['location', 'salary', 'company', 'company_name', 'tweet_volume', 'date', 'start_date', 'type', 'provider', 'institution', 'address'] as $key) {
            $v = $row[$key] ?? null;
            if (is_scalar($v) && $v !== '' && $v !== null) {
                $sub = (string) $v;
                break;
            }
        }

        return [
            'title' => $title !== '' ? $title : mb_strimwidth($text, 0, 80, '…'),
            'text' => $text,
            'url' => $this->firstString([$row['url'] ?? null, $row['permalink'] ?? null, $row['link'] ?? null]),
            'meta' => $sub !== '' ? $sub : $meta,
            'raw' => $row,
        ];
    }

    private function stats(string $platform, array $raw, array $profile, array $company, array $media, bool $isMediaLookup, array $tweet = [], bool $isTweetLookup = false): array
    {
        if ($isMediaLookup) {
            $map = [
                'likes' => $media['like_count'] ?? $media['likes'] ?? null,
                'comments' => $media['comment_count'] ?? $media['comments'] ?? null,
                'views' => $media['view_count'] ?? $media['views'] ?? $media['play_count'] ?? null,
            ];
        } elseif ($isTweetLookup) {
            $map = [
                'likes' => $tweet['likes'] ?? null,
                'retweets' => $tweet['retweets'] ?? null,
                'replies' => $tweet['replies'] ?? null,
                'views' => $tweet['views'] ?? null,
            ];
        } else {
            // Facebook personal profiles report follower count as "likes".
            $followers = $profile['followers'] ?? $profile['follower_count'] ?? $company['followers'] ?? null;
            if ($followers === null && $platform === 'facebook') {
                $followers = $profile['likes'] ?? null;
            }
            $postsStat = $profile['posts_count'] ?? $profile['media_count'] ?? $raw['tweet_count_fetched'] ?? null;
            if ($platform === 'twitter') {
                // X reports total tweets as tweets_count; media_count is photos+videos only.
                $postsStat = $profile['tweets_count'] ?? $postsStat;
            }
            $linkedinFollowers = $profile['follower_count'] ?? $company['follower_count'] ?? null;
            $map = [
                'posts' => $postsStat,
                'followers' => $followers ?? $linkedinFollowers,
                'following' => $profile['following'] ?? $profile['following_count'] ?? null,
                'likes' => ($platform === 'facebook') ? null : ($profile['likes'] ?? null),
                'talking_about' => $profile['talking_about'] ?? $profile['talking_about_this'] ?? null,
                'employees' => $company['employees'] ?? $company['staff_count'] ?? $company['employee_count'] ?? null,
                'connections' => $profile['connection_count'] ?? null,
            ];
        }

        $out = [];
        foreach ($map as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if ($isMediaLookup && $label !== 'likes' && is_numeric($value) && (int) $value === 0) {
                continue;
            }
            $out[] = ['label' => ucfirst($label), 'value' => is_numeric($value) ? (int) $value : $value];
        }

        return $out;
    }

    private function collectMedia(array $raw, array $tool): array
    {
        if (($tool['focus'] ?? '') === 'avatar') {
            return [];
        }

        $out = [];

        if (is_array($raw['media'] ?? null) && $this->isAssoc($raw['media'])) {
            $out = array_merge($out, $this->flattenMediaNode($raw['media']));
        } else {
            $out = array_merge($out, $this->ingestRows($this->asList($raw['media'] ?? [])));
        }

        if (is_array($raw['tweet'] ?? null) && $this->isAssoc($raw['tweet'])) {
            $out = array_merge($out, $this->flattenMediaNode($raw['tweet']));
        }

        $out = array_merge($out, $this->ingestRows(array_merge(
            $this->asList($raw['posts'] ?? []),
            $this->asList($raw['reels'] ?? []),
            $this->asList($raw['photos'] ?? []),
            $this->asList($raw['videos'] ?? []),
            $this->asList($raw['stories'] ?? []),
            $this->asList($raw['tweets'] ?? []),
            $this->asList($raw['listings'] ?? []),
        )));

        $seen = [];
        $unique = [];
        foreach ($out as $item) {
            $key = ($item['url'] ?: $item['thumb']).'|'.$item['permalink'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $item;
        }

        return $this->filterMediaByFocus($unique, $tool);
    }

    /**
     * Facebook often returns photos/videos as a list of CDN URL strings.
     */
    private function ingestRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (is_string($row) && trim($row) !== '') {
                $url = $this->cleanText(trim($row));
                $isPermalink = $this->isSocialPermalink($url);
                $out[] = [
                    'url' => $isPermalink ? '' : $url,
                    'thumb' => $isPermalink ? '' : $url,
                    'caption' => '',
                    'likes' => 0,
                    'comments' => 0,
                    'views' => 0,
                    'is_video' => str_contains(strtolower($url), '.mp4') || str_contains(strtolower($url), '/video'),
                    'is_reel' => false,
                    'is_carousel' => false,
                    'permalink' => $isPermalink ? $url : '',
                ];
                continue;
            }
            if (is_array($row)) {
                $out = array_merge($out, $this->flattenMediaNode($row));
            }
        }

        return $out;
    }

    /**
     * Social media payloads nest the real files in media.media[].
     */
    private function flattenMediaNode(array $row): array
    {
        $children = $row['media'] ?? null;
        if (is_array($children) && $children !== [] && ! $this->isAssoc($children)) {
            $out = [];
            foreach ($children as $child) {
                if (is_string($child) && trim($child) !== '') {
                    $out = array_merge($out, $this->ingestRows([$child]));
                    continue;
                }
                if (! is_array($child)) {
                    continue;
                }
                $item = $this->mediaItem($child);
                if ($item['caption'] === '') {
                    $item['caption'] = $this->firstString([$row['caption'] ?? null, $row['text'] ?? null]);
                }
                if ($item['permalink'] === '') {
                    $permalink = (string) ($row['url'] ?? $row['permalink'] ?? '');
                    $item['permalink'] = $this->isSocialPermalink($permalink) ? $permalink : '';
                }
                if ($item['likes'] === 0) {
                    $item['likes'] = (int) ($row['like_count'] ?? $row['likes'] ?? 0);
                }
                if ($item['comments'] === 0) {
                    $item['comments'] = (int) ($row['comment_count'] ?? $row['comments'] ?? 0);
                }
                if ($item['url'] !== '' || $item['thumb'] !== '') {
                    $out[] = $item;
                }
            }

            return $out;
        }

        $item = $this->mediaItem($row);
        if ($item['url'] === '' && $item['thumb'] === '' && empty($item['is_video'])) {
            return [];
        }

        return [$item];
    }

    private function filterMediaByFocus(array $items, array $tool): array
    {
        $focus = $tool['focus'] ?? '';

        if ($focus === 'photo') {
            $photos = array_values(array_filter($items, fn (array $item) => empty($item['is_video'])));

            return $photos !== [] ? $photos : $items;
        }

        if (in_array($focus, ['reel', 'video'], true)) {
            $videos = array_values(array_filter($items, fn (array $item) => ! empty($item['is_video'])));

            return $videos !== [] ? $videos : $items;
        }

        return $items;
    }

    private function mediaItem(array $row): array
    {
        $raw = is_array($row['raw'] ?? null) ? $row['raw'] : [];
        $permalinkCandidate = $this->firstString([$row['permalink'] ?? null, $row['url'] ?? null]);
        $permalink = $this->isSocialPermalink($permalinkCandidate) ? $permalinkCandidate : '';

        $playable = $this->firstString([
            $row['video_url'] ?? null,
            $row['playable_url'] ?? null,
            $raw['playable_url'] ?? null,
            $raw['browser_native_hd_url'] ?? null,
            $raw['browser_native_sd_url'] ?? null,
            $raw['playable_url_quality_hd'] ?? null,
        ]);

        if ($playable === '') {
            $playable = $this->findNestedPlayable($raw['attachments'] ?? $raw);
        }

        $isReel = (bool) ($row['is_reel'] ?? false)
            || strtolower((string) ($row['product_type'] ?? '')) === 'clips'
            || (bool) preg_match('~/(?:reel|reels)/~i', $permalink);

        // Threads media_type: 1=image, 2=video, 8=carousel
        $mediaType = (int) ($row['media_type'] ?? 0);
        $isVideo = (bool) ($row['is_video'] ?? $isReel)
            || $playable !== ''
            || $mediaType === 2
            || (int) ($row['length_seconds'] ?? $row['length'] ?? $row['video_duration'] ?? 0) > 0
            || (bool) preg_match('~/(?:reel|reels|videos|watch)/~i', $permalink)
            || $this->looksLikeVideoFile($playable);

        $file = $playable;
        if ($file === '') {
            foreach ([
                $row['display_url'] ?? null,
                $row['image_url'] ?? null,
                $row['image'] ?? null,
                $row['src'] ?? null,
                $row['url'] ?? null,
            ] as $candidate) {
                $value = $this->firstString([$candidate]);
                if ($value !== '' && ! $this->isSocialPermalink($value) && ! $this->looksLikeVideoFile($value)) {
                    $file = $value;
                    break;
                }
            }
        }

        $thumb = '';
        foreach ([
            $row['thumbnail'] ?? null,
            $row['thumb'] ?? null,
            $row['cover_url'] ?? null,
            $row['image'] ?? null,
            $row['display_url'] ?? null,
            $row['image_url'] ?? null,
            $row['full_picture'] ?? null,
            $row['picture'] ?? null,
            (! $isVideo && $file !== '') ? $file : null,
        ] as $candidate) {
            $value = $this->firstMediaString([$candidate]);
            if ($value !== '' && $this->looksLikeImageFile($value)) {
                $thumb = $value;
                break;
            }
        }

        if ($thumb === '') {
            $thumb = $this->findNestedImage($raw['attachments'] ?? $raw);
        }

        return [
            'url' => $isVideo ? ($playable ?: $file) : $file,
            'thumb' => $thumb,
            'caption' => $this->firstString([$row['caption'] ?? null, $row['text'] ?? null, $row['title'] ?? null]),
            'likes' => (int) ($row['likes'] ?? $row['like_count'] ?? $row['reactions'] ?? 0),
            'comments' => (int) ($row['comments'] ?? $row['comment_count'] ?? 0),
            'views' => (int) ($row['views'] ?? $row['view_count'] ?? $row['play_count'] ?? 0),
            'is_video' => $isVideo,
            'is_reel' => $isReel || ($isVideo && (bool) preg_match('~/(?:reel|reels)/~i', $permalink)),
            'is_carousel' => (bool) ($row['is_carousel'] ?? false) || (int) ($row['media_type'] ?? 0) === 8 || ((int) ($row['carousel_count'] ?? 0) > 1),
            'permalink' => $permalink,
        ];
    }

    /**
     * Facebook posts hide the MP4 under attachments[].styles.attachment.media.videoDeliveryLegacyFields.
     */
    private function findNestedPlayable(mixed $node, int $depth = 0): string
    {
        if ($depth > 22 || ! is_array($node)) {
            return '';
        }

        foreach ([
            'browser_native_hd_url',
            'browser_native_sd_url',
            'playable_url',
            'playable_url_quality_hd',
            'video_url',
            'hd_src',
            'sd_src',
        ] as $key) {
            $value = $this->firstString([$node[$key] ?? null]);
            if ($value !== '' && $this->looksLikeVideoFile($value)) {
                return $value;
            }
        }

        // Facebook nests the progressive MP4 inside videoDeliveryLegacyFields (dash_manifest / browser_native_*).
        if (is_array($node['videoDeliveryLegacyFields'] ?? null)) {
            foreach (['browser_native_hd_url', 'browser_native_sd_url', 'playable_url'] as $key) {
                $value = $this->firstString([$node['videoDeliveryLegacyFields'][$key] ?? null]);
                if ($value !== '' && $this->looksLikeVideoFile($value)) {
                    return $value;
                }
            }
        }

        foreach ($node as $value) {
            $found = $this->findNestedPlayable($value, $depth + 1);
            if ($found !== '') {
                return $found;
            }
        }

        return '';
    }

    /**
     * Facebook video posts often leave top-level image empty and put the poster in attachments.
     */
    private function findNestedImage(mixed $node, int $depth = 0): string
    {
        if ($depth > 14 || ! is_array($node)) {
            return '';
        }

        foreach (['uri', 'url', 'src', 'thumbnail', 'thumb'] as $key) {
            $value = $this->firstString([$node[$key] ?? null]);
            if ($value !== '' && $this->looksLikeImageFile($value)) {
                return $value;
            }
        }

        // Facebook shared videos carry the poster as thumbnailImage.uri
        if (is_array($node['thumbnailImage'] ?? null)) {
            $value = $this->firstString([$node['thumbnailImage']['uri'] ?? null]);
            if ($value !== '' && $this->looksLikeImageFile($value)) {
                return $value;
            }
        }

        foreach ($node as $value) {
            $found = $this->findNestedImage($value, $depth + 1);
            if ($found !== '') {
                return $found;
            }
        }

        return '';
    }

    private function looksLikeVideoFile(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $lower = strtolower($url);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return str_contains($lower, '.mp4')
            || str_contains($lower, '.m4v')
            || str_contains($lower, '.mov')
            || str_contains($host, 'video-')
            || str_contains($lower, '/v/t2/');
    }

    private function looksLikeImageFile(string $url): bool
    {
        if ($url === '' || $this->isSocialPermalink($url) || $this->looksLikeVideoFile($url)) {
            return false;
        }

        $lower = strtolower($url);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return str_contains($lower, '.jpg')
            || str_contains($lower, '.jpeg')
            || str_contains($lower, '.png')
            || str_contains($lower, '.webp')
            || str_contains($lower, '.gif')
            || str_contains($host, 'scontent')
            || (str_contains($host, 'fbcdn.net') && ! str_contains($host, 'video-'));
    }

    private function isSocialPermalink(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        if (preg_match('~\.(mp4|m4v|mov|jpg|jpeg|png|webp|gif)(?:$|\?)~i', $url)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        foreach (['fbcdn.net', 'twimg.com', 'licdn.com', 'scontent'] as $cdn) {
            if (str_contains($host, $cdn)) {
                return false;
            }
        }

        foreach (['facebook.com', 'fb.com', 'x.com', 'twitter.com', 'threads.net', 'threads.com', 'linkedin.com'] as $site) {
            if ($host === $site || str_ends_with($host, '.'.$site)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract the original author of a shared Facebook post from attached_story.
     */
    private function sharedActor(array $row): ?array
    {
        $attached = $row['raw']['attached_story'] ?? null;
        if (! is_array($attached)) {
            return null;
        }

        // Walk comet_sections.context_layout.story.actors (shared post author)
        $sections = $attached['comet_sections'] ?? null;
        $actors = null;
        if (is_array($sections)) {
            $actors = $sections['context_layout']['story']['actors']
                ?? $sections['header']['story']['actors']
                ?? $sections['actor_photo']['story']['actors']
                ?? null;
        }
        if (! is_array($actors) || $actors === []) {
            $actors = $attached['actors'] ?? null;
        }
        if (! is_array($actors) || $actors === []) {
            return null;
        }

        $actor = $actors[0];
        if (! is_array($actor)) {
            return null;
        }

        // The avatar/url live deeper in context_layout (nested story actors).
        $context = is_array($sections) ? ($sections['context_layout'] ?? null) : null;
        $photo = is_array($context) ? $this->firstNestedValue($context, ['profile_picture', 'profile_url']) : null;
        $photoUrl = is_array($photo) ? ($photo['profile_url'] ?? '') : '';
        $photoAvatar = is_array($photo) ? $this->firstMediaString([$photo['profile_picture']['uri'] ?? null, $photo['profile_picture'] ?? null]) : '';

        return [
            'name' => $this->firstString([$actor['name'] ?? null]),
            'url' => $this->firstString([$photoUrl, $actor['profile_url'] ?? null, $actor['url'] ?? null]),
            'avatar' => $this->firstMediaString([$photoAvatar, $actor['profile_picture']['uri'] ?? null, $actor['profile_pic'] ?? null, $actor['image'] ?? null]),
        ];
    }

    /**
     * Recursively find the first object containing every wanted key.
     */
    private function firstNestedValue(mixed $node, array $wanted, int $depth = 0): ?array
    {
        if ($depth > 12 || ! is_array($node)) {
            return null;
        }
        $hasAll = true;
        foreach ($wanted as $key) {
            if (! array_key_exists($key, $node)) {
                $hasAll = false;
                break;
            }
        }
        if ($hasAll) {
            return $node;
        }
        foreach ($node as $value) {
            $found = $this->firstNestedValue($value, $wanted, $depth + 1);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function collectPosts(array $raw): array
    {
        $out = [];
        foreach (array_merge(
            $this->asList($raw['posts'] ?? []),
            $this->asList($raw['posts_preview'] ?? []),
            $this->asList($raw['tweets'] ?? []),
            $this->asList($raw['replies'] ?? []),
            $this->asList($raw['tweet'] ?? []),
            $this->asList($raw['post'] ?? []),
            $this->asList($raw['retweets'] ?? []),
            $this->asList($raw['reposts'] ?? []),
            $this->asList($raw['quotes'] ?? []),
        ) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $text = $this->firstString([$row['text'] ?? null, $row['caption'] ?? null, $row['title'] ?? null]);
            if ($text === '' && empty($row['url'])) {
                continue;
            }
            $timeRaw = $this->firstString([$row['time'] ?? null, $row['date'] ?? null, $row['created_time'] ?? null, $row['timestamp'] ?? null, $row['creation_time'] ?? null, $row['created_at'] ?? null, $row['date_published'] ?? null]);
            $time = $timeRaw;
            if ($timeRaw !== '' && ctype_digit($timeRaw) && (int) $timeRaw > 1000000000) {
                $time = date('M j, Y', (int) $timeRaw);
            } elseif ($timeRaw !== '' && str_contains($timeRaw, 'T') && preg_match('/^\d{4}-\d{2}-\d{2}/', $timeRaw, $dm)) {
                // ISO 8601: 2026-09-01T19:34:09.488Z
                $time = date('M j, Y', strtotime($timeRaw));
            } elseif ($timeRaw !== '' && str_contains($timeRaw, '+0000')) {
                // Twitter style: "Tue Sep 01 11:00:33 +0000 2026"
                $parsed = date_parse($timeRaw);
                if ($parsed && ! empty($parsed['year'])) {
                    $time = date('M j, Y', mktime(0, 0, 0, $parsed['month'] ?: 1, $parsed['day'] ?: 1, $parsed['year']));
                }
            }
            $post = [
                'text' => $text,
                'url' => $this->firstString([$row['url'] ?? null, $row['permalink'] ?? null, $row['raw']['permalink_url'] ?? null]),
                'likes' => (int) ($row['likes'] ?? $row['like_count'] ?? $row['reactions'] ?? 0),
                'comments' => (int) ($row['comments'] ?? $row['comment_count'] ?? $row['reply_count'] ?? 0),
                'retweets' => (int) ($row['retweets'] ?? $row['repost_count'] ?? 0),
                'quotes' => (int) ($row['quotes'] ?? $row['quote_count'] ?? 0),
                'views' => (int) ($row['views'] ?? 0),
                'time' => $time,
                'media' => [],
            ];

            // A shared post (attached_story) carries the original author in actors.
            $sharedActor = $this->sharedActor($row);
            if ($sharedActor !== null) {
                $post['author'] = $sharedActor['name'];
                $post['author_url'] = $sharedActor['url'];
                $post['author_avatar'] = $sharedActor['avatar'];
                $post['is_share'] = true;
            } else {
                $userObj = is_array($row['user'] ?? null) ? $row['user'] : [];
                $post['author'] = $this->firstString([
                    $userObj['full_name'] ?? null,
                    $userObj['username'] ?? null,
                    $row['author'] ?? null,
                    $row['author_name'] ?? null,
                    $row['full_name'] ?? null,
                ]);
                $post['author_url'] = $this->firstString([$row['author_url'] ?? null]);
                $post['author_avatar'] = $this->firstMediaString([
                    $userObj['profile_pic_url'] ?? null,
                    $userObj['profile_picture'] ?? null,
                    $userObj['profile_pic'] ?? null,
                ]);
                $post['is_share'] = false;
            }
            $postMedia = $this->flattenMediaNode($row);
            if ($postMedia === []) {
                // Facebook nests real media (video / image) deep in raw.comet_sections.content
                $comet = $row['raw']['comet_sections'] ?? [];
                if (is_array($comet) && $comet !== []) {
                    $postMedia = $this->flattenMediaNode([
                        'raw' => $comet,
                        'caption' => $text,
                        'url' => $row['url'] ?? null,
                    ]);
                }
            }
            if ($postMedia !== []) {
                // Drop media entries that have no playable file or thumbnail at all.
                $post['media'] = array_values(array_filter($postMedia, fn (array $mm) => ($mm['url'] ?? '') !== '' || ($mm['thumb'] ?? '') !== ''));
            } else {
                $postThumb = $this->firstMediaString([
                    $row['full_picture'] ?? null,
                    $row['picture'] ?? null,
                    $row['image'] ?? null,
                    $row['image_url'] ?? null,
                    $row['thumb'] ?? null,
                    $row['thumbnail'] ?? null,
                ]);
                if ($postThumb !== '') {
                    $post['media'][] = [
                        'url' => '',
                        'thumb' => $postThumb,
                        'caption' => '',
                        'likes' => 0,
                        'comments' => 0,
                        'views' => 0,
                        'is_video' => false,
                        'is_reel' => false,
                        'is_carousel' => false,
                        'permalink' => $this->isSocialPermalink((string) $post['url']) ? $post['url'] : '',
                    ];
                }
            }
            $out[] = $post;
        }

        return $out;
    }

    private function collectPeople(array $raw): array
    {
        $rows = array_merge(
            $this->asList($raw['users'] ?? []),
            $this->asList($raw['people'] ?? []),
            $this->asList($raw['followers'] ?? []),
            $this->asList($raw['following'] ?? []),
            $this->asList($raw['employees'] ?? []),
            $this->asList($raw['employees_preview'] ?? []),
            $this->asList($raw['similar'] ?? []),
            $this->asList($raw['similar_preview'] ?? []),
            $this->asList($raw['affiliated_preview'] ?? []),
            $this->asList($raw['likes'] ?? []),
        );

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $username = $this->firstString([$row['username'] ?? null, $row['handle'] ?? null, $row['name'] ?? null]);
            if ($username === '') {
                continue;
            }
            $out[] = [
                'username' => $username,
                'full_name' => $this->firstString([$row['full_name'] ?? null, $row['name'] ?? null, $username]),
                'avatar' => $this->firstString([$row['profile_pic'] ?? null, $row['profile_pic_url'] ?? null, $row['avatar'] ?? null, $row['image'] ?? null]),
                'url' => $this->firstString([$row['url'] ?? null]),
                'verified' => (bool) ($row['is_verified'] ?? $row['verified'] ?? false),
            ];
        }

        return $out;
    }

    private function collectItems(array $raw): array
    {
        $rows = array_merge(
            $this->asList($raw['comments'] ?? []),
            $this->asList($raw['jobs'] ?? []),
            $this->asList($raw['listings'] ?? []),
            $this->asList($raw['events'] ?? []),
            $this->asList($raw['hashtags'] ?? []),
            $this->asList($raw['tags'] ?? []),
            $this->asList($raw['places'] ?? []),
            $this->asList($raw['trends'] ?? []),
            $this->asList($raw['locations'] ?? []),
            $this->asList($raw['locations_preview'] ?? []),
            $this->asList($raw['experience'] ?? []),
            $this->asList($raw['education'] ?? []),
            $this->asList($raw['activity'] ?? []),
            $this->asList($raw['articles'] ?? []),
            $this->asList($raw['featured'] ?? []),
            $this->asList($raw['courses'] ?? []),
        );

        $out = [];
        foreach ($rows as $row) {
            if (is_string($row) && $row !== '') {
                $out[] = ['title' => $row, 'text' => '', 'url' => '', 'meta' => ''];
                continue;
            }
            if (! is_array($row)) {
                continue;
            }
            $title = $this->firstString([
                $row['name'] ?? null,
                $row['title'] ?? null,
                $row['address'] ?? null,
                $row['question'] ?? null,
                $row['username'] ?? null,
                $row['hashtag'] ?? null,
                $row['tag'] ?? null,
                $row['text'] ?? null,
            ]);
            $text = $this->firstString([
                $row['text'] ?? null,
                $row['body'] ?? null,
                $row['description'] ?? null,
                $row['subtitle'] ?? null,
                $row['snippet'] ?? null,
                $row['comment'] ?? null,
            ]);
            if ($title === '' && $text === '') {
                continue;
            }
            $out[] = [
                'title' => $title !== '' ? $title : mb_strimwidth($text, 0, 80, '…'),
                'text' => $text,
                'url' => $this->firstString([$row['url'] ?? null, $row['permalink'] ?? null]),
                'meta' => $this->firstString([$row['location'] ?? null, $row['company'] ?? null, $row['author'] ?? null, $row['date'] ?? null]),
            ];
        }

        return $out;
    }

    private function collectHighlights(array $raw): array
    {
        $out = [];
        foreach ($this->asList($raw['highlights'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $items = [];
            foreach ($this->asList($row['items'] ?? []) as $item) {
                if (is_array($item)) {
                    $media = $this->mediaItem($item);
                    if ($media['url'] !== '' || $media['thumb'] !== '') {
                        $items[] = $media;
                    }
                }
            }
            $out[] = [
                'title' => $this->firstString([$row['title'] ?? null, $row['name'] ?? null, 'Highlight']),
                'cover' => $this->firstString([$row['cover_url'] ?? null, $row['cover'] ?? null, $row['thumbnail'] ?? null]),
                'items' => $items,
            ];
        }

        return $out;
    }

    private function collectBioLinks(array $profile): array
    {
        $out = [];
        foreach ($this->asList($profile['bio_links'] ?? []) as $row) {
            if (is_string($row) && $this->looksLikeHttp($row)) {
                $out[] = ['title' => $row, 'url' => $row];
                continue;
            }
            if (! is_array($row)) {
                continue;
            }
            $url = $this->firstString([$row['url'] ?? null, $row['link'] ?? null]);
            if ($url === '') {
                continue;
            }
            $out[] = [
                'title' => $this->firstString([$row['title'] ?? null, $url]),
                'url' => $url,
            ];
        }

        return $out;
    }

    private function looksLikeHttp(string $value): bool
    {
        return (bool) preg_match('~^https?://~i', $value);
    }

    private function collectDetail(string $platform, array $raw, array $profile, array $company, array $job, array $media): array
    {
        $source = $company ?: $job ?: ($this->isAssoc($media) ? $media : []) ?: $profile;
        $about = is_array($raw['about'] ?? null) ? $raw['about'] : [];
        if ($source === $profile && $about !== []) {
            $source = $about;
        }
        $out = [];

        // Facebook about.sections.overview holds the human-readable highlights
        // (job, education, location, relationship) as a list of strings.
        $about = is_array($raw['about'] ?? null) ? $raw['about'] : [];
        $overview = $about['sections']['overview'] ?? null;
        if ($source === $about && is_array($overview)) {
            foreach ($overview as $line) {
                if (is_string($line) && trim($line) !== '') {
                    $out[] = ['label' => 'Overview', 'value' => $this->cleanText(trim($line))];
                }
            }
        }

        // X exposes a lot of technical flags (ids, counts, verified shapes) that
        // users don't need — only keep the human-relevant fields.
        if ($platform === 'twitter') {
            $keep = ['location', 'website', 'url', 'created_at'];
            foreach ($keep as $key) {
                $value = $profile[$key] ?? null;
                if ($value === '' || $value === null || is_array($value)) {
                    continue;
                }
                $label = match ($key) {
                    'url' => 'Profile',
                    'created_at' => 'Joined',
                    default => ucwords(str_replace('_', ' ', $key)),
                };
                $display = (string) $value;
                if ($key === 'created_at' && str_contains($display, '+0000')) {
                    $parsed = date_parse($display);
                    if ($parsed && ! empty($parsed['year'])) {
                        $display = date('M j, Y', mktime(0, 0, 0, $parsed['month'] ?: 1, $parsed['day'] ?: 1, $parsed['year']));
                    }
                }
                $out[] = ['label' => $label, 'value' => $this->cleanText($display)];
            }

            return $out;
        }

        // LinkedIn: keep only the human-relevant company/profile fields.
        if ($platform === 'linkedin') {
            $keep = $company !== [] ? [
                'industry', 'website', 'headquarters', 'company_size', 'specialties',
            ] : [
                'headline', 'about', 'location', 'website', 'current_company', 'disambiguating_description',
            ];
            foreach ($keep as $key) {
                $value = $company[$key] ?? $profile[$key] ?? null;
                if ($value === '' || $value === null || is_array($value)) {
                    continue;
                }
                $out[] = ['label' => ucwords(str_replace('_', ' ', $key)), 'value' => $this->cleanText((string) $value)];
            }

            return $out;
        }

        $skip = [
            'username', 'full_name', 'biography', 'bio', 'profile_pic', 'profile_pic_url',
            'avatar', 'posts', 'tweets', 'id', 'fbid', 'pk', 'user_id', 'entity_id',
            'followers', 'following', 'posts_count', 'media_count',
            'follower_count', 'following_count', 'type', 'raw', 'edges',
            'cover', 'cover_url', 'cover_photo', 'banner', 'bio_links',
        ];
        foreach ($source as $key => $value) {
            if (in_array($key, $skip, true) || $value === '' || $value === null) {
                continue;
            }
            if (is_bool($value)) {
                $out[] = ['label' => ucwords(str_replace('_', ' ', (string) $key)), 'value' => $value ? 'Yes' : 'No'];
                continue;
            }
            if (is_array($value)) {
                continue;
            }
            $out[] = ['label' => ucwords(str_replace('_', ' ', (string) $key)), 'value' => $this->cleanText((string) $value)];
            if (count($out) >= 24) {
                break;
            }
        }

        return $out;
    }

    private function asList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return $this->isAssoc($value) ? [$value] : array_values($value);
    }

    private function isAssoc(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Keep the first item for each unique key produced by $keyFn.
     */
    private function dedupeBy(array $items, callable $keyFn): array
    {
        $seen = [];
        $out = [];
        foreach ($items as $item) {
            $key = (string) $keyFn($item);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $item;
        }

        return $out;
    }

    /**
     * Like firstString, but digs into common nested shapes: ['source' => url],
     * ['url' => url], [0 => url], and object-style arrays.
     */
    private function firstMediaString(array $candidates): string
    {
        foreach ($candidates as $value) {
            if (is_string($value) && trim($value) !== '') {
                return $this->cleanText(trim($value));
            }
            if (is_array($value)) {
                foreach ([
                    'source' => $value['source'] ?? null,
                    'url' => $value['url'] ?? null,
                    'uri' => $value['uri'] ?? null,
                    'link' => $value['link'] ?? null,
                    'src' => $value['src'] ?? null,
                ] as $key => $nested) {
                    if (is_string($nested) && trim($nested) !== '') {
                        return $this->cleanText(trim($nested));
                    }
                    if (is_array($nested)) {
                        foreach ($nested as $deeper) {
                            if (is_string($deeper) && trim($deeper) !== '') {
                                return $this->cleanText(trim($deeper));
                            }
                        }
                    }
                    unset($key);
                }
                foreach ($value as $nested) {
                    if (is_string($nested) && trim($nested) !== '') {
                        return $this->cleanText(trim($nested));
                    }
                    if (is_array($nested)) {
                        foreach ($nested as $deeper) {
                            if (is_string($deeper) && trim($deeper) !== '') {
                                return $this->cleanText(trim($deeper));
                            }
                        }
                    }
                }
            }
        }

        return '';
    }

    private function firstString(array $candidates): string
    {
        foreach ($candidates as $value) {
            if (is_string($value) && trim($value) !== '') {
                return $this->cleanText(trim($value));
            }
            if (is_numeric($value)) {
                return (string) $value;
            }
        }

        return '';
    }

    /**
     * Facebook OG fields often HTML-encode query strings (&amp;), which 403s the CDN.
     */
    private function cleanText(string $value): string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return str_replace('&amp;', '&', $decoded);
    }
}
