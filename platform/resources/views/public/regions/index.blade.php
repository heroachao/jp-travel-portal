@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="public-card p-6">
            <p class="public-kicker">Region Standings</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight">Japan Regions</h1>
            <p class="mt-3 max-w-3xl text-lg text-slate-600">Browse regional travel channels for practical Japan trip planning.</p>
        </div>

        @ad('content-mid-rectangle')

        <div class="mt-5 grid gap-4 md:grid-cols-3">
            @forelse($regions as $region)
                <a class="public-tool-link" href="{{ \App\Support\PublicUrl::route('regions.show', $region) }}">
                    <strong>{{ $region->display_name ?: $region->name }}</strong>
                    @if($region->excerpt)
                        <span>{{ $region->excerpt }}</span>
                    @endif
                </a>
            @empty
                <p class="text-slate-600">No region guides are available yet.</p>
            @endforelse
        </div>
    </section>
@endsection
