@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="public-card p-6">
            <p class="public-kicker">Destination Board</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight">Japan Destinations</h1>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            @foreach($destinations as $destination)
                <a class="public-tool-link" href="{{ route('destinations.show', $destination) }}">
                    <strong>{{ $destination->name }}</strong>
                    <span>{{ $destination->excerpt }}</span>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $destinations->links() }}</div>
    </section>
@endsection
