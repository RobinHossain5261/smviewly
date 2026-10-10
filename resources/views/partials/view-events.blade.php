@php
    $section = collect($result['sections'] ?? [])->firstWhere('key', 'events');
    $items = $section['items'] ?? ($result['items'] ?? []);
@endphp
<div class="p-5 sm:p-8">
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="rounded-full bg-brand-primary/10 text-brand-primary px-3 py-1 text-[10px] font-bold uppercase tracking-wider">{{ $result['tool_name'] }}</span>
        <h2 class="text-lg font-extrabold text-slate-900">Events</h2>
        @if($result['title'] || $result['handle'])
            <span class="text-xs font-bold text-slate-400">· {{ $result['title'] ?: $result['handle'] }}</span>
        @endif
    </div>

    @if(count($items))
        <div class="space-y-3">
            @foreach($items as $event)
                <div class="rounded-2xl border border-slate-100 bg-white p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary">
                            @php $d = date_parse((string)($event['raw']['start_time'] ?? $event['raw']['date'] ?? '')); @endphp
                            @if(!empty($d['month']))
                                <span class="text-[10px] font-extrabold uppercase leading-none">{{ date('M', mktime(0,0,0,$d['month'],1,$d['year'] ?: 2000)) }}</span>
                                <span class="text-sm font-extrabold leading-tight">{{ $d['day'] }}</span>
                            @else
                                <i class="ph ph-calendar text-lg"></i>
                            @endif
                        </div>
                        <div class="min-w-0 flex-grow">
                            <p class="text-sm font-extrabold text-slate-900 break-words">{{ $event['title'] }}</p>
                            @if(!empty($event['meta']))
                                <p class="text-xs text-slate-500 font-semibold mt-0.5">{{ $event['meta'] }}</p>
                            @endif
                            @if(!empty($event['text']) && $event['text'] !== $event['title'])
                                <p class="text-xs text-slate-600 mt-1.5 whitespace-pre-line break-words leading-relaxed">{{ \Illuminate\Support\Str::limit($event['text'], 160) }}</p>
                            @endif
                            @if(!empty($event['url']))
                                <a href="{{ $event['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-block mt-2 text-xs font-bold text-brand-primary">Open event</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500 font-medium py-10 text-center">No events found for this lookup.</p>
    @endif
</div>
