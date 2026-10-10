@extends('layouts.admin')

@section('content')
<div class="space-y-8 max-w-4xl">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Rebranding & Site Settings</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1 font-semibold">Configure global variables, logo files, brand style sheets, and scraper credentials.</p>
    </div>

    <!-- Form Container -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Core Branding -->
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">1. Core Branding</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Brand Name</label>
                        <input type="text" name="brand_name" required value="{{ old('brand_name', $settings['brand_name'] ?? 'SM Viewer') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Contact Email Address</label>
                        <input type="email" name="contact_email" required value="{{ old('contact_email', $settings['contact_email'] ?? 'support@smviewer.org') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Contact Phone (optional)</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" placeholder="+1 555 0100" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">LinkedIn URL</label>
                        <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $settings['linkedin_url'] ?? '') }}" placeholder="https://www.linkedin.com/company/…" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">X / Twitter URL</label>
                        <input type="url" name="twitter_url" value="{{ old('twitter_url', $settings['twitter_url'] ?? '') }}" placeholder="https://x.com/…" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Facebook URL</label>
                        <input type="url" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}" placeholder="https://facebook.com/…" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                    <!-- Brand Logo Upload -->
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Upload Logo (Replaces default icon)</label>
                        <input type="file" name="logo" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                        @if(!empty($settings['logo_path']))
                            <div class="mt-3 flex items-center gap-3 p-3 bg-slate-50 border border-slate-150 rounded-xl">
                                <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Brand Logo Thumbnail" class="h-6 w-auto">
                                <span class="text-[10px] text-slate-500 font-medium">Active logo image file</span>
                            </div>
                        @endif
                    </div>

                    <!-- Theme Colors -->
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Theme Palette Variables</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/50 p-2">
                                <input type="color" name="primary_color" required value="{{ old('primary_color', $settings['primary_color'] ?? '#4f46e5') }}" class="h-8 w-10 border-0 bg-transparent cursor-pointer rounded">
                                <span class="text-xs text-slate-700 font-medium">Primary</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/50 p-2">
                                <input type="color" name="secondary_color" required value="{{ old('secondary_color', $settings['secondary_color'] ?? '#ec4899') }}" class="h-8 w-10 border-0 bg-transparent cursor-pointer rounded">
                                <span class="text-xs text-slate-700 font-medium">Secondary</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: SEO Settings -->
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">2. Search Engine Optimization</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">SEO Title Tag (Meta Title)</label>
                        <input type="text" name="meta_title" required value="{{ old('meta_title', $settings['meta_title'] ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">SEO Meta Description</label>
                        <textarea name="meta_description" required rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 3: Credentials & Integrations -->
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">3. Scraper & Integrations</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">CheckMyAlgo API Base URL</label>
                        <input type="url" name="social_api_base_url" value="{{ old('social_api_base_url', $settings['social_api_base_url'] ?? 'https://data.checkmyalgo.com') }}" placeholder="https://data.checkmyalgo.com" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">CheckMyAlgo Public API Token (serp_…)</label>
                        <input type="password" name="social_api_token" value="{{ old('social_api_token', $settings['social_api_token'] ?? '') }}" placeholder="serp_…" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                        <p class="text-[10px] text-slate-500 mt-1">Powers Facebook, X, Threads, and LinkedIn lookups. Generate the token in data.checkmyalgo.com API settings.</p>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-600">
                        <input type="hidden" name="social_api_use_proxy" value="0">
                        <input type="checkbox" name="social_api_use_proxy" value="1" {{ old('social_api_use_proxy', $settings['social_api_use_proxy'] ?? '0') === '1' ? 'checked' : '' }}>
                        Use rotating proxy on the data API
                    </label>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Custom Head Scripts (AdSense or Tracking Snippets)</label>
                        <textarea name="custom_head_scripts" rows="3" placeholder="<!-- Place custom analytics scripts or header tracking files here -->" class="w-full font-mono rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-705 placeholder-slate-400 focus:border-brand-primary focus:outline-none">{{ old('custom_head_scripts', $settings['custom_head_scripts'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 4: General Text Descriptions -->
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">4. Profile Descriptions</h3>
                
                <div>
                    <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">About Section Description</label>
                    <textarea name="about_text" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">{{ old('about_text', $settings['about_text'] ?? '') }}</textarea>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Home Page Blog Section Title</label>
                    <input type="text" name="home_blog_title" value="{{ old('home_blog_title', $settings['home_blog_title'] ?? 'Latest Social Media Strategy Guides & Tips') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Home Page Blog Summary</label>
                    <textarea name="home_blog_description" rows="12" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">{{ old('home_blog_description', $settings['home_blog_description'] ?? '') }}</textarea>
                    <p class="text-[10px] text-slate-500 mt-1">Short summary only. Do not paste keyword-stuffed articles here.</p>
                </div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-brand-primary py-4 text-xs sm:text-sm font-bold text-white hover:opacity-95 transition-all">
                Save Global Configurations
            </button>
        </form>
    </div>
</div>
@endsection
