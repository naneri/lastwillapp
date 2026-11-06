@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-success small fw-medium']) }}>
        {{ $status }}
    </div>
@endif
