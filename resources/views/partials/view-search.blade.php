@php
    $posts = $result['posts'] ?? [];
    $people = $result['people'] ?? [];
    $items = $result['items'] ?? [];
    $query = $result['query'] ?? '';
@endphp
<div class="p-5 sm:p-8">
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="rounded-full bg-brand-primary/10 text-brand-primary px-3 py-1 text-[10px] font-bold uppercase tracking-wider">{{ $result['tool_name'] }}</span>
        <h2 class="text-lg font-extrabold text-slate-900">Search results</h2>
        @if($query !== '')
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">“{{ $query }}”</span>
        @endif
    </div>

    @if(!count($posts) && !count($people) && !count($items))
        <p class="text-sm text-slate-500 font-medium py-10 text-center">No results found for this search.</p>
    @endif

    @if(count($posts))
        <h3 class="text-sm font-extrabold text-slate-900 mb-3 mt-4">Posts ({{ count($posts) }})</h3>
        <div class="space-y-3">
            @foreach($posts as $post)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        @php $postAvatar = ($post['author_avatar'] ?? '') ?: ($result['avatar'] ?? ''); @endphp
                        @if($postAvatar)
                            <img src="{{ $img($postAvatar) }}" alt="" class="h-6 w-6 rounded-full object-cover bg-slate-100">
                        @endif
                        @if(!empty($post['author']))
                            <p class="text-xs font-extrabold text-slate-800">{{ $post['author'] }}</p>
                        @endif
                        @if(!empty($post['time']) && $post['time'] !== '0')
                            <span class="text-[11px] text-slate-400 font-semibold">· {{ $post['time'] }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-800 font-medium whitespace-pre-line break-words">{{ $post['text'] }}</p>
                    @if(count($post['media'] ?? []))
                        <div class="mt-3 grid gap-1.5 {{ count($post['media']) > 1 ? 'grid-cols-2' : 'grid-cols-1' }}">
                            @foreach($post['media'] as $pm)
                                @include('partials.media-tile', ['item' => $pm, 'showDownload' => true, 'downloadLabel' => 'Save'])
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-[11px] font-bold text-slate-400">
                        @if(!empty($post['likes']))<span class="inline-flex items-center gap-1"><i class="ph ph-heart text-slate-400"></i> {{ number_format($post['likes']) }}</span>@endif
                        @if(!empty($post['retweets']))<span class="inline-flex items-center gap-1"><i class="ph ph-repeat text-slate-400"></i> {{ number_format($post['retweets']) }}</span>@endif
                        @if(!empty($post['comments']))<span class="inline-flex items-center gap-1"><i class="ph ph-chat-circle text-slate-400"></i> {{ number_format($post['comments']) }}</span>@endif
                        @if(!empty($post['url']))<a href="{{ $post['url'] }}" target="_blank" rel="noopener noreferrer" class="text-brand-primary">Open</a>@endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(count($people))
        <h3 class="text-sm font-extrabold text-slate-900 mb-3 mt-6">People ({{ count($people) }})</h3>
        <div class="divide-y divide-slate-100 border border-slate-100 rounded-2xl bg-white">
            @foreach($people as $person)
                <div class="flex items-center gap-3 p-3">
                    @if(!empty($person['avatar']))
                        <img src="{{ $img($person['avatar']) }}" alt="" class="h-10 w-10 rounded-full object-cover bg-slate-100">
                    @else
                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center"><i class="ph ph-user text-slate-400"></i></div>
                    @endif
                    <div class="min-w-0 flex-grow">
                        <p class="text-sm font-bold text-slate-900 truncate">{{ $person['username'] }}</p>
                        @if(!empty($person['full_name']) && $person['full_name'] !== $person['username'])
                            <p class="text-xs text-slate-500 truncate">{{ $person['full_name'] }}</p>
                        @endif
                    </div>
                    @if(!empty($person['url']))
                        <a href="{{ $person['url'] }}" target="_blank" rel="noopener noreferrer" class="shrink-0 text-xs font-bold text-brand-primary">Open</a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(count($items))
        <h3 class="text-sm font-extrabold text-slate-900 mb-3 mt-6">More ({{ count($items) }})</h3>
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
    @endif
</div>
