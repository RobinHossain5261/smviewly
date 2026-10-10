@php
    $section = collect($result['sections'] ?? [])->firstWhere('key', 'trends');
    $items = $section['items'] ?? ($result['items'] ?? []);
@endphp
<div class="p-5 sm:p-8">
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="rounded-full bg-brand-primary/10 text-brand-primary px-3 py-1 text-[10px] font-bold uppercase tracking-wider">{{ $result['tool_name'] }}</span>
        <h2 class="text-lg font-extrabold text-slate-900">Trending now</h2>
        @if(isset($result['query']) && $result['query'] !== '')
            <span class="text-xs font-bold text-slate-400">· {{ $result['query'] }}</span>
        @endif
    </div>

    @if(count($items))
        <div class="divide-y divide-slate-100 border border-slate-100 rounded-2xl bg-white">
            @foreach($items as $i => $item)
                <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="flex items-start gap-4 p-4 hover:bg-slate-50 transition-colors">
                    <span class="w-8 text-center text-sm font-extrabold text-slate-300 tabular-nums">{{ $i + 1 }}</span>
                    <div class="min-w-0 flex-grow">
                        <p class="text-sm font-bold text-slate-900 break-words">{{ $item['title'] }}</p>
                        @if(!empty($item['meta']))
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">{{ $item['meta'] }}</p>
                        @endif
                    </div>
                    <i class="ph ph-arrow-up-right text-slate-300 mt-1"></i>
                </a>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500 font-medium py-10 text-center">No trends found for this lookup.</p>
    @endif
</div>
