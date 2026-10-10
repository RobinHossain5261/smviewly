<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Support\SitemapBuilder;
use Illuminate\Database\Seeder;

class FaqPageSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            ['Do I need to sign in or share my personal credentials?', 'Public lookups do not require connecting your social account. Never provide social passwords or login cookies. Follow the input guidance and complete the security code.'],
            ['Will target profile owners see my IP address or actions in their logs?', 'Lookups are processed server-side through a data service rather than through your personal social account. This does not guarantee absolute anonymity or the absence of logs across all services. Read our Privacy Policy for information handling.'],
            ['Can I download high-definition media (Photos, Reels, Videos)?', 'You can save media when a lookup returns an available downloadable file. Resolution and format depend on the source. Only save content you own or have permission to download.'],
            ['Is there a limit on search queries or downloads?', 'Requests may be limited to prevent abuse, and upstream services can impose their own restrictions. If a request is limited, allow time before retrying. Availability and success are not guaranteed.'],
            ['Which platforms are supported?', 'The current toolkit includes Facebook, X (Twitter), Threads and LinkedIn. Available tools differ by platform; open its tools page to see the current catalog.'],
            ['Can I view private profiles or restricted posts?', 'No. The tools are for available public content and do not bypass private profiles, closed groups, friends-only posts or other access restrictions.'],
        ];
        foreach ($questions as $index => [$question, $answer]) {
            Faq::updateOrCreate(['question' => $question], ['answer' => $answer, 'order_index' => $index + 1]);
        }
        SitemapBuilder::flush();
    }
}
