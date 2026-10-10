<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Support\SitemapBuilder;
use Illuminate\Database\Seeder;

class PracticalBlogGuidesSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(['slug' => 'social-media-tips'], ['name' => 'Social Media Tips']);
        $guides = [
            [
                'title' => 'Choosing the Right Facebook Tool for a Public Link',
                'slug' => 'choosing-the-right-facebook-tool-for-a-public-link',
                'image_path' => 'images/blog_fb_guide.png',
                'content' => '<p>A Facebook page address, a personal profile, and an individual post are different starting points. Choosing a tool that matches your link helps you request the content you actually want to review.</p><h2>Start with the type of content</h2><p>Use Page Viewer for a public brand or community page. Page Posts, Page Photos, Page Reels, and Page Videos focus on particular sections of that page. For a person, start with Profile Viewer or its related profile tools. Group Viewer is for available public group information, while Post Viewer accepts a specific post permalink.</p><h2>Copy the right address</h2><p>Open the content in your browser and copy its address, or use the source platform’s sharing options to copy a link. If you want one post, copy the post link rather than the page address. Follow the input guidance on the chosen tool: some accept a name, while others need a URL or group ID.</p><h2>Complete your lookup</h2><p>Paste the input, enter the security code, and submit. Review the information returned before selecting another section. An empty result does not prove that a page has no posts; it may mean the content is not available to a logged-out lookup.</p><h2>Understand the limits</h2><p>A public-looking page can still be restricted by age, location, login requirements, or temporary request limits. Private groups and friends-only posts are outside the scope of these tools. If a lookup fails, check the link and try the matching tool rather than repeatedly submitting unrelated inputs.</p><h2>Save responsibly</h2><p>If a returned item includes a downloadable file, its format and resolution depend on the source. Save only your own content or media you have permission to keep. Keep the original post link for attribution and context.</p>',
            ],
            [
                'title' => 'A Practical Checklist for Saving Public Social Media References',
                'slug' => 'a-practical-checklist-for-saving-public-social-media-references',
                'image_path' => 'images/hero-alpine-lake.jpg',
                'content' => '<p>A useful reference collection needs more than a folder of images. A source link, a short note, and a clear understanding of permission make it easier to revisit a public post without losing its context.</p><h2>Keep the original source</h2><p>Before saving a file, copy the original public post address. Note the creator or organization and the date you found it. Posts can change or disappear, so recording the source helps you explain what the reference showed at the time you collected it.</p><h2>Choose the matching tool</h2><p>Use the tool for the platform and content type you are working with. Facebook page media tools and X Media Viewer are different workflows. Read the input guidance and check whether the tool expects a username, a page address, or an individual post link.</p><h2>Check what is actually available</h2><p>A lookup may return information, thumbnails, or downloadable media. A thumbnail is not a guarantee of a full video file. If the source does not expose a downloadable file, do not assume a different tool can bypass a restriction.</p><h2>Separate inspiration from permission</h2><p>Public visibility does not automatically grant permission to republish a photo or video. Keep references for the purpose allowed by the owner and applicable rules. Ask the creator for permission when you plan to reuse their work, and record any conditions they provide.</p><h2>Organize with a little context</h2><p>Use descriptive filenames and a brief note explaining why each item is useful. Group references by project or topic. Preserve source links alongside the files and remove material you no longer need. Avoid storing unnecessary personal information about people shown in a post.</p><h2>Expect source-dependent quality</h2><p>Available resolution and format depend on the original upload and the platform response. Review a file before relying on it for a presentation or production task. A viewer tool helps access available public content; it does not recreate missing originals.</p>',
            ],
        ];
        foreach ($guides as $guide) {
            Post::firstOrCreate(['slug' => $guide['slug']], $guide + ['category_id' => $category->id, 'status' => 'published']);
        }
        SitemapBuilder::flush();
    }
}
