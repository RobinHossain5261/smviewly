@php
    $people = $people ?? ($result['people'] ?? []);
@endphp
<div class="p-5 sm:p-8">
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="rounded-full bg-brand-primary/10 text-brand-primary px-3 py-1 text-[10px] font-bold uppercase tracking-wider">{{ $result['tool_name'] }}</span>
        @if($result['title'] || $result['handle'])
            <p class="text-sm font-extrabold text-slate-900">{{ $result['title'] ?: $result['handle'] }}</p>
        @endif
        @if($result['url'])
            <a href="{{ $result['url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-brand-primary">Open profile</a>
        @endif
    </div>

    @if(count($people))
        <div class="divide-y divide-slate-100 border border-slate-100 rounded-2xl bg-white">
            @foreach($people as $person)
                <div class="flex items-center gap-3 p-3">
                    @if(!empty($person['avatar']))
                        <img src="{{ $img($person['avatar']) }}" alt="" class="h-11 w-11 rounded-full object-cover bg-slate-100">
                    @else
                        <div class="h-11 w-11 rounded-full bg-slate-100 flex items-center justify-center"><i class="ph ph-user text-slate-400"></i></div>
                    @endif
                    <div class="min-w-0 flex-grow">
                        <p class="text-sm font-bold text-slate-900 truncate">{{ $person['username'] }}</p>
                        @if(!empty($person['full_name']) && $person['full_name'] !== $person['username'])
                            <p class="text-xs text-slate-500 truncate">{{ $person['full_name'] }}</p>
                        @endif
                    </div>
                    @if(!empty($person['url']))
                        <a href="{{ $person['url'] }}" target="_blank" rel="noopener noreferrer" class="shrink-0 inline-flex items-center gap-1 rounded-lg bg-brand-primary/10 px-3 py-1.5 text-xs font-bold text-brand-primary hover:bg-brand-primary/20">Open</a>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500 font-medium py-10 text-center">No people found for this lookup.</p>
    @endif
</div>
