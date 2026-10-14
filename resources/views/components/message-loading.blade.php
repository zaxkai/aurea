@props([
    'size' => null,
])

<x-ui.message-loading {{ $attributes->merge(['size' => $size]) }} />
