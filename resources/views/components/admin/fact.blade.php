{{-- One labelled value in the facts row of <x-admin.entity-header>. --}}
@props(['label'])
<div class="col">
    <dt>{{ $label }}</dt>
    <dd {{ $attributes->merge(['class' => 'text-truncate']) }}>{{ $slot }}</dd>
</div>
