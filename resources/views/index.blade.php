@extends('statamic::layout')
@section('title', 'Link checker')
@section('wrapper_class', 'max-w-full')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    @livewireStyles
@endpush

@section('content')
    <header class="flex justify-between mb-4">
        <h1>Link checker</h1>
        <div class="flex items-center">
            <p>Last run: {{ \Martinoak\StatamicLinkChecker\Model\Link::first()?->updated_at?->format('j.n.Y H:i') ?? 'N/A' }}</p>
            <a href="{{ cp_route('link-checker.run') }}" class="btn-primary ml-4">Re-run</a>
        </div>
    </header>

    <livewire:link-checker::links />

    @livewireScriptConfig
@endsection
