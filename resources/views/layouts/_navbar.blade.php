{{-- Main site navbar, included by layouts/app.
     Left cluster: Home + the airline-scoped groups (Flights / Fleet / Community / Management),
     rendered only when an active airline is in the session — their routes all sit behind
     the `airline` middleware anyway. Management is only shown to reviewers/managers. --}}
@php
    $activeAirline = auth()->check() ? session('activeairline') : null;
@endphp
<header class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ auth()->check() ? route('dashboard') : route('home') }}">
            <img src="{{ asset('img/yaams-logo-icon.svg') }}" alt="{{ $instanceName }} Logo" height="32">
            <span class="fs-5 fw-bold tracking-tight">{{ $instanceName }}</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard', 'home') ? 'active' : '' }}" href="{{ auth()->check() ? route('dashboard') : route('home') }}"><i class="bi bi-house-door"></i> Home</a>
                </li>

                @if($activeAirline)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('flightlist', 'flightadd', 'viewflight') ? 'active' : '' }}" href="#" id="navbarDropdownFlights" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-journal-album"></i> Flights
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navbarDropdownFlights">
                            <li><a class="dropdown-item {{ request()->routeIs('flightadd') ? 'active' : '' }}" href="{{ route('flightadd') }}"><i class="bi bi-plus-circle me-2 text-secondary"></i> File a PIREP</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('flightlist', 'viewflight') ? 'active' : '' }}" href="{{ route('flightlist') }}"><i class="bi bi-journal-text me-2 text-secondary"></i> My PIREPs</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('fleetmanager', 'createaircraft', 'viewaircraft', 'editaircraft') ? 'active' : '' }}" href="#" id="navbarDropdownFleet" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-airplane-fill"></i> Fleet
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navbarDropdownFleet">
                            <li><a class="dropdown-item {{ request()->routeIs('fleetmanager', 'viewaircraft', 'editaircraft') ? 'active' : '' }}" href="{{ route('fleetmanager') }}"><i class="bi bi-airplane me-2 text-secondary"></i> Fleet overview</a></li>
                            @can('add aircraft')
                                <li><a class="dropdown-item {{ request()->routeIs('createaircraft') ? 'active' : '' }}" href="{{ route('createaircraft') }}"><i class="bi bi-plus-circle me-2 text-secondary"></i> Add aircraft</a></li>
                            @endcan
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('crewactivity') ? 'active' : '' }}" href="#" id="navbarDropdownCommunity" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-people-fill"></i> Community
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navbarDropdownCommunity">
                            <li><a class="dropdown-item {{ request()->routeIs('crewactivity') ? 'active' : '' }}" href="{{ route('crewactivity') }}"><i class="bi bi-activity me-2 text-secondary"></i> Crew Activity</a></li>
                        </ul>
                    </li>

                    @php
                        $canReviewFlights = $activeAirline->require_pirep_review && auth()->user()->canReviewFlightsFor($activeAirline);
                        $isAirlineManager = auth()->user()->isManagerOf($activeAirline);
                    @endphp
                    @if($canReviewFlights || $isAirlineManager)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('flightreviewindex', 'airline.settings', 'invitecodes.index', 'notams.index') ? 'active' : '' }}" href="#" id="navbarDropdownManagement" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-briefcase"></i> Management
                            </a>
                            <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navbarDropdownManagement">
                                @if($canReviewFlights)
                                    <li><a class="dropdown-item {{ request()->routeIs('flightreviewindex') ? 'active' : '' }}" href="{{ route('flightreviewindex') }}"><i class="bi bi-clipboard-check me-2 text-secondary"></i> Review flights</a></li>
                                @endif
                                @if($canReviewFlights && $isAirlineManager)
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                @if($isAirlineManager)
                                    <li><a class="dropdown-item {{ request()->routeIs('airline.settings') ? 'active' : '' }}" href="{{ route('airline.settings') }}"><i class="bi bi-sliders me-2 text-secondary"></i> Operations</a></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('invitecodes.index') ? 'active' : '' }}" href="{{ route('invitecodes.index') }}"><i class="bi bi-ticket-perforated me-2 text-secondary"></i> Invite codes</a></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('notams.index') ? 'active' : '' }}" href="{{ route('notams.index') }}"><i class="bi bi-megaphone me-2 text-secondary"></i> Announcements</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif
            </ul>

            <ul class="navbar-nav align-items-center">
                @guest
                    <li class="nav-item me-2">
                        <a class="nav-link btn btn-outline-light btn-sm px-3" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary btn-sm px-3 text-white" href="{{ route('register') }}">Sign Up</a>
                    </li>
                @endguest

                @auth
                    @php
                        // Fallback to empty collection if relation is not loaded or user has no airlines
                        $userAirlines = auth()->user()->airlines ?? collect();
                        $newNotifications = auth()->user()->countNewNotifications();
                    @endphp

                    @if($userAirlines->count() > 0)
                        <li class="nav-item dropdown me-2 border-end border-secondary pe-3 border-opacity-25">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="airlineDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-2 font-monospace">{{ $activeAirline ? $activeAirline->icao_callsign : 'N/A' }}</span>
                                <span class="d-none d-md-inline small fw-semibold text-white-50">{{ $activeAirline ? $activeAirline->name : 'Select Airline' }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="airlineDropdown">
                                <li><h6 class="dropdown-header text-uppercase tracking-wider">Switch Active Airline</h6></li>

                                @foreach($userAirlines as $airline)
                                    <li>
                                        <a class="dropdown-item d-flex justify-content-between align-items-center py-2 @if($activeAirline && $activeAirline->id === $airline->id) bg-light text-dark disabled @endif"
                                           href="#"
                                           onclick="event.preventDefault(); document.getElementById('switch-airline-form-{{ $airline->id }}').submit();">

                                            <span class="fw-medium @if($activeAirline && $activeAirline->id === $airline->id) fw-bold text-primary @endif">{{ $airline->name }}</span>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-3 font-monospace">{{ $airline->icao_callsign }}</span>
                                        </a>

                                        <form id="switch-airline-form-{{ $airline->id }}" action="{{ route('changeactiveairline') }}" method="POST" class="d-none">
                                            @csrf
                                            <input type="hidden" name="airline_id" value="{{ $airline->id }}">
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                    <li class="nav-item me-lg-2">
                        <a class="nav-link position-relative d-flex align-items-center" href="{{ route('usernotifications') }}" title="Notifications" aria-label="Notifications">
                            <i class="bi {{ $newNotifications > 0 ? 'bi-bell-fill' : 'bi-bell' }} fs-5"></i>
                            @if($newNotifications > 0)
                                <span class="position-absolute start-100 translate-middle badge rounded-pill bg-danger border border-light"
                                      style="top: 35%; font-size: 0.6rem; padding: 0.25em 0.45em; line-height: 1;">
                                    {{ $newNotifications > 9 ? '9+' : $newNotifications }}
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <x-pilot-avatar :name="Auth::user()->name" :size="32" />
                            <span>{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="navbarDropdownUser">
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{ route('usernotifications') }}">
                                    <i class="bi bi-bell me-2 text-secondary"></i>
                                    <span>Notifications</span>
                                    @if($newNotifications > 0)
                                        <span class="badge bg-danger rounded-pill ms-auto">{{ $newNotifications }}</span>
                                    @endif
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('portal') }}"><i class="bi bi-buildings me-2 text-secondary"></i> Airline Portal</a></li>
                            <li><a class="dropdown-item" href="{{ route('settings.profile') }}"><i class="bi bi-gear me-2 text-secondary"></i> Settings</a></li>
                            @role('Super-Admin')
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-lock me-2 text-secondary"></i> Administration</a></li>
                            @endrole
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</header>
