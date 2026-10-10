@php
    $platformQuestions = [
        ['Which Facebook tool should I choose?', 'Use Page Viewer for a public page, Profile Viewer for a person, Group Viewer for a public group, or Post Viewer for a specific post. Photos, reels, and videos each have dedicated page tools.'],
        ['Where do I find the right Facebook link?', 'Open the page, profile, group, or post in Facebook and copy its address, or use the Share menu to copy a link. For an individual post, copy the post link rather than the page address. Follow the input guidance on your chosen tool.'],
        ['Can I access private profiles or groups?', 'No. These tools are for publicly available content. They do not unlock friends-only posts, private groups, or content that requires permission from its owner.'],
        ['Why does a public page sometimes return no results?', 'Facebook may require a login, restrict a page by age or location, remove a post, or temporarily limit requests. Public visibility in your own logged-in account does not guarantee that the content is available to a logged-out lookup. Check the link and try the tool that matches it.'],
        ['Can I save photos, reels, and videos?', 'You can save media when a lookup returns an available downloadable file. Format and resolution depend on the source. Only download content you own or have permission to save.'],
    ];

    $platformMeta = \App\Support\PlatformCatalog::get($platform);
    $platformName = $platformMeta['name'];
    $toolExamples = collect(\App\Support\PlatformCatalog::tools($platform))->take(3)->pluck('name')->join(', ');
    if ($platform !== 'facebook') {
        $platformQuestions = [
            ["Which {$platformName} tool should I choose?", "Choose the tool that matches what you want to explore, such as {$toolExamples}. Each tool describes its supported content and input."],
            ["What should I enter in a {$platformName} tool?", 'Follow the input guidance on your chosen tool. Depending on the tool, use a public URL, username, name, or search query. Input examples: '.$platformMeta['placeholder'].'.'],
            ['Can I access private or restricted content?', 'No. These tools only show publicly available content. They do not bypass privacy settings or account permissions.'],
            ['Why might a lookup return no results?', "{$platformName} may require a login, restrict content, remove a post, or temporarily limit requests. Check the input and try the tool that matches it."],
            ['Can I save media from the results?', 'Only when the results include a downloadable file. Availability and quality depend on the source. Save content you own or have permission to download.'],
        ];
    }
    $chooseToolCopy = $platform === 'facebook'
        ? 'Pick a page, profile, group, or post tool. For photos, reels, and videos, use the dedicated page media tools.'
        : 'Explore '.$platformMeta['short'].'. Choose a tool such as '.$toolExamples.' and check its input guidance.';
@endphp
<div class="sm-section-band sm-band-how">
<section class="sm-container sm-how" aria-labelledby="{{ $platform }}-how-title">
    <div class="sm-how-intro"><p class="sm-kicker">Simple & Transparent</p><h2 id="{{ $platform }}-how-title">From {{ $platformName }} input to available content.</h2><p>Choose the tool that matches your link. No {{ $platformName }} account connection required.</p><a class="sm-how-cta" href="{{ route('viewer.tool', ['platform' => $platform, 'tool' => \App\Support\PlatformCatalog::defaultTool($platform)['slug']]) }}">Try a lookup <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div>
    <div class="sm-steps">
        <article class="sm-step-card"><span class="sm-step-number">STEP 01</span><span class="sm-step-icon"><i class="ph ph-squares-four" aria-hidden="true"></i></span><h3>Choose the matching tool</h3><p>{{ $chooseToolCopy }}</p></article>
        <article class="sm-step-card"><span class="sm-step-number">STEP 02</span><span class="sm-step-icon"><i class="ph ph-link" aria-hidden="true"></i></span><h3>Enter your link or search</h3><p>Follow the tool&#39;s input guidance, enter the supported URL, name, or query, and complete the security code.</p></article>
        <article class="sm-step-card"><span class="sm-step-number">STEP 03</span><span class="sm-step-icon"><i class="ph ph-eye" aria-hidden="true"></i></span><h3>Review the results</h3><p>Browse available information and media. Save a file only if it is downloadable and you have permission to keep it.</p></article>
    </div>
</section>
</div>
<div class="sm-section-band sm-band-faq">
    <section class="sm-container sm-faq" aria-labelledby="{{ $platform }}-faq-title">
        <div class="sm-faq-sidebar"><p class="sm-kicker">Common questions</p><h2 id="{{ $platform }}-faq-title">{{ $platformName }} tools, explained.</h2><p class="sm-faq-sub">Link formats, public visibility, and what to expect from a lookup.</p><a class="sm-text-link" href="{{ route('contact') }}">Still have questions? Contact support <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div>
        <div class="sm-faq-list">
            @foreach($platformQuestions as $index => [$question, $answer])
            <div class="sm-faq-item {{ $index === 0 ? 'is-open' : '' }}">
                <button class="sm-faq-question" type="button" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="{{ $platform }}-faq-{{ $index }}"><span>{{ $question }}</span><span class="sm-faq-toggle"><i class="ph ph-plus" aria-hidden="true"></i></span></button>
                <div class="sm-faq-collapse" id="{{ $platform }}-faq-{{ $index }}"><div class="sm-faq-body"><p>{{ $answer }}</p></div></div>
            </div>
            @endforeach
        </div>
    </section>
</div>
