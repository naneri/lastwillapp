@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-danger small ps-0 mb-0 list-unstyled']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
