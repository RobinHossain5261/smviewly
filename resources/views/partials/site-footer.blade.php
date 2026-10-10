<footer class="sm-footer">
    <div class="sm-container">
        <div class="sm-footer-top">
            <div class="sm-footer-brand-col">
                <a class="sm-brand" href="{{ route('home') }}">
                    <span class="sm-logo"><i class="ph-bold ph-scan" aria-hidden="true"></i></span>
                    <span>{{ $siteSettings->brand_name }}</span><span class="sm-brand-dot">.</span>
                </a>
                <p>Explore public profiles, posts, and available media across four social platforms. No social account connection required.</p>
            </div>
            <nav aria-label="Tools navigation">
                <h3>Explore Tools</h3>
                <a href="{{ route('tools') }}">All Tools Toolkit</a>
                @foreach(\App\Support\PlatformCatalog::all() as $footerSlug => $footerMeta)
                <a href="{{ route('viewer.tool', ['platform' => $footerSlug]) }}">{{ $footerMeta['name'] }} Tools</a>
                @endforeach
            </nav>
            <nav aria-label="Company navigation">
                <h3>Company</h3>
                <a href="{{ route('about') }}">About Us</a>
                <a href="{{ route('blog.index') }}">Resources & Guides</a>
                <a href="{{ route('contact') }}">Contact Support</a>
            </nav>
            <nav aria-label="Legal navigation">
                <h3>Legal & Privacy</h3>
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms of Service</a>
                <a href="{{ route('faqs') }}">Frequently Asked Questions</a>
            </nav>
        </div>
        <div class="sm-footer-bottom">
            <span>&copy; {{ date('Y') }} {{ $siteSettings->brand_name }}. All rights reserved.</span>
            <p>SM Viewer is an independent research tool and is not affiliated with, sponsored by, or endorsed by Meta, Facebook, X, or LinkedIn.</p>

        </div>
    </div>
</footer>
<a href="#page-top" class="sm-back-to-top" aria-label="Back to top" aria-hidden="true" tabindex="-1"><i class="ph-bold ph-arrow-up" aria-hidden="true"></i></a>
