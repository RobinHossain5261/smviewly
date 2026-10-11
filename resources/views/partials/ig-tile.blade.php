@php
    $file = ($item['url'] ?? '') ?: ($item['thumb'] ?? '');
    $thumb = $item['thumb'] ?? '';
    $isVideo = ! empty($item['is_video']);
    $isCarousel = ! empty($item['is_carousel']);
    $likes = (int) ($item['likes'] ?? 0);
    $comments = (int) ($item['comments'] ?? 0);
@endphp
<div class="relative group aspect-square overflow-hidden bg-slate-100 cursor-pointer"
     onclick="openViewerMedia(this)"
     data-media-url="{{ $file ?: $thumb }}"
     data-poster="{{ $thumb }}"
     data-video="{{ $isVideo ? '1' : '0' }}"
     data-permalink="{{ $item['permalink'] ?? '' }}">
    @if($thumb)
        <img src="{{ $img($thumb) }}" alt="" loading="lazy" class="h-full w-full object-cover group-hover:scale-[1.03] transition-transform duration-300">
    @elseif(!$isVideo && $file)
        <img src="{{ $img($file) }}" alt="" loading="lazy" class="h-full w-full object-cover group-hover:scale-[1.03] transition-transform duration-300">
    @elseif($isVideo)
        <div class="h-full w-full bg-slate-900 flex items-center justify-center text-white/80"><i class="ph-fill ph-play text-3xl"></i></div>
    @endif

    @if($isVideo || $isCarousel)
        <span class="absolute top-1.5 right-1.5 text-white drop-shadow-md">
            <i class="ph-fill {{ $isVideo ? 'ph-play' : 'ph-images' }} text-sm"></i>
        </span>
    @endif

    @if($likes > 0 || $comments > 0)
        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-3 text-white">
            <div class="flex items-center gap-4">
                @if($likes > 0)
                    <span class="flex items-center gap-1 text-sm font-bold"><i class="ph-fill ph-heart"></i> {{ number_format($likes) }}</span>
                @endif
                @if($comments > 0)
                    <span class="flex items-center gap-1 text-sm font-bold"><i class="ph-fill ph-chat-circle"></i> {{ number_format($comments) }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($file)
                    <button type="button" onclick="openViewerMedia(this)"
                            data-media-url="{{ $file ?: $thumb }}"
                            data-poster="{{ $thumb }}"
                            data-video="{{ $isVideo ? '1' : '0' }}"
                            data-permalink="{{ $item['permalink'] ?? '' }}"
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-900 shadow hover:bg-slate-100" title="View"><i class="ph ph-eye text-sm"></i></button>
                @endif
                @if($file)
                    <a href="{{ route('viewer.download', ['url' => $file]) }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-900 shadow hover:bg-slate-100" title="Download"><i class="ph ph-download-simple text-sm"></i></a>
                @endif
            </div>
        </div>
    @else
        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
            @if($file)
                <button type="button" onclick="openViewerMedia(this)"
                        data-media-url="{{ $file ?: $thumb }}"
                        data-poster="{{ $thumb }}"
                        data-video="{{ $isVideo ? '1' : '0' }}"
                        data-permalink="{{ $item['permalink'] ?? '' }}"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-900 shadow hover:bg-slate-100" title="View"><i class="ph ph-eye text-sm"></i></button>
            @endif
            @if($file)
                <a href="{{ route('viewer.download', ['url' => $file]) }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-900 shadow hover:bg-slate-100" title="Download"><i class="ph ph-download-simple text-sm"></i></a>
            @endif
        </div>
    @endif
</div>
