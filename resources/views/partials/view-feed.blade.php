@php
    $posts = $posts ?? ($result['posts'] ?? []);
    $handle = $result['handle'] ?? '';
    $title = $result['title'] ?? '';
    $avatar = $result['avatar'] ?? '';
@endphp
<div class="p-5 sm:p-8">
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="rounded-full bg-brand-primary/10 text-brand-primary px-3 py-1 text-[10px] font-bold uppercase tracking-wider">{{ $result['tool_name'] }}</span>
        @if($title || $handle)
            <p class="text-sm font-extrabold text-slate-900">{{ $title ?: $handle }}</p>
        @endif
        @if($result['verified'])
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-500 text-white" title="Verified"><i class="ph ph-check text-xs font-bold"></i></span>
        @endif
        @if($result['url'])
            <a href="{{ $result['url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-brand-primary">Open profile</a>
        @endif
    </div>

    @if(count($posts))
        <div class="space-y-3">
            @foreach($posts as $post)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        @php $postAvatar = ($post['author_avatar'] ?? '') ?: $avatar; @endphp
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
                        @if(!empty($post['quotes']))<span class="inline-flex items-center gap-1"><i class="ph ph-quotes text-slate-400"></i> {{ number_format($post['quotes']) }}</span>@endif
                        @if(!empty($post['comments']))<span class="inline-flex items-center gap-1"><i class="ph ph-chat-circle text-slate-400"></i> {{ number_format($post['comments']) }}</span>@endif
                        @if(!empty($post['views']))<span class="inline-flex items-center gap-1"><i class="ph ph-chart-bar text-slate-400"></i> {{ number_format($post['views']) }}</span>@endif
                        @if(!empty($post['url']))<a href="{{ $post['url'] }}" target="_blank" rel="noopener noreferrer" class="text-brand-primary">Open</a>@endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500 font-medium py-10 text-center">No posts found for this lookup.</p>
    @endif
</div>
