@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-12">
        <h1 class="text-4xl font-bold">Japan Destinations</h1>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach($destinations as $destination)
                <a class="rounded-lg border bg-white p-5" href="{{ route('destinations.show', $destination) }}">
                    <h2 class="font-semibold">{{ $destination->name }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $destination->excerpt }}</p>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $destinations->links() }}</div>
    </section>
@endsection
