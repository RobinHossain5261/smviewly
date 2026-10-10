@php
    $file = ($item['url'] ?? '') ?: ($item['thumb'] ?? '');
    $poster = $item['thumb'] ?? '';
    $isVideo = ! empty($item['is_video']);
    $previewSrc = $poster ?: ((! $isVideo) ? $file : '');
@endphp
<div class="relative group rounded-2xl overflow-hidden bg-slate-100 aspect-square">
    @if($previewSrc)
        <img src="{{ $img($previewSrc) }}" alt="" class="h-full w-full object-cover">
    @elseif($isVideo)
        <div class="h-full w-full bg-slate-900"></div>
    @endif

    <button type="button"
            class="absolute inset-0 flex items-center justify-center cursor-pointer"
            data-media-url="{{ $isVideo ? $file : $previewSrc }}"
            data-poster="{{ $poster }}"
            data-video="{{ $isVideo ? '1' : '0' }}"
            data-permalink="{{ $item['permalink'] ?? '' }}"
            onclick="openViewerMedia(this)">
        @if($isVideo)
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-black/65 text-white shadow-lg group-hover:scale-110 transition-transform">
                <i class="ph-fill ph-play text-xl ml-0.5"></i>
            </span>
        @endif
    </button>

    @if($isVideo)
        <span class="absolute top-2 right-2 rounded-full bg-black/70 text-white text-[10px] font-bold px-2 py-0.5 pointer-events-none">VIDEO</span>
    @endif

    @if(!empty($showDownload) && ($file || $previewSrc))
        <div class="absolute inset-x-0 bottom-0 p-2 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-between gap-2 pointer-events-none">
            <span class="text-[10px] text-white font-bold truncate">{{ !empty($item['likes']) ? number_format($item['likes']).' likes' : '' }}</span>
            <div class="flex items-center gap-1.5 pointer-events-auto">
                <button type="button" onclick="openViewerMedia(this)"
                        data-media-url="{{ $isVideo ? $file : $previewSrc }}"
                        data-poster="{{ $poster }}"
                        data-video="{{ $isVideo ? '1' : '0' }}"
                        data-permalink="{{ $item['permalink'] ?? '' }}"
                        class="rounded-lg bg-white/90 text-slate-900 text-[10px] font-bold px-2 py-1 inline-flex items-center gap-1" title="View">
                    <i class="ph ph-eye text-xs"></i> View
                </button>
                <a href="{{ $dl($file ?: $previewSrc) }}" class="rounded-lg bg-white text-slate-900 text-[10px] font-bold px-2 py-1">{{ $downloadLabel ?? 'Save' }}</a>
            </div>
        </div>
    @endif
</div>
