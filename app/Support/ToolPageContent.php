<?php

namespace App\Support;

class ToolPageContent
{
    public static function forTool(string $platform, array $meta, array $tool): array
    {
        $name = $meta['name'].' '.$tool['name'];
        $content = $tool['content'];
        $entity = $tool['entity'] ?? 'profile';

        [$input, $example] = match (true) {
            $platform === 'twitter' && $entity === 'trends' => ['a location or WOEID', '1 (worldwide)'],
            $platform === 'twitter' && $entity === 'tweet' => ['a public tweet URL or status ID', 'https://x.com/username/status/1234567890'],
            $platform === 'twitter' => ['an X username or public profile URL', 'nasa'],
            $platform === 'facebook' && $entity === 'page' => ['a Facebook page name or public page URL', 'https://www.facebook.com/NASA'],
            $platform === 'facebook' && $entity === 'group' => ['a public Facebook group URL or group ID', 'https://www.facebook.com/groups/GROUP_ID'],
            $platform === 'facebook' && $entity === 'post' => ['the permalink of a public Facebook post', 'https://www.facebook.com/PAGE/posts/POST_ID'],
            $platform === 'facebook' => ['a Facebook profile name or public profile URL', 'https://www.facebook.com/zuck'],
            $platform === 'linkedin' && $entity === 'company' => ['a LinkedIn company name or company URL', 'https://www.linkedin.com/company/google'],
            $platform === 'linkedin' && $content === 'search' => ['job keywords, a location and an optional remote filter separated by |', 'python | United States | remote'],
            $platform === 'linkedin' && $entity === 'job' => ['a LinkedIn job URL or job ID', 'https://www.linkedin.com/jobs/view/JOB_ID'],
            $platform === 'linkedin' && $entity === 'learning' => ['a course topic or skill', 'project management'],
            $platform === 'linkedin' => ['a public LinkedIn profile name or URL', 'https://www.linkedin.com/in/satyanadella'],
            $platform === 'threads' && in_array($content, ['media', 'likes', 'quotes'], true) => ['the URL of a public Threads post', 'https://www.threads.com/@username/post/POST_ID'],
            $platform === 'threads' && in_array($content, ['tag', 'search_tags'], true) => ['a topic or tag keyword', 'photography'],
            $platform === 'threads' && $content === 'search_users' => ['a name or Threads username', 'zuck'],
            $platform === 'threads' && in_array($content, ['search', 'search_recent'], true) => ['a search phrase', 'space exploration'],
            $platform === 'threads' && $content === 'home' => ['a label for your public feed lookup', 'public feed'],
            $platform === 'threads' && in_array($content, ['replies', 'reposts'], true) => ['a Threads username or public post URL', 'zuck'],
            default => ['a Threads username or public profile URL', 'https://www.threads.com/@zuck'],
        };

        $result = match ($content) {
            'profile' => $platform === 'threads' ? 'available public profile details and posts' : 'available public profile or page details',
            'company' => 'available company information and about details',
            'posts' => 'available public posts from the selected page, profile or company',
            'photos' => 'available public photos and photo entries',
            'reels' => 'available public reel entries',
            'videos' => 'available public video entries',
            'about' => 'available page category, contact and about information',
            'events' => 'available public event details',
            'followers' => 'available public follower entries',
            'post', 'tweet' => 'the available details of the linked public post',
            'comments' => 'available public comments on the linked post',
            'tweets' => 'available recent tweets, rather than the separate replies feed',
            'replies' => 'available public replies',
            'media' => $platform === 'twitter' ? 'available photos and videos from the account media tab' : 'available details and media from the linked Threads post',
            'retweets' => 'available reposted tweets from the account',
            'highlights' => 'available highlighted posts from the account',
            'trends' => 'available trending topics for the requested location',
            'locations' => $platform === 'twitter' ? 'available trend locations and their identifiers' : 'available company office locations',
            'reposts' => 'available reposts from the selected public account or post',
            'likes' => 'available public entries for people who liked the post',
            'quotes' => 'available quote posts associated with the linked thread',
            'tag' => 'available public posts associated with the topic',
            'search' => $platform === 'linkedin' ? 'available job listings matching your query' : 'available top Threads results matching your phrase',
            'search_recent' => 'available recent Threads results matching your phrase',
            'search_users' => 'available public accounts matching the name or username',
            'search_tags' => 'available topic tags matching your keyword',
            'home' => 'an available snapshot of the public Threads feed',
            'jobs' => 'available job listings associated with the company',
            'employees' => 'available public entries associated with the company',
            'similar' => 'available related company entries',
            'detail' => 'available job information for the linked vacancy',
            'experience' => 'available work experience listed on the public profile',
            'education' => 'available education entries listed on the public profile',
            'activity' => 'available public activity associated with the profile',
            'articles' => 'available articles associated with the public profile',
            default => 'available learning course results matching your topic',
        };

        $limitation = match (true) {
            in_array($content, ['followers', 'employees', 'likes'], true) => 'Returned people are only the entries exposed by the source; this is not a complete list or an export of private account data.',
            in_array($content, ['trends', 'locations'], true) && $platform === 'twitter' => 'Location support and trending topics depend on the source. A location identifier is different from an account username.',
            in_array($content, ['search', 'search_recent', 'search_users', 'search_tags'], true) => 'Search ordering and coverage depend on the source. Try a more specific phrase if results are unrelated or empty.',
            in_array($content, ['jobs', 'detail'], true) => 'Vacancies can close or change. Confirm the current listing and application details on LinkedIn.',
            in_array($content, ['photos', 'reels', 'videos', 'media'], true) => 'A preview does not guarantee a downloadable file. Only save media you own or have permission to download.',
            $content === 'home' => 'This is a public snapshot, not your personal signed-in feed. The label does not select a private account.',
            default => 'Results can be partial or unavailable when the source restricts access, removes content or limits requests. Private content is not unlocked.',
        };

        return [
            'input' => 'Enter '.$input.'.',
            'example' => $example,
            'example_note' => str_contains($example, '_ID') || str_contains($example, 'username') || str_contains($example, '/PAGE/')
                ? 'Replace the uppercase placeholders or username with a real public link or identifier.'
                : 'This is an input example; available results may change.',
            'intro' => 'Use '.$name.' to review '.$result.'. '.$limitation,
            'result' => 'Review '.$result.'. '.$limitation,
            'questions' => [
                ['What input does '.$name.' accept?', 'Enter '.$input.'. Example: '.$example.'. Use a real public link or identifier when the example contains a placeholder.'],
                ['What does '.$name.' show?', 'This tool requests '.$result.' from '.$meta['name'].'. '.$limitation],
                ['Does '.$name.' require my social account?', 'No social account connection or password is requested. The lookup uses available public content; it cannot open private or restricted information.'],
                ['Why can '.$name.' return no results?', 'Check that your input matches this tool: '.$input.'. Content may have been removed, restricted or temporarily unavailable. Lists may contain only part of the available data.'],
            ],
        ];
    }
}
