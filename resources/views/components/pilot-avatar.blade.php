@props(['name' => '', 'size' => 40])
@php
    // Build up to two initials from the pilot's name
    $parts = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach ($parts as $part) {
        if ($part !== '') {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        if (mb_strlen($initials) >= 2) {
            break;
        }
    }
    if ($initials === '') {
        $initials = '?';
    }

    // Deterministic colour derived from the name so each pilot keeps the same avatar
    $palette = ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#198754', '#20c997', '#0dcaf0', '#495057'];
    $color = $palette[abs(crc32($name)) % count($palette)];
@endphp
<span {{ $attributes->merge(['class' => 'd-inline-flex align-items-center justify-content-center rounded-circle text-white fw-semibold flex-shrink-0']) }}
      style="width: {{ $size }}px; height: {{ $size }}px; background-color: {{ $color }}; font-size: {{ round($size * 0.4) }}px;"
      title="{{ $name }}">
    {{ $initials }}
</span>
