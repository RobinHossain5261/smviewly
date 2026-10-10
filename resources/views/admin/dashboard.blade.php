@extends('layouts.admin')
@section('content')
<div class="ad-dashboard">
<section class="ad-welcome"><div><p class="ad-eyebrow">YOUR WORKSPACE</p><h1>Welcome back, {{ auth()->user()->name }}.</h1><p>Keep your content fresh and your community connected.</p><div class="ad-welcome-links"><a class="ad-button" href="{{ route('admin.posts.create') }}"><i class="ph ph-plus" aria-hidden="true"></i> Write an article</a><a href="{{ route('home') }}">View website &nearr;</a></div></div><div class="ad-welcome-note"><i class="ph ph-shield-check" aria-hidden="true"></i><strong>Site administration</strong><span>Messages, content, and settings in one place.</span></div></section>
<div class="ad-stats">
@foreach([
['Contact messages', $contactsCount, 'ph-envelope-simple', 'admin.contacts.index', 'Your community inbox'],
['Published articles', $publishedCount, 'ph-article', 'admin.posts.index', 'Live on your blog'],
['Draft articles', $postsCount - $publishedCount, 'ph-note-pencil', 'admin.posts.index', 'Ready for your next edit'],
['Categories', $categoriesCount, 'ph-folder', 'admin.categories.index', 'Organize your content'],
] as [$label, $count, $icon, $destination, $caption])
<a class="ad-stat" href="{{ route($destination) }}"><span class="ad-stat-label">{{ $label }} <i class="ph {{ $icon }}" aria-hidden="true"></i></span><strong>{{ $count }}</strong><span>{{ $caption }} &nearr;</span></a>
@endforeach
</div>
<div class="ad-content-grid">
<section class="ad-panel"><div class="ad-panel-heading"><div><p class="ad-eyebrow">COMMUNITY</p><h2>Contact inbox</h2></div><a href="{{ route('admin.contacts.index') }}">All messages &rarr;</a></div>
@forelse($recentContacts as $contact)
<details class="ad-message"><summary><span class="ad-avatar">{{ mb_substr($contact->name, 0, 1) }}</span><span class="ad-message-copy"><strong>{{ $contact->subject ?: 'General enquiry' }}</strong><small>{{ $contact->name }} &middot; {{ $contact->created_at->diffForHumans() }}</small></span><i class="ph ph-caret-down" aria-hidden="true"></i></summary><div class="ad-message-body"><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a><p>{{ $contact->message }}</p></div></details>
@empty
<div class="ad-empty"><i class="ph ph-chat-circle" aria-hidden="true"></i><strong>Your inbox is ready.</strong><p>Messages from the contact page will appear here.</p></div>
@endforelse
</section>
<section class="ad-panel"><div class="ad-panel-heading"><div><p class="ad-eyebrow">PUBLISHING</p><h2>Recent articles</h2></div><a href="{{ route('admin.posts.index') }}">Manage blog &rarr;</a></div>
@forelse($recentPosts as $post)
<a class="ad-post" href="{{ route('admin.posts.edit', $post->id) }}"><span><span class="ad-status {{ $post->status === 'published' ? 'ad-status-live' : '' }}">{{ ucfirst($post->status) }}</span><strong>{{ $post->title }}</strong><small>{{ $post->category?->name ?? 'Uncategorized' }} &middot; {{ $post->created_at->format('M d, Y') }}</small></span><i class="ph ph-pencil-simple" aria-hidden="true"></i></a>
@empty
<div class="ad-empty"><i class="ph ph-note-pencil" aria-hidden="true"></i><strong>Your next story starts here.</strong><p>Create your first article using the button above.</p></div>
@endforelse
</section></div>
<section class="ad-quick-section"><p class="ad-eyebrow">MAKE IT YOURS</p><h2>Website controls</h2><div class="ad-quick-grid">
@foreach([
['admin.settings', 'ph-sliders-horizontal', 'Site settings', 'Update branding, contact details, and integrations.'],
['admin.faqs.index', 'ph-question', 'Questions & answers', $faqsCount.' FAQs. Keep your help content useful.'],
['admin.categories.index', 'ph-folder', 'Blog categories', 'Create and rename categories for your articles.'],
] as [$destination, $icon, $label, $description])
<a class="ad-quick" href="{{ route($destination) }}"><i class="ph {{ $icon }}" aria-hidden="true"></i><strong>{{ $label }}</strong><p>{{ $description }}</p><span>Manage &nearr;</span></a>
@endforeach
</div></section></div>
@endsection
