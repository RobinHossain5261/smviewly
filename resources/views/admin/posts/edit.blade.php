@extends('layouts.admin')

@section('content')
<div class="space-y-8 max-w-4xl">
    <!-- Header -->
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.posts.index') }}" class="text-xs font-bold text-slate-505 hover:text-slate-900 transition-colors">
            ← Back
        </a>
        <h1 class="text-2xl font-bold text-slate-900 ml-2">Edit Blog Post</h1>
    </div>

    <!-- Form container -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
        <form action="{{ route('admin.posts.update', $post->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Title -->
            <div>
                <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Article Title</label>
                <input type="text" name="title" required value="{{ old('title', $post->title) }}" placeholder="e.g. How to View Stories Anonymously" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
            </div>

            <!-- Categories / Status grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Category</label>
                    <select name="category_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3.5 px-4 text-xs sm:text-sm text-slate-900 focus:border-brand-primary focus:outline-none cursor-pointer">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Status</label>
                    <select name="status" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3.5 px-4 text-xs sm:text-sm text-slate-900 focus:border-brand-primary focus:outline-none cursor-pointer">
                        <option value="published" {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Published (Live on site)</option>
                        <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Draft (Admin only)</option>
                    </select>
                </div>
            </div>

            <!-- Feature Image Upload -->
            <div>
                <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Featured Image (Upload replaces current image)</label>
                <input type="file" name="image" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                
                <div class="mt-3 flex items-center gap-3 p-3 bg-slate-50 border border-slate-150 rounded-xl max-w-xs">
                    <img src="{{ $post->image_url }}" alt="Post image preview" class="h-10 w-16 object-cover rounded bg-slate-100 border border-slate-200">
                    <span class="text-[10px] text-slate-500 font-medium">Active featured image file</span>
                </div>
            </div>

            <!-- Content body -->
            <div>
                <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Content Details (Line Breaks supported)</label>
                <textarea name="content" required rows="10" placeholder="Type the article body content details here..." class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3.5 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none leading-relaxed">{{ old('content', $post->content) }}</textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full rounded-xl bg-brand-primary py-4 text-xs sm:text-sm font-bold text-white hover:opacity-95 transition-all">
                Update Article Details
            </button>
        </form>
    </div>
</div>
@endsection
