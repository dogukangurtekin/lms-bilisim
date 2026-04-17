@extends('layout.app')
@section('title', $title)
@section('content')
<section class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-sm font-semibold text-gray-700 shadow">
                <span>💎</span><span>1250</span>
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-sm font-semibold text-gray-700 shadow">
                <span>🪙</span><span>840</span>
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-sm font-semibold text-gray-700 shadow">
                <span>🚀</span><span>72</span>
            </span>
            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-600 shadow hover:bg-gray-100">
                🔔
            </button>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-sm font-semibold text-gray-700 shadow">U</span>
        </div>
    </div>

    <article class="w-full rounded-2xl bg-white p-6 shadow-lg">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#FDBA12] text-lg font-bold text-white">
                    {{ $lessonNumber }}
                </span>
                <h2 class="text-2xl font-bold text-gray-900">{{ $detailTitle }}</h2>
            </div>
            @if($isCompleted ?? false)
                <span class="inline-flex h-12 items-center justify-center rounded-xl bg-emerald-600 px-6 text-base font-semibold text-white">
                    Bu dersi tamamladiniz
                </span>
            @else
                <a href="{{ $startUrl ?? '#' }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-[#FDBA12] px-6 text-base font-semibold text-white transition hover:brightness-95">
                    Derse Basla
                </a>
            @endif
        </div>

        <div class="mt-6 space-y-6 text-gray-700">
            <section>
                <h3 class="text-lg font-bold text-gray-900">Konu:</h3>
                <p class="mt-2 text-base leading-relaxed">{{ $konu }}</p>
            </section>

            <section>
                <h3 class="text-lg font-bold text-gray-900">Kazanimlar:</h3>
                <ul class="mt-2 list-disc space-y-1 pl-6 text-base">
                    @foreach($kazanimlar as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </section>

            <section>
                <h3 class="text-lg font-bold text-gray-900">Etkinlikler:</h3>
                <ul class="mt-2 list-disc space-y-1 pl-6 text-base">
                    @foreach($etkinlikler as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </section>
        </div>
    </article>
</section>
@endsection
