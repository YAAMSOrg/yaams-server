@extends('layouts.app')
@section('title', 'My Flights')

@section('content')
@php
    // Header sort link: first click sorts descending (newest / longest first),
    // a second click on the same column flips the order.
    $sortUrl = function (string $column) {
        $order = request('sort_by') === $column && request('sort_order', 'desc') === 'desc' ? 'asc' : 'desc';

        return route('flightlist', array_merge(request()->query(), [
            'sort_by' => $column,
            'sort_order' => $order,
            'page' => 1,
        ]), false);
    };

    $ariaSort = function (string $column) {
        if (request('sort_by') !== $column) {
            return 'none';
        }

        return request('sort_order', 'desc') === 'asc' ? 'ascending' : 'descending';
    };

    $sortIcon = function (string $column) {
        if (request('sort_by') !== $column) {
            return '';
        }

        return request('sort_order', 'desc') === 'asc'
            ? '<i class="bi bi-arrow-up" aria-hidden="true"></i>'
            : '<i class="bi bi-arrow-down" aria-hidden="true"></i>';
    };

    $filterUrl = fn (?string $value) => route('flightlist', array_merge(
        request()->except(['status', 'page']),
        $value ? ['status' => $value] : []
    ), false);

    $blockTime = fn (int $minutes) => intdiv($minutes, 60) . ':' . sprintf('%02d', $minutes % 60);

    // [label, count, active] per status filter; '' is "All"
    $filters = [
        '' => ['All', $totalFlights, $status === null],
        'pending' => ['Pending', $statusCounts['pending'], $status === 'pending'],
        'accepted' => ['Accepted', $statusCounts['accepted'], $status === 'accepted'],
        'rejected' => ['Rejected', $statusCounts['rejected'], $status === 'rejected'],
    ];

    $filtered = request('search') || $status;
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold text-dark">My Flights</h1>
        <p class="text-muted mb-0 yaams-logbook__record">
            {{ session('activeairline')->name }}
            <span aria-hidden="true">·</span>
            {{ number_format($totalFlights) }} {{ Str::plural('flight', $totalFlights) }} filed
            <span aria-hidden="true">·</span>
            <span class="text-dark fw-semibold" title="{{ number_format($acceptedMinutes) }} minutes">{{ $blockTime($acceptedMinutes) }}</span> accepted block time
        </p>
    </div>
    <div>
        <a href="{{ route('flightadd') }}" class="btn btn-primary px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> File PIREP
        </a>
    </div>
</div>

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
    <h4 class="alert-heading fs-6 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Error during request</h4>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center mb-3">
    <nav aria-label="Filter by status" class="yaams-logbook__filters">
        @foreach($filters as $value => [$label, $count, $active])
            <a href="{{ $filterUrl($value ?: null) }}"
               class="yaams-logbook__filter @if($active) is-active @endif"
               @if($active) aria-current="page" @endif>
                {{ $label }} <span class="yaams-logbook__count">{{ $count }}</span>
            </a>
        @endforeach
    </nav>

    <form action="{{ route('flightlist') }}" method="GET" class="m-0 flex-grow-1" role="search">
        @foreach(['status', 'sort_by', 'sort_order'] as $keep)
            @if(request()->filled($keep))
                <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">
            @endif
        @endforeach
        <label for="flight-search" class="visually-hidden">Search flights</label>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted" aria-hidden="true"></i>
            </span>
            <input type="search" id="flight-search" name="search" class="form-control border-start-0 ps-0" placeholder="Flight, ICAO or registration" value="{{ request('search') }}">
            @if(request('search'))
                <a href="{{ route('flightlist', request()->except(['search', 'page'])) }}" class="btn btn-outline-secondary d-flex align-items-center" aria-label="Clear search">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </a>
            @endif
            <button class="btn btn-primary px-4 fw-semibold" type="submit">Search</button>
        </div>
    </form>
</div>

@if ($flights->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-journal-text fs-1 text-secondary d-block mb-3" aria-hidden="true"></i>
            @if($filtered)
                <h2 class="h5 fw-bold text-dark">No matching flights</h2>
                <p class="text-muted mx-auto mb-3" style="max-width: 400px;">
                    @if(request('search'))
                        Nothing in your logbook matches "{{ request('search') }}"{{ $status ? " among your {$status} flights" : '' }}.
                    @else
                        You have no {{ $status }} flights for this airline.
                    @endif
                </p>
                <a href="{{ route('flightlist', request()->except(['search', 'status', 'page'])) }}" class="btn btn-outline-secondary btn-sm px-4">Show all flights</a>
            @else
                <h2 class="h5 fw-bold text-dark">No flights logged yet</h2>
                <p class="text-muted mx-auto" style="max-width: 400px;">You haven't submitted any PIREPs for this airline. Ready to log your first route?</p>
                <a href="{{ route('flightadd') }}" class="btn btn-outline-primary btn-sm px-4 mt-2">File a PIREP now</a>
            @endif
        </div>
    </div>
@else
    <div class="card shadow-sm border-0 overflow-hidden mb-4">
        <table class="table align-middle mb-0 yaams-logbook">
            <caption class="visually-hidden">
                Your flights, {{ $maxEntries }} {{ Str::plural('entry', $maxEntries) }}@if($status), {{ $status }} only @endif
            </caption>
            <thead>
                <tr>
                    <th scope="col" aria-sort="{{ $ariaSort('date') }}"><a href="{!! $sortUrl('date') !!}">Date (UTC) {!! $sortIcon('date') !!}</a></th>
                    <th scope="col" aria-sort="{{ $ariaSort('flightnumber') }}"><a href="{!! $sortUrl('flightnumber') !!}">Flight {!! $sortIcon('flightnumber') !!}</a></th>
                    <th scope="col" aria-sort="{{ $ariaSort('route') }}"><a href="{!! $sortUrl('route') !!}">Route {!! $sortIcon('route') !!}</a></th>
                    <th scope="col" aria-sort="{{ $ariaSort('aircraft') }}"><a href="{!! $sortUrl('aircraft') !!}">Aircraft {!! $sortIcon('aircraft') !!}</a></th>
                    <th scope="col" class="text-end" aria-sort="{{ $ariaSort('duration') }}"><a href="{!! $sortUrl('duration') !!}">Block {!! $sortIcon('duration') !!}</a></th>
                    <th scope="col" class="text-end" aria-sort="{{ $ariaSort('status') }}"><a href="{!! $sortUrl('status') !!}">Status {!! $sortIcon('status') !!}</a></th>
                </tr>
            </thead>
            <tbody>
                @foreach($flights as $flight)
                    <tr @class(['yaams-logbook__entry', 'is-rejected' => (int) $flight->status_id === 3])>
                        <td class="yaams-logbook__date">
                            <span class="d-block">{{ str_replace('/', '-', $flight->flight_date) }}</span>
                            <span class="yaams-logbook__sub">{{ substr($flight->blockoff, 11, 5) }}-{{ substr($flight->blockon, 11, 5) }}Z</span>
                        </td>
                        <td class="yaams-logbook__flight">
                            <a href="{{ route('viewflight', $flight->id) }}" class="stretched-link">{{ $flight->full_flight_number }}</a>
                            <span class="yaams-logbook__sub">{{ $flight->full_icao_callsign }}</span>
                        </td>
                        <td class="yaams-logbook__route">
                            <span class="yaams-logbook__legs">
                                <abbr title="{{ $flight->departure_airport?->name ?? $flight->departure_icao }}">{{ $flight->departure_icao }}</abbr><i class="bi bi-arrow-right" aria-hidden="true"></i><span class="visually-hidden"> to </span><abbr title="{{ $flight->arrival_airport?->name ?? $flight->arrival_icao }}">{{ $flight->arrival_icao }}</abbr>
                            </span>
                            <span class="yaams-logbook__sub yaams-logbook__airports">{{ $flight->departure_airport?->name ?? $flight->departure_icao }} - {{ $flight->arrival_airport?->name ?? $flight->arrival_icao }}</span>
                        </td>
                        <td class="yaams-logbook__aircraft">
                            <span class="d-block">{{ $flight->aircraft->registration }}</span>
                            <span class="yaams-logbook__sub">{{ $flight->aircraft->full_type }}</span>
                        </td>
                        <td class="yaams-logbook__block text-end">
                            @if(is_int($flight->flight_duration_minutes))
                                <span class="visually-hidden">Block time </span>{{ $blockTime($flight->flight_duration_minutes) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="yaams-logbook__status text-end">
                            <x-flight-status :flight="$flight" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($maxPages > 1)
        <nav aria-label="Flight list pagination">
            <ul class="pagination justify-content-center shadow-sm d-inline-flex border rounded-3 overflow-hidden">

                <li class="page-item @if($currentPage <= 1) disabled @endif">
                    <a class="page-link border-0 px-3 py-2 text-secondary" href="{{ route('flightlist', array_merge(request()->query(), ['page' => $currentPage - 1]), false) }}" aria-label="Previous">
                        <span aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                    </a>
                </li>

                @for($page = 1; $page <= $maxPages; $page++)
                    <li class="page-item @if($page === $currentPage) active @endif">
                        <a class="page-link border-0 px-3 py-2 fw-semibold @if($page === $currentPage) text-white bg-primary @else text-secondary @endif" href="{{ route('flightlist', array_merge(request()->query(), ['page' => $page]), false) }}">
                            {{ $page }}
                        </a>
                    </li>
                @endfor

                <li class="page-item @if($currentPage >= $maxPages) disabled @endif">
                    <a class="page-link border-0 px-3 py-2 text-secondary" href="{{ route('flightlist', array_merge(request()->query(), ['page' => $currentPage + 1]), false) }}" aria-label="Next">
                        <span aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                    </a>
                </li>
            </ul>
        </nav>
    @endif
@endif
@endsection
