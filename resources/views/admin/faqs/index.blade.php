@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Frequently Asked Questions Manager</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1 font-semibold">Add, update, or remove dynamic FAQ items displayed on your site.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Side: Add New FAQ Form -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 h-fit">
            <h2 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Create FAQ</h2>
            
            <form action="{{ route('admin.faqs.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Question</label>
                    <input type="text" name="question" required placeholder="e.g. Is this service free?" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Answer Content</label>
                    <textarea name="answer" required rows="4" placeholder="Enter answer details here..." class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-1.5">Sort Order Index</label>
                    <input type="number" name="order_index" required value="0" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-brand-primary focus:outline-none">
                </div>

                <button type="submit" class="w-full rounded-xl bg-brand-primary py-3 text-xs sm:text-sm font-bold text-white hover:opacity-95 transition-all cursor-pointer">
                    Save FAQ
                </button>
            </form>
        </div>

        <!-- Right Side: FAQ List Table -->
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Seeded & Configured FAQs</h2>
            
            <div class="space-y-4">
                @forelse($faqs as $faq)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between gap-4">
                        <form action="{{ route('admin.faqs.update', $faq->id) }}" method="POST" class="space-y-3 w-full">
                            @csrf
                            
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
                                <div class="sm:col-span-3">
                                    <input type="text" name="question" required value="{{ $faq->question }}" class="w-full rounded-lg border border-slate-200 bg-white py-2 px-3 text-xs text-slate-900 focus:border-brand-primary focus:outline-none">
                                </div>
                                <div>
                                    <input type="number" name="order_index" required value="{{ $faq->order_index }}" class="w-full rounded-lg border border-slate-200 bg-white py-2 px-3 text-xs text-slate-900 focus:border-brand-primary focus:outline-none">
                                </div>
                            </div>
                            
                            <div>
                                <textarea name="answer" required rows="2" class="w-full rounded-lg border border-slate-200 bg-white py-2 px-3 text-xs text-slate-900 focus:border-brand-primary focus:outline-none">{{ $faq->answer }}</textarea>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-slate-250">
                                <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-[10px] font-bold text-white hover:bg-green-700 transition-colors cursor-pointer">
                                    Update
                                </button>
                        </form>
                        
                        <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg bg-red-50 text-red-600 border border-red-200 px-3 py-1.5 text-[10px] font-bold hover:bg-red-650 hover:text-white transition-colors cursor-pointer">
                                Delete
                            </button>
                        </form>
                            </div>
                    </div>
                @empty
                    <p class="text-center text-xs text-slate-500 font-semibold py-8">No FAQ items defined yet. Use the creation panel.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
