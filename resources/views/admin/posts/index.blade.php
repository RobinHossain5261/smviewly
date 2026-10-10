@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Manage Blog Posts</h1>
            <p class="text-xs sm:text-sm text-slate-505 mt-1 font-semibold">Publish, update, and manage all articles and search engine guides.</p>
        </div>
        <a href="{{ route('admin.posts.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-primary px-4 py-2.5 text-xs sm:text-sm font-bold text-white hover:opacity-95 transition-all">
            + Write Article
        </a>
    </div>

    <!-- Posts Table Container -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-550 font-bold">
                        <th class="pb-3 pr-4 text-xs uppercase tracking-wide">Image</th>
                        <th class="pb-3 text-xs uppercase tracking-wide">Title</th>
                        <th class="pb-3 text-xs uppercase tracking-wide">Category</th>
                        <th class="pb-3 text-xs uppercase tracking-wide">Status</th>
                        <th class="pb-3 text-xs uppercase tracking-wide">Date Published</th>
                        <th class="pb-3 text-xs uppercase tracking-wide text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($posts as $post)
                        <tr class="text-slate-600 hover:bg-slate-50/50 transition-colors">
                            <!-- Thumbnail image -->
                            <td class="py-4 pr-4">
                                <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="h-10 w-16 object-cover rounded bg-slate-100 border border-slate-200">
                            </td>
                            
                            <!-- Title & Slug -->
                            <td class="py-4 font-bold text-slate-900 max-w-xs truncate">
                                <a href="{{ route('admin.posts.edit', $post->id) }}" class="hover:underline hover:text-brand-primary">
                                    {{ $post->title }}
                                </a>
                                <span class="block text-[10px] text-slate-500 font-medium font-mono mt-0.5 truncate">{{ $post->slug }}</span>
                            </td>

                            <!-- Category -->
                            <td class="py-4 text-xs font-semibold text-slate-500">{{ $post->category->name }}</td>

                            <!-- Status -->
                            <td class="py-4">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold tracking-wider uppercase {{ $post->status === 'published' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-yellow-50 text-yellow-750 border border-yellow-200' }}">
                                    {{ $post->status }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="py-4 text-xs text-slate-500 font-semibold">{{ $post->created_at->format('M d, Y') }}</td>

                            <!-- Action operations -->
                            <td class="py-4 text-right font-semibold">
                                <div class="flex items-center justify-end gap-3.5">
                                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="text-xs font-bold text-brand-primary hover:underline">
                                        Edit
                                    </a>
                                    
                                    <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this blog post?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-red-650 hover:text-red-700 hover:underline">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500 font-semibold">No blog posts found. Write a post to display it here.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginations -->
        <div class="mt-6 border-t border-slate-100 pt-4">
            {{ $posts->links() }}
        </div>
    </div>
</div>
@endsection
