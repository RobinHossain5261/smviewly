@php
    $section = collect($result['sections'] ?? [])->firstWhere('key', 'courses');
    $items = $section['items'] ?? ($result['items'] ?? []);
@endphp
<div class="p-5 sm:p-8">
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="rounded-full bg-brand-primary/10 text-brand-primary px-3 py-1 text-[10px] font-bold uppercase tracking-wider">{{ $result['tool_name'] }}</span>
        <h2 class="text-lg font-extrabold text-slate-900">Courses</h2>
        @if(isset($result['query']) && $result['query'] !== '')
            <span class="text-xs font-bold text-slate-400">· {{ $result['query'] }}</span>
        @endif
    </div>

    @if(count($items))
        <div class="space-y-3">
            @foreach($items as $course)
                <div class="rounded-2xl border border-slate-100 bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-extrabold text-slate-900 break-words">{{ $course['title'] }}</p>
                            @if(!empty($course['meta']))
                                <p class="text-xs text-slate-500 font-semibold mt-1">{{ $course['meta'] }}</p>
                            @endif
                        </div>
                        @if(!empty($course['url']))
                            <a href="{{ $course['url'] }}" target="_blank" rel="noopener noreferrer" class="shrink-0 inline-flex items-center gap-1 rounded-lg bg-brand-primary px-3 py-1.5 text-xs font-bold text-white hover:opacity-95">
                                View <i class="ph ph-arrow-up-right"></i>
                            </a>
                        @endif
                    </div>
                    @if(!empty($course['text']) && $course['text'] !== $course['title'])
                        <p class="text-xs text-slate-600 mt-2 whitespace-pre-line break-words leading-relaxed">{{ \Illuminate\Support\Str::limit($course['text'], 160) }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500 font-medium py-10 text-center">No courses found for this lookup.</p>
    @endif
</div>
