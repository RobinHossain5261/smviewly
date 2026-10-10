@extends('layouts.app')

@section('title', $result['title'] . ' · ' . ucfirst($result['platform']) . ' ' . $result['tool_name'])
@section('description', 'Anonymous ' . $result['tool_name'] . ' result for ' . $result['handle'])

@section('content')
<div class="py-12 px-4 sm:px-6 lg:px-8 bg-[#fafafa]">
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('viewer.tool', ['platform' => $result['platform'], 'tool' => $result['tool']]) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 mb-8 transition-colors">
            <i class="ph ph-caret-left font-bold text-sm"></i>
            <span>Back to {{ $result['tool_name'] }}</span>
        </a>
        @include('partials.live-results', ['result' => $result])
    </div>
</div>
@endsection
