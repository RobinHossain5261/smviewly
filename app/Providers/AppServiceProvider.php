<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Models\Post;
use App\Observers\PostObserver;
use App\Support\PlatformCatalog;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Post::observe(PostObserver::class);

        view()->composer('*', function ($view) {
            $settings = $view->getData()['siteSettings'] ?? null;
            $view->with('logoUrl', \App\Support\BrandAssets::url($settings->logo_path ?? null, 'images/logo.webp'));
            $view->with('faviconUrl', asset('favicon.ico').'?v='.filemtime(public_path('favicon.ico')));
        });

        try {
            if (Schema::hasTable('site_settings')) {
                $settingsArray = SiteSetting::allSettings();
                $settingsObj = (object) [
                    'brand_name' => $settingsArray['brand_name'] ?? 'smviewly',
                    'primary_color' => $settingsArray['primary_color'] ?? '#2563eb',
                    'secondary_color' => $settingsArray['secondary_color'] ?? '#2563eb',
                    'meta_title' => $settingsArray['meta_title'] ?? 'smviewly | Public Social Media Viewer',
                    'meta_description' => $settingsArray['meta_description'] ?? 'Explore available public Facebook pages, X profiles, Threads posts and LinkedIn company information with smviewly. No social account connection needed.',
                    'contact_email' => $settingsArray['contact_email'] ?? 'support@smviewer.org',
                    'contact_phone' => $settingsArray['contact_phone'] ?? '',
                    'linkedin_url' => $settingsArray['linkedin_url'] ?? '',
                    'twitter_url' => $settingsArray['twitter_url'] ?? '',
                    'facebook_url' => $settingsArray['facebook_url'] ?? '',
                    'logo_path' => $settingsArray['logo_path'] ?? 'images/logo.webp',
                    'favicon_path' => $settingsArray['favicon_path'] ?? 'images/favicon.ico',
                    'custom_head_scripts' => $settingsArray['custom_head_scripts'] ?? '',
                    'about_text' => $settingsArray['about_text'] ?? '',
                    'home_blog_title' => $settingsArray['home_blog_title'] ?? 'Latest Social Media Strategy Guides & Tips',
                    'home_blog_description' => $settingsArray['home_blog_description'] ?? '',
                    'brand_initial' => strtoupper(mb_substr($settingsArray['brand_name'] ?? 'smviewly', 0, 1)),
                    'brand_expand' => $settingsArray['brand_expand'] ?? 'Social Media Viewer',
                ];
                view()->share('siteSettings', $settingsObj);
                view()->share('platforms', PlatformCatalog::all());
            } else {
                $this->shareFallbackSettings();
            }
        } catch (\Exception $e) {
            $this->shareFallbackSettings();
        }
    }

    /**
     * Fallback values if table doesn't exist yet
     */
    private function shareFallbackSettings(): void
    {
        view()->share('platforms', PlatformCatalog::all());
        view()->share('siteSettings', (object) [
            'brand_name' => 'smviewly',
            'primary_color' => '#2563eb',
            'secondary_color' => '#2563eb',
            'meta_title' => 'smviewly | Public Social Media Viewer',
            'meta_description' => 'Explore available public Facebook pages, X profiles, Threads posts and LinkedIn company information with smviewly. No social account connection needed.',
            'contact_email' => 'support@smviewer.org',
            'contact_phone' => '',
            'linkedin_url' => '',
            'twitter_url' => '',
            'facebook_url' => '',
            'logo_path' => 'images/logo.webp',
            'favicon_path' => 'images/favicon.ico',
            'custom_head_scripts' => '',
            'about_text' => '',
            'home_blog_title' => 'Latest Social Media Strategy Guides & Tips',
            'home_blog_description' => '',
            'brand_initial' => 'S',
            'brand_expand' => 'Social Media Viewer',
        ]);
    }
}
