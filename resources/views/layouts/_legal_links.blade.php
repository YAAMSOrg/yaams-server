{{-- Links to the published legal pages (Imprint / Privacy / Terms). Reads
     settings through rescue() so it is safe in error pages, where the DB may be
     down. Pass $separator to append " |" after the last link (inline footers). --}}
@php($legalLinks = rescue(fn () => \App\Support\LegalPage::links(), [], false))
@foreach ($legalLinks as $link)
    <a href="{{ $link['url'] }}" class="text-decoration-none text-secondary">{{ $link['title'] }}</a>@if (! $loop->last || ($separator ?? false)) |@endif
@endforeach
