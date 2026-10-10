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

        try {
            if (Schema::hasTable('site_settings')) {
                $settingsArray = SiteSetting::allSettings();
                $settingsObj = (object) [
                    'brand_name' => $settingsArray['brand_name'] ?? 'SM Viewer',
                    'primary_color' => $settingsArray['primary_color'] ?? '#2563eb',
                    'secondary_color' => $settingsArray['secondary_color'] ?? '#2563eb',
                    'meta_title' => $settingsArray['meta_title'] ?? 'SM Viewer | Anonymous Social Media Viewer & Downloader',
                    'meta_description' => $settingsArray['meta_description'] ?? 'SM Viewer (Social Media Viewer) lets you view and download public Facebook, X, Threads, and LinkedIn content anonymously. No login required.',
                    'contact_email' => $settingsArray['contact_email'] ?? 'support@smviewer.org',
                    'contact_phone' => $settingsArray['contact_phone'] ?? '',
                    'linkedin_url' => $settingsArray['linkedin_url'] ?? '',
                    'twitter_url' => $settingsArray['twitter_url'] ?? '',
                    'facebook_url' => $settingsArray['facebook_url'] ?? '',
                    'logo_path' => $settingsArray['logo_path'] ?? 'images/logo.png',
                    'favicon_path' => $settingsArray['favicon_path'] ?? 'images/favicon.ico',
                    'custom_head_scripts' => $settingsArray['custom_head_scripts'] ?? '',
                    'about_text' => $settingsArray['about_text'] ?? '',
                    'home_blog_title' => $settingsArray['home_blog_title'] ?? 'Latest Social Media Strategy Guides & Tips',
                    'home_blog_description' => $settingsArray['home_blog_description'] ?? '',
                    'brand_initial' => strtoupper(mb_substr($settingsArray['brand_name'] ?? 'SM Viewer', 0, 1)),
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
            'brand_name' => 'SM Viewer',
            'primary_color' => '#2563eb',
            'secondary_color' => '#2563eb',
            'meta_title' => 'SM Viewer | Anonymous Social Media Viewer & Downloader',
            'meta_description' => 'SM Viewer (Social Media Viewer) lets you view and download public Facebook, X, Threads, and LinkedIn content anonymously. No login required.',
            'contact_email' => 'support@smviewer.org',
            'contact_phone' => '',
            'linkedin_url' => '',
            'twitter_url' => '',
            'facebook_url' => '',
            'logo_path' => 'images/logo.png',
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
