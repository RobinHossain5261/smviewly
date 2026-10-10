@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Manage Categories</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1 font-semibold">Organize articles and search engine guides under custom taxonomies.</p>
    </div>

    <!-- Grid: Add Category Form (Left) & Categories List (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Add Category Form -->
        <div class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-5">Create New Category</h3>
                
                <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Category Name</label>
                        <input type="text" name="name" required placeholder="e.g. Social Media Strategy" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-brand-primary py-3.5 text-xs font-bold text-white hover:opacity-95 transition-all">
                        Create Category
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Categories List Table -->
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-5">Existing Categories</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-500 font-bold">
                                <th class="pb-3 text-xs uppercase tracking-wide">Name</th>
                                <th class="pb-3 text-xs uppercase tracking-wide">Slug / Taxonomy</th>
                                <th class="pb-3 text-xs uppercase tracking-wide">Associated Posts</th>
                                <th class="pb-3 text-xs uppercase tracking-wide text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($categories as $category)
                                <tr class="text-slate-600">
                                    <!-- Name -->
                                    <td class="py-3.5 font-bold text-slate-900">
                                        {{ $category->name }}
                                    </td>
                                    
                                    <!-- Slug -->
                                    <td class="py-3.5 font-mono text-[10px] text-slate-500">{{ $category->slug }}</td>
                                    
                                    <!-- Count -->
                                    <td class="py-3.5">
                                        <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold">
                                            {{ $category->posts_count }} posts
                                        </span>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-3.5">
                                            <!-- Simple inline rename link via prompt -->
                                            <button type="button" 
                                                    onclick="renameCategory({{ $category->id }}, '{{ $category->name }}')"
                                                    class="text-xs font-bold text-brand-primary hover:underline">
                                                Rename
                                            </button>

                                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category? This cannot be undone.');" class="inline">
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
                                    <td colspan="4" class="py-8 text-center text-slate-500 font-semibold">No categories found. Add one on the left.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Simple Hidden Form to handle category updates/renaming via prompt -->
<form id="rename-category-form" action="" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="name" id="rename-category-input">
</form>

<script>
    /**
     * Shows a browser prompt to quickly rename a category without complex modals.
     */
    function renameCategory(id, currentName) {
        const newName = prompt("Rename Category:", currentName);
        if (newName && newName.trim() !== "" && newName.trim() !== currentName) {
            const form = document.getElementById('rename-category-form');
            form.action = "/admin/categories/" + id;
            document.getElementById('rename-category-input').value = newName.trim();
            form.submit();
        }
    }
</script>
@endsection
