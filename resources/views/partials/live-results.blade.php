@php
    $dl = fn ($url) => $url ? route('viewer.download', ['url' => $url]) : '#';
    $img = fn ($url) => $url ? route('viewer.media', ['url' => $url]) : '';
    $intent = $result['intent'] ?? 'view';
    $focus = $result['focus'] ?? 'media';
    $isDownload = $intent === 'download';
    $isAvatar = $focus === 'avatar';
    $downloadLabel = $result['download_label'] ?? 'Download';
    $featured = $result['media'][0] ?? null;
    $rest = array_slice($result['media'] ?? [], 1);
    $fileOf = fn ($item) => ($item['url'] ?? '') ?: ($item['thumb'] ?? '');
    $openUrl = $result['url'] ?: (($featured['permalink'] ?? '') ?: '');
    $photos = $result['photos'] ?? array_values(array_filter($result['media'] ?? [], fn ($item) => empty($item['is_video'])));
    $videos = $result['videos'] ?? array_values(array_filter($result['media'] ?? [], fn ($item) => ! empty($item['is_video']) && empty($item['is_reel'])));
    $reels = $result['reels'] ?? array_values(array_filter($result['media'] ?? [], fn ($item) => ! empty($item['is_reel'])));
    $stories = $result['stories'] ?? [];
    $counts = $result['counts'] ?? [
        'photos' => count($photos),
        'videos' => count($videos),
        'reels' => count($reels),
        'posts' => count($result['posts'] ?? []),
    ];
    $isProfile = ! $isDownload && ! $isAvatar;
    $profileUrl = $openUrl;
    if ($profileUrl === '' && ! empty($result['handle'])) {
        $profileUrl = match ($result['platform'] ?? '') {
            'facebook' => 'https://www.facebook.com/'.$result['handle'],
            'twitter' => 'https://x.com/'.$result['handle'],
            'threads' => 'https://www.threads.net/@'.$result['handle'],
            'linkedin' => 'https://www.linkedin.com/in/'.$result['handle'],
            default => '',
        };
    }
    $defaultTab = match (true) {
        count($photos) > 0 => 'photos',
        count($result['posts'] ?? []) > 0 => 'posts',
        count($videos) > 0 => 'videos',
        count($reels) > 0 => 'reels',
        count($stories) > 0 => 'stories',
        count($result['highlights'] ?? []) > 0 => 'highlights',
        count($result['people'] ?? []) > 0 => 'people',
        count($result['items'] ?? []) > 0 => 'items',
        default => 'photos',
    };

    if (($result['platform'] ?? '') === 'twitter' && count($result['photos'] ?? []) > 0) {
        $defaultTab = 'photos';
    }

    if (($result['platform'] ?? '') === 'linkedin' && count($result['posts'] ?? []) > 0) {
        $defaultTab = 'posts';
    }

    $isFacebook = ($result['platform'] ?? '') === 'facebook' && $isProfile;

    $fbTab = match (true) {
        count($photos) > 0 => 'photos',
        count($videos) > 0 => 'videos',
        count($reels) > 0 => 'reels',
        count($result['posts'] ?? []) > 0 => 'posts',
        count($result['people'] ?? []) > 0 => 'people',
        count($result['detail'] ?? []) > 0 => 'about',
        default => 'photos',
    };


    $view = $result['view'] ?? 'profile';
    $hasDedicatedView = ! $isFacebook && in_array($view, ['feed', 'people', 'trends', 'jobs', 'events', 'courses', 'search', 'list', 'media'], true);
@endphp

<div data-result-root {{ $isFacebook ? 'data-fb-root' : '' }} class="mx-auto w-full max-w-5xl min-w-0 overflow-hidden rounded-3xl border border-slate-200 bg-white text-left {{ $isProfile ? 'p-0' : 'p-5 sm:p-8' }}">
    @if($isAvatar && !empty($result['avatar']))
        <div class="flex flex-col items-center text-center max-w-md mx-auto">
            <span class="rounded-full bg-brand-primary/10 text-brand-primary px-3 py-1 text-[10px] font-bold uppercase tracking-wider mb-4">{{ $result['tool_name'] }}</span>
            <div class="relative group/avatar cursor-pointer" onclick="openViewerMedia(this)" data-media-url="{{ $result['avatar'] }}" data-poster="{{ $result['avatar'] }}" data-video="0">
                <img src="{{ $img($result['avatar']) }}" alt="" class="h-40 w-40 sm:h-52 sm:w-52 rounded-full object-cover border-4 border-white bg-slate-100 shadow-lg">
                <div class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover/avatar:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-slate-900 shadow-lg"><i class="ph ph-eye text-xl"></i></span>
                </div>
            </div>
            <h2 class="mt-5 text-2xl font-extrabold text-slate-900">{{ $result['title'] ?: '@'.$result['handle'] }}</h2>
            @if($result['handle'] && $result['handle'] !== $result['title'])
                <p class="text-sm font-bold text-slate-500">{{ '@'.$result['handle'] }}</p>
            @endif
            <a href="{{ $dl($result['avatar']) }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-brand-primary px-6 py-3 text-sm font-extrabold text-white hover:opacity-95">
                <i class="ph ph-download-simple text-base"></i>
                {{ $downloadLabel }}
            </a>
        </div>

    @elseif($isDownload && $featured)
        <div class="flex flex-wrap items-center gap-2 mb-5">
            @if($result['handle'])
                <p class="text-sm font-extrabold text-slate-900">{{ '@'.$result['handle'] }}</p>
            @endif
            @if($result['verified'])
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-500 text-white" title="Verified"><i class="ph ph-check text-xs font-bold"></i></span>
            @endif
            <span class="rounded-full bg-brand-primary/10 text-brand-primary px-3 py-1 text-[10px] font-bold uppercase tracking-wider">{{ $result['tool_name'] }}</span>
        </div>

        <div class="mx-auto max-w-lg">
            <div class="relative overflow-hidden rounded-2xl bg-black">
                @if($featured['is_video'] && $featured['url'])
                    <video controls playsinline preload="metadata" class="w-full max-h-[70vh] bg-black"
                           @if($featured['thumb']) poster="{{ $img($featured['thumb']) }}" @endif>
                        <source src="{{ $img($featured['url']) }}" type="video/mp4">
                    </video>
                @elseif($featured['thumb'] || $featured['url'])
                    <img src="{{ $img($featured['thumb'] ?: $featured['url']) }}" alt="" class="w-full max-h-[70vh] object-contain bg-slate-950">
                @endif
            </div>

            @if($result['caption'] || $featured['caption'])
                <p class="mt-4 text-sm text-slate-700 font-medium whitespace-pre-line break-words">{{ $result['caption'] ?: $featured['caption'] }}</p>
            @endif

            @if(count($result['stats']))
                <div class="mt-4 flex flex-wrap gap-3">
                    @foreach($result['stats'] as $stat)
                        <div class="rounded-2xl bg-[#f8fafc] py-2.5 px-4 text-center min-w-[88px]">
                            <p class="text-lg font-extrabold text-slate-900">{{ is_numeric($stat['value']) ? number_format($stat['value']) : $stat['value'] }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-5 flex flex-wrap items-center gap-2">
                @if($fileOf($featured))
                    <a href="{{ $dl($fileOf($featured)) }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-primary px-5 py-3 text-sm font-extrabold text-white hover:opacity-95">
                        <i class="ph ph-download-simple text-base"></i>
                        {{ $downloadLabel }}
                    </a>
                @endif
                @if($openUrl)
                    <a href="{{ $openUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-slate-100 border border-slate-200 px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-200">Open source</a>
                @endif
            </div>
        </div>

        @if(count($rest))
            <h3 class="text-sm font-extrabold text-slate-900 mt-8 mb-3">More to download ({{ count($rest) }})</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                @foreach($rest as $item)
                    @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => $downloadLabel])
                @endforeach
            </div>
        @endif

    @elseif($hasDedicatedView)
        @php $viewPartial = match ($view) {
            'feed' => 'partials.view-feed',
            'people' => 'partials.view-people',
            'trends' => 'partials.view-trends',
            'jobs' => 'partials.view-jobs',
            'events' => 'partials.view-events',
            'courses' => 'partials.view-courses',
            'search' => 'partials.view-search',
            'list' => 'partials.view-list',
            default => 'partials.view-feed',
        }; @endphp
        @if($view === 'media')
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
                @if(count($result['media'] ?? []))
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                        @foreach($result['media'] as $item)
                            @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => 'Save'])
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 font-medium py-10 text-center">No media found for this lookup.</p>
                @endif
            </div>
        @else
            @include($viewPartial)
        @endif

    @elseif($isFacebook)
        {{-- Facebook-native profile: cover, overlapping avatar, blue actions, tabs, posts feed --}}
        <div class="relative">
            {{-- Cover photo: full-bleed like real Facebook --}}
            <div class="relative h-40 sm:h-64 lg:h-72 overflow-hidden bg-[#1877F2]">
                @if(!empty($result['cover']))
                    <img src="{{ $img($result['cover']) }}" alt="Cover photo" loading="lazy"
                         class="absolute inset-0 h-full w-full object-cover object-top">
                    <div class="absolute bottom-3 right-3 z-20 flex items-center gap-2">
                        <button type="button" onclick="openViewerMedia(this)" data-media-url="{{ $result['cover'] }}" data-poster="{{ $result['cover'] }}" data-video="0"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-white/95 px-3 py-1.5 text-xs font-bold text-slate-800 shadow hover:bg-white transition-colors">
                            <i class="ph ph-eye text-sm"></i> View
                        </button>
                        <a href="{{ $dl($result['cover']) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white/95 px-3 py-1.5 text-xs font-bold text-slate-800 shadow hover:bg-white transition-colors">
                            <i class="ph ph-download-simple text-sm"></i> Download
                        </a>
                    </div>
                @endif
            </div>

            {{-- Avatar + identity --}}
            <div class="px-4 sm:px-8 pb-4">
                <div class="flex flex-col sm:flex-row sm:items-end gap-3 -mt-16 sm:-mt-24">
                    <div class="shrink-0 mx-auto sm:mx-0 relative z-10">
                        @if(!empty($result['avatar']))
                            <div class="relative group/avatar">
                                <img src="{{ $img($result['avatar']) }}" alt="" class="h-24 w-24 sm:h-36 sm:w-36 rounded-2xl object-cover border-4 border-white bg-slate-100 shadow-sm">
                                <div class="absolute inset-0 rounded-2xl bg-black/50 opacity-0 group-hover/avatar:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <button type="button" onclick="openViewerMedia(this)" data-media-url="{{ $result['avatar'] }}" data-poster="{{ $result['avatar'] }}" data-video="0"
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-800 hover:bg-slate-100" title="View">
                                        <i class="ph ph-eye text-base"></i>
                                    </button>
                                    <a href="{{ $dl($result['avatar']) }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-800 hover:bg-slate-100" title="Download">
                                        <i class="ph ph-download-simple text-base"></i>
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="h-24 w-24 sm:h-36 sm:w-36 rounded-2xl bg-[#1877F2] border-4 border-white flex items-center justify-center">
                                <i class="ph ph-user text-white text-4xl sm:text-5xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0 text-center sm:text-left pb-1">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-2 gap-y-1.5">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-950 tracking-tight break-all">{{ $result['title'] ?: $result['handle'] }}</h2>
                            @if($result['verified'])
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#1877F2] text-white" title="Verified"><i class="ph-fill ph-check text-xs font-bold"></i></span>
                            @endif
                        </div>
                        @if($result['handle'] && $result['handle'] !== $result['title'])
                            <p class="text-sm font-medium text-slate-500">{{ '@'.$result['handle'] }}</p>
                        @endif
                        @if(!empty($result['category']))
                            <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ $result['category'] }}</p>
                        @endif
                    </div>
                </div>

                @if($result['bio'])
                    <p class="text-sm text-slate-700 whitespace-pre-line break-words mt-3 leading-relaxed">{{ $result['bio'] }}</p>
                @endif

                {{-- Actions --}}
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-4 border-t border-slate-100 pt-4">
                    @if($profileUrl)
                        <a href="{{ $profileUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg bg-[#1877F2] px-5 py-2 text-sm font-bold text-white hover:bg-[#166fe5] transition-colors">
                            <i class="ph-fill ph-user-plus text-base"></i> Follow
                        </a>
                    @endif
                    <button type="button" onclick="shareLookupResult({{ json_encode($result['title'] ?: ($result['handle'] ?? 'smviewly')) }}, {{ json_encode($profileUrl ?: url()->current()) }})" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-5 py-2 text-sm font-bold text-slate-800 hover:bg-slate-200 transition-colors">
                        <i class="ph ph-share-network text-base"></i> Share
                    </button>
                </div>
            </div>

            {{-- Stats strip --}}
            @php
                $fbStats = [];
                $fbMap = ['posts' => 'Posts', 'followers' => 'Followers', 'following' => 'Following', 'likes' => 'Likes', 'talking_about' => 'Talking about this', 'employees' => 'Employees'];
                foreach ($result['stats'] as $stat) {
                    $key = strtolower($stat['label'] ?? '');
                    if (isset($fbMap[$key])) { $fbStats[$fbMap[$key]] = $stat['value']; }
                }
                $fbStats['Posts'] = $fbStats['Posts'] ?? (int) ($result['counts']['posts'] ?? 0);
            @endphp
            @if(count($fbStats))
                <div class="mx-4 sm:mx-8 mb-2 rounded-xl bg-slate-50 border border-slate-100 px-4 py-3">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-8 gap-y-2">
                        @foreach($fbStats as $label => $value)
                            <div class="text-center sm:text-left">
                                <span class="block text-base sm:text-lg font-bold text-slate-950">{{ is_numeric($value) ? number_format((int) $value) : ($value ?: '—') }}</span>
                                <span class="block text-xs text-slate-500 font-medium">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Tabs --}}
            <div class="px-4 sm:px-8">
                <div class="flex items-center justify-center sm:justify-start gap-1 border-t border-slate-200 overflow-x-auto no-scrollbar">
                    @foreach([
                        ['photos', 'Photos', count($photos)],
                        ['videos', 'Videos', count($videos)],
                        ['reels', 'Reels', count($reels)],
                        ['posts', 'Posts', count($result['posts'] ?? [])],
                        ['people', 'Followers', (int) ($result['counts']['followers'] ?? count($result['people'] ?? []))],
                        ['about', 'About', count($result['detail'] ?? [])],
                    ] as $tab)
                        @if($tab[0] === 'posts' || $tab[0] === 'photos' || $tab[0] === 'videos' || $tab[0] === 'about' || $tab[2] > 0)
                            <button type="button" data-result-tab="{{ $tab[0] }}" onclick="switchResultTab(this, '{{ $tab[0] }}')"
                                class="result-tab shrink-0 inline-flex items-center gap-1.5 px-4 py-3 text-sm font-semibold border-b-2 -mb-px {{ $fbTab === $tab[0] ? 'border-[#1877F2] text-[#1877F2]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                                {{ $tab[1] }}
                                @if($tab[2] > 0)<span class="text-xs font-bold text-slate-400">{{ $tab[2] }}</span>@endif
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Panels --}}
            <div class="px-4 sm:px-8 py-5">
                {{-- Posts feed --}}
                <div data-tab-panel="posts" class="{{ $fbTab === 'posts' ? '' : 'hidden' }} space-y-4">
                    @if(count($result['posts'] ?? []))
                        @foreach($result['posts'] as $post)
                            @php $postMedia = $post['media'] ?? []; @endphp
                            <div class="rounded-2xl border border-slate-100 bg-white p-4">
                                <div class="flex items-center gap-3">
                                    @php $postAvatar = ($post['author_avatar'] ?? '') ?: ($result['avatar'] ?? ''); @endphp
                                    @if($postAvatar)
                                        <img src="{{ $img($postAvatar) }}" alt="" class="h-10 w-10 rounded-full object-cover bg-slate-100">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-[#1877F2]"></div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-900 truncate">
                                            {{ $post['author'] ?: ($result['title'] ?: $result['handle']) }}
                                        </p>
                                        <p class="text-xs text-slate-400">
                                            @if(!empty($post['is_share']))
                                                <span class="inline-flex items-center gap-1"><i class="ph ph-arrow-elbow-down-right"></i> shared {{ ($post['time'] ?? '') !== '' && $post['time'] !== '0' ? '· '.$post['time'] : '' }}</span>
                                            @else
                                                {{ ($post['time'] ?? '') !== '' && $post['time'] !== '0' ? $post['time'] : 'Post' }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                @if($post['text'])
                                    <p class="text-sm text-slate-800 whitespace-pre-line break-words mt-3 leading-relaxed">{{ $post['text'] }}</p>
                                @endif
                                @if(count($postMedia))
                                    <div class="mt-3 grid gap-1 {{ count($postMedia) > 1 ? 'grid-cols-2' : 'grid-cols-1' }}">
                                        @foreach($postMedia as $pm)
                                            @include('partials.media-tile', ['item' => $pm, 'showDownload' => false])
                                        @endforeach
                                    </div>
                                @endif
                                @if(($post['likes'] ?? 0) || ($post['comments'] ?? 0))
                                    <div class="mt-3 flex items-center gap-4 text-xs font-semibold text-slate-500 border-t border-slate-100 pt-3">
                                        @if($post['likes'])
                                            <span class="inline-flex items-center gap-1.5"><span class="flex h-4 w-4 items-center justify-center rounded-full bg-[#1877F2] text-white"><i class="ph-fill ph-thumbs-up text-[9px]"></i></span> {{ number_format($post['likes']) }}</span>
                                        @endif
                                        @if($post['comments'])
                                            <span class="inline-flex items-center gap-1"><i class="ph ph-chat-circle-dots text-slate-400"></i> {{ number_format($post['comments']) }} comments</span>
                                        @endif
                                    </div>
                                @endif
                                @if(!empty($post['url']))
                                    <a href="{{ $post['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-bold text-[#1877F2] hover:underline mt-2">Open post <i class="ph ph-arrow-up-right"></i></a>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-slate-500 font-medium py-8 text-center">No posts found for this profile.</p>
                    @endif
                </div>

                {{-- Photos grid --}}
                <div data-tab-panel="photos" class="{{ $fbTab === 'photos' ? '' : 'hidden' }}">
                    @if(count($photos))
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                            @foreach($photos as $item)
                                @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => 'Save'])
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500 font-medium py-8 text-center">No photos in this lookup.</p>
                    @endif
                </div>

                {{-- Videos grid --}}
                <div data-tab-panel="videos" class="{{ $fbTab === 'videos' ? '' : 'hidden' }}">
                    @if(count($videos))
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                            @foreach($videos as $item)
                                @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => 'Save'])
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500 font-medium py-8 text-center">No videos in this lookup.</p>
                    @endif
                </div>

                {{-- Reels grid --}}
                <div data-tab-panel="reels" class="{{ $fbTab === 'reels' ? '' : 'hidden' }}">
                    @if(count($reels))
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                            @foreach($reels as $item)
                                @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => 'Save'])
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500 font-medium py-8 text-center">No reels in this lookup.</p>
                    @endif
                </div>

                {{-- Followers / People --}}
                <div data-tab-panel="people" class="{{ $fbTab === 'people' ? '' : 'hidden' }}">
                    @if(count($result['people'] ?? []))
                        <div class="divide-y divide-slate-100 border border-slate-100 rounded-2xl bg-white">
                            @foreach($result['people'] as $person)
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
                                        <a href="{{ $person['url'] }}" target="_blank" rel="noopener noreferrer" class="shrink-0 inline-flex items-center gap-1 rounded-lg bg-[#1877F2]/10 px-3 py-1.5 text-xs font-bold text-[#1877F2] hover:bg-[#1877F2]/20">Follow</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500 font-medium py-8 text-center">No followers found for this profile.</p>
                    @endif
                </div>

                {{-- About --}}
                <div data-tab-panel="about" class="{{ $fbTab === 'about' ? '' : 'hidden' }}">
                    @if(count($result['detail'] ?? []))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($result['detail'] as $row)
                                <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $row['label'] }}</p>
                                    <p class="text-xs font-semibold text-slate-800 break-all">{{ $row['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500 font-medium py-8 text-center">No extra details found for this profile.</p>
                    @endif
                </div>
            </div>
        </div>

    @elseif($isProfile)
        {{-- Profile layout matching reference: cover, avatar, counts, actions, tabs, galleries --}}
        <div class="relative h-36 sm:h-48 bg-brand-primary">
            @if(!empty($result['cover']))
                <img src="{{ $img($result['cover']) }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute bottom-3 right-3 z-10 flex items-center gap-2">
                    <button type="button" onclick="openViewerMedia(this)" data-media-url="{{ $result['cover'] }}" data-poster="{{ $result['cover'] }}" data-video="0"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-white/95 px-3 py-1.5 text-xs font-bold text-slate-800 shadow hover:bg-white transition-colors">
                        <i class="ph ph-eye text-sm"></i> View
                    </button>
                    <a href="{{ $dl($result['cover']) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white/95 px-3 py-1.5 text-xs font-bold text-slate-800 shadow hover:bg-white transition-colors">
                        <i class="ph ph-download-simple text-sm"></i> Download
                    </a>
                </div>
            @endif
        </div>

        <div class="px-5 sm:px-8 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4 -mt-12 sm:-mt-14 mb-4">
                <div class="relative shrink-0">
                    @if(!empty($result['avatar']))
                        <div class="relative group/avatar">
                            <img src="{{ $img($result['avatar']) }}" alt="" class="h-24 w-24 sm:h-28 sm:w-28 rounded-full object-cover border-4 border-white bg-slate-100">
                            <div class="absolute inset-0 rounded-full bg-black/50 opacity-0 group-hover/avatar:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <button type="button" onclick="openViewerMedia(this)" data-media-url="{{ $result['avatar'] }}" data-poster="{{ $result['avatar'] }}" data-video="0"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-800 hover:bg-slate-100" title="View">
                                    <i class="ph ph-eye text-sm"></i>
                                </button>
                                <a href="{{ $dl($result['avatar']) }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-800 hover:bg-slate-100" title="Download">
                                    <i class="ph ph-download-simple text-sm"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="h-24 w-24 sm:h-28 sm:w-28 rounded-full bg-brand-primary border-4 border-white flex items-center justify-center">
                            <i class="ph ph-user text-white text-3xl"></i>
                        </div>
                    @endif
                    <span class="absolute bottom-1 right-1 h-4 w-4 rounded-full border-2 border-white {{ $result['private'] ? 'bg-amber-400' : 'bg-emerald-500' }}" title="{{ $result['private'] ? 'Private' : 'Public' }}"></span>
                </div>
                <div class="flex-1 min-w-0 pb-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $result['title'] ?: '@'.$result['handle'] }}</h2>
                        @if($result['verified'])
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-500 text-white" title="Verified"><i class="ph ph-check text-xs font-bold"></i></span>
                        @endif
                    </div>
                    @if($result['handle'] && $result['handle'] !== $result['title'])
                        <p class="text-sm font-bold text-slate-500">{{ '@'.$result['handle'] }}</p>
                    @endif
                    @if(!empty($result['category']))
                        <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $result['category'] }}</p>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2 mb-4">
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ number_format($counts['photos']) }} Photos</span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ number_format($counts['videos']) }} Videos</span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ number_format($counts['reels']) }} Reels</span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ number_format($counts['posts']) }} Posts</span>
                @foreach($result['stats'] as $stat)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ is_numeric($stat['value']) ? number_format($stat['value']) : $stat['value'] }} {{ $stat['label'] }}</span>
                @endforeach
            </div>

            @if($result['private'])
                <p class="text-xs font-bold text-amber-600 mb-3">This account is marked private — public data may be limited.</p>
            @endif
            @if($result['bio'])
                <p class="text-sm text-slate-600 font-medium whitespace-pre-line break-words mb-3">{{ $result['bio'] }}</p>
            @endif
            @if($result['caption'] && $focus === 'media')
                <p class="text-sm text-slate-600 font-medium whitespace-pre-line break-words mb-3">{{ $result['caption'] }}</p>
            @endif
            @if(!empty($result['bio_links']))
                <div class="flex flex-col gap-1 mb-3">
                    @foreach($result['bio_links'] as $link)
                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-primary hover:underline break-all">
                            <i class="ph ph-link text-sm"></i> {{ $link['title'] }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-2 mb-5">
                @if($profileUrl)
                    <a href="{{ $profileUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-primary px-4 py-2.5 text-xs font-extrabold text-white hover:opacity-95">
                        <i class="ph ph-user-plus text-sm"></i> Follow
                    </a>
                @endif
                <button type="button" onclick="shareLookupResult({{ json_encode($result['title'] ?: ($result['handle'] ?? 'smviewly')) }}, {{ json_encode($profileUrl ?: url()->current()) }})" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 border border-slate-200 px-4 py-2.5 text-xs font-extrabold text-slate-700 hover:bg-slate-200">
                    <i class="ph ph-share-network text-sm"></i> Share
                </button>
                @if(!empty($result['avatar']))
                    <a href="{{ $dl($result['avatar']) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 border border-slate-200 px-4 py-2.5 text-xs font-extrabold text-slate-700 hover:bg-slate-200">
                        <i class="ph ph-export text-sm"></i> Export
                    </a>
                @endif
            </div>

            @if(count($result['detail']))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-5">
                    @foreach($result['detail'] as $row)
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $row['label'] }}</p>
                            <p class="text-xs font-semibold text-slate-800 break-all">{{ $row['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 mb-5">
                @foreach([
                    ['photos', 'Photos', 'ph-image', count($photos)],
                    ['posts', 'Posts', 'ph-article', count($result['posts'] ?? [])],
                    ['videos', 'Videos', 'ph-video', count($videos)],
                    ['reels', 'Reels', 'ph-film-strip', count($reels)],
                    ['stories', 'Stories', 'ph-circle', count($stories)],
                    ['highlights', 'Highlights', 'ph-star', count($result['highlights'] ?? [])],
                    ['people', 'People', 'ph-users', count($result['people'] ?? [])],
                    ['items', $focus === 'comments' ? 'Comments' : 'More', 'ph-list', count($result['items'] ?? [])],
                ] as $tab)
                    @if($tab[0] === 'photos' || $tab[0] === 'posts' || $tab[0] === 'videos' || $tab[0] === 'reels' || $tab[3] > 0)
                        <button type="button" data-result-tab="{{ $tab[0] }}" onclick="switchResultTab(this, '{{ $tab[0] }}')"
                            class="result-tab inline-flex items-center gap-1.5 px-3 py-3 text-xs font-bold border-b-2 -mb-px {{ $defaultTab === $tab[0] ? 'border-brand-primary text-brand-primary' : 'border-transparent text-slate-400 hover:text-slate-700' }}">
                            <i class="ph {{ $tab[2] }}"></i> {{ $tab[1] }}
                        </button>
                    @endif
                @endforeach
            </div>

            <div data-tab-panel="photos" class="{{ $defaultTab === 'photos' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 inline-flex items-center gap-2"><i class="ph ph-images"></i> Photo Gallery</h3>
                    <span class="text-xs font-bold text-slate-400">{{ count($photos) }} Photos</span>
                </div>
                @if(count($photos))
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                        @foreach($photos as $item)
                            @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => 'Save'])
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 font-medium py-8 text-center">No photos in this lookup.</p>
                @endif
            </div>

            <div data-tab-panel="posts" class="{{ $defaultTab === 'posts' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-extrabold text-slate-900">Posts</h3>
                    <span class="text-xs font-bold text-slate-400">{{ count($result['posts'] ?? []) }} Posts</span>
                </div>
                @if(count($result['posts'] ?? []))
                    <div class="space-y-3">
                        @foreach($result['posts'] as $post)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    @if(!empty($post['author_avatar']))
                                        <img src="{{ $img($post['author_avatar']) }}" alt="" class="h-6 w-6 rounded-full object-cover bg-slate-100">
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
                                    @if($post['url'])<a href="{{ $post['url'] }}" target="_blank" rel="noopener noreferrer" class="text-brand-primary">Open</a>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 font-medium py-8 text-center">No post captions in this lookup.</p>
                @endif
            </div>

            <div data-tab-panel="videos" class="{{ $defaultTab === 'videos' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-extrabold text-slate-900">Videos</h3>
                    <span class="text-xs font-bold text-slate-400">{{ count($videos) }} Videos</span>
                </div>
                @if(count($videos))
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                        @foreach($videos as $item)
                            @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => 'Save'])
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 font-medium py-8 text-center">No videos in this lookup.</p>
                @endif
            </div>

            <div data-tab-panel="reels" class="{{ $defaultTab === 'reels' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-extrabold text-slate-900">Reels</h3>
                    <span class="text-xs font-bold text-slate-400">{{ count($reels) }} Reels</span>
                </div>
                @if(count($reels))
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                        @foreach($reels as $item)
                            @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => 'Save'])
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 font-medium py-8 text-center">No reels in this lookup.</p>
                @endif
            </div>

            @if(count($stories))
            <div data-tab-panel="stories" class="{{ $defaultTab === 'stories' ? '' : 'hidden' }}">
                <h3 class="text-sm font-extrabold text-slate-900 mb-3">Stories ({{ count($stories) }})</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                    @foreach($stories as $item)
                        @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => 'Save'])
                    @endforeach
                </div>
            </div>
            @endif

            @if(count($result['highlights'] ?? []))
            <div data-tab-panel="highlights" class="{{ $defaultTab === 'highlights' ? '' : 'hidden' }}">
                <h3 class="text-sm font-extrabold text-slate-900 mb-3">Highlights ({{ count($result['highlights']) }})</h3>
                <div class="flex gap-4 overflow-x-auto pb-3">
                    @foreach($result['highlights'] as $hl)
                        <div class="shrink-0 w-24 text-center">
                            @if($hl['cover'])
                                <img src="{{ $img($hl['cover']) }}" alt="" class="h-20 w-20 mx-auto rounded-full object-cover border border-slate-200 bg-slate-100">
                            @endif
                            <p class="mt-2 text-[10px] font-bold text-slate-600 truncate">{{ $hl['title'] }}</p>
                            @if(!empty($hl['items']))
                                <p class="text-[10px] text-slate-400">{{ count($hl['items']) }} items</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                @foreach($result['highlights'] as $hl)
                    @if(!empty($hl['items']))
                        <h4 class="text-xs font-extrabold text-slate-800 mt-4 mb-2">{{ $hl['title'] }}</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3 mb-4">
                            @foreach($hl['items'] as $item)
                                @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => 'Save'])
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
            @endif

            @if(count($result['people'] ?? []))
            <div data-tab-panel="people" class="{{ $defaultTab === 'people' ? '' : 'hidden' }}">
                <h3 class="text-sm font-extrabold text-slate-900 mb-3">People ({{ count($result['people']) }})</h3>
                <div class="divide-y divide-slate-100 border border-slate-100 rounded-2xl">
                    @foreach($result['people'] as $person)
                        <div class="flex items-center gap-3 p-3">
                            @if($person['avatar'])
                                <img src="{{ $img($person['avatar']) }}" alt="" class="h-10 w-10 rounded-full object-cover bg-slate-100">
                            @else
                                <div class="h-10 w-10 rounded-full bg-slate-100"></div>
                            @endif
                            <div class="min-w-0 flex-grow">
                                <p class="text-xs font-extrabold text-slate-900">{{ $person['username'] }}</p>
                                <p class="text-[11px] text-slate-500">{{ $person['full_name'] }}</p>
                            </div>
                            @if(!empty($person['url']))
                                <a href="{{ $person['url'] }}" target="_blank" rel="noopener noreferrer" class="text-[11px] font-bold text-brand-primary">Open</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(count($result['items'] ?? []))
            <div data-tab-panel="items" class="{{ $defaultTab === 'items' ? '' : 'hidden' }}">
                <h3 class="text-sm font-extrabold text-slate-900 mb-3">{{ $focus === 'comments' ? 'Comments' : 'Results' }} ({{ count($result['items']) }})</h3>
                <div class="space-y-2">
                    @foreach($result['items'] as $item)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <p class="text-sm font-extrabold text-slate-900">{{ $item['title'] }}</p>
                            @if($item['text'] && $item['text'] !== $item['title'])
                                <p class="text-xs text-slate-600 mt-1 whitespace-pre-line break-words">{{ $item['text'] }}</p>
                            @endif
                            @if($item['meta'])
                                <p class="text-[11px] text-slate-400 mt-1 font-semibold">{{ $item['meta'] }}</p>
                            @endif
                            @if($item['url'])
                                <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-block mt-2 text-[11px] font-bold text-brand-primary">Open</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

    @else
        <div class="p-5 sm:p-8">
            @if($featured && $focus === 'media')
                <div class="mx-auto max-w-lg mb-6">
                    <div class="relative overflow-hidden rounded-2xl bg-black">
                        @if($featured['is_video'] && $featured['url'])
                            <video controls playsinline preload="metadata" class="w-full max-h-[70vh] bg-black"
                                   @if($featured['thumb']) poster="{{ $img($featured['thumb']) }}" @endif>
                                <source src="{{ $img($featured['url']) }}" type="video/mp4">
                            </video>
                        @elseif($featured['thumb'] || $featured['url'])
                            <img src="{{ $img($featured['thumb'] ?: $featured['url']) }}" alt="" class="w-full max-h-[70vh] object-contain bg-slate-950">
                        @endif
                    </div>
                </div>
            @endif
            @if(count($result['media']))
                <h3 class="text-sm font-extrabold text-slate-900 mb-3">Media ({{ count($result['media']) }})</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                    @foreach($result['media'] as $item)
                        @include('partials.media-tile', ['item' => $item, 'showDownload' => true, 'downloadLabel' => 'Save'])
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($isDownload && ! $isAvatar && ! $featured)
        <p class="text-sm text-slate-500 font-medium text-center py-6">No downloadable file was found. Paste a public reel, photo, or video URL.</p>
    @elseif($isAvatar && empty($result['avatar']))
        <p class="text-sm text-slate-500 font-medium text-center py-6">No profile photo was found for this account.</p>
    @elseif(! $isDownload && ! $isAvatar && !count($result['media']) && !count($result['posts']) && !count($result['people']) && !count($result['items']) && !count($result['highlights']) && !$result['bio'] && !count($result['stats']))
        <p class="text-sm text-slate-500 font-medium text-center py-6 px-5">The API returned no extra media for this query. Try another public username or URL.</p>
    @endif
</div>
