@php
    $items = $result['items'] ?? [];
@endphp
<div class="p-5 sm:p-8">
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="rounded-full bg-brand-primary/10 text-brand-primary px-3 py-1 text-[10px] font-bold uppercase tracking-wider">{{ $result['tool_name'] }}</span>
        @if($result['title'] || $result['handle'])
            <p class="text-sm font-extrabold text-slate-900">{{ $result['title'] ?: $result['handle'] }}</p>
        @endif
        @if(isset($result['query']) && $result['query'] !== '')
            <span class="text-xs font-bold text-slate-400">· {{ $result['query'] }}</span>
        @endif
    </div>

    @if(count($items))
        <div class="space-y-2">
            @foreach($items as $item)
                <div class="rounded-2xl border border-slate-100 p-4">
                    <p class="text-sm font-extrabold text-slate-900">{{ $item['title'] }}</p>
                    @if(!empty($item['text']) && $item['text'] !== $item['title'])
                        <p class="text-xs text-slate-600 mt-1 whitespace-pre-line break-words">{{ $item['text'] }}</p>
                    @endif
                    @if(!empty($item['meta']))
                        <p class="text-[11px] text-slate-400 mt-1 font-semibold">{{ $item['meta'] }}</p>
                    @endif
                    @if(!empty($item['url']))
                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-block mt-2 text-[11px] font-bold text-brand-primary">Open</a>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500 font-medium py-10 text-center">No results found for this lookup.</p>
    @endif
</div>
