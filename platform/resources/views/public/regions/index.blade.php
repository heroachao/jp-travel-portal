@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-12">
        <h1 class="text-4xl font-bold">Japan Regions</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-600">Browse regional travel channels for practical Japan trip planning.</p>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @forelse($regions as $region)
                <a class="rounded-lg border bg-white p-5" href="{{ route('regions.show', $region) }}">
                    <h2 class="font-semibold">{{ $region->display_name ?: $region->name }}</h2>
                    @if($region->excerpt)
                        <p class="mt-2 text-sm text-slate-600">{{ $region->excerpt }}</p>
                    @endif
                </a>
            @empty
                <p class="text-slate-600">No region guides are available yet.</p>
            @endforelse
        </div>
    </section>
@endsection
