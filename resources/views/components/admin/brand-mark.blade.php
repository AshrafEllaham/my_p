@props([
    'alt' => '',
])

<img
    src="{{ asset('assets/brand/saey-mark.svg') }}"
    alt="{{ $alt }}"
    {{ $attributes }}
>
