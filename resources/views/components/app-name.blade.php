@php
    $name = config('app.name', 'Laravel');
    $words = preg_split('/\s+/', trim($name));
@endphp

@if (count($words) >= 2)
    {{-- Bolds the first and last word (e.g. "Kana" and "Keeper" in "Kana
         Legacy Keeper") so they visually tie back to the kanakeeper.*
         domain, leaving whatever's in between unstyled, without
         hardcoding the app's name here. --}}
    <span class="fw-bold">{{ $words[0] }}</span>
    @foreach (array_slice($words, 1, -1) as $word)
        {{ $word }}
    @endforeach
    <span class="fw-bold">{{ end($words) }}</span>
@else
    {{ $name }}
@endif
