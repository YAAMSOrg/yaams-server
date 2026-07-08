<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', $instanceName)</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">

    <style>
        body {
            background-color: #f4f6f9; /* Soft off-white for contrast against white cards */
            color: #333;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,.05);
            font-weight: 600;
            color: #495057;
            padding: 1rem 1.25rem;
        }
        .form-label {
            font-weight: 500;
            color: #4a5568;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        .input-group-text {
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15); /* Softer focus ring */
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ auth()->check() ? route('dashboard') : route('home') }}">
                    <img src="{{ asset('img/yaams-temp-logo.png') }}" alt="{{ $instanceName }} Logo" height="32">
                <span class="fs-5 fw-bold tracking-tight">{{ $instanceName }}</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ auth()->check() ? route('dashboard') : route('home') }}"><i class="bi bi-house-door"></i> Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownFlights" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-journal-album"></i> Flights
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navbarDropdownFlights">
                            <li><a class="dropdown-item" href="{{ route('flightlist') }}">My PIREPs</a></li>
                            <li><a class="dropdown-item" href="{{ route('flightadd') }}">File a PIREP</a></li>
                            @if(session('activeairline') && session('activeairline')->require_pirep_review && auth()->user()->canReviewFlightsFor(session('activeairline')))
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('flightreviewindex') }}">Review flights</a></li>
                            @endif
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('fleetmanager') }}"><i class="bi bi-airplane-fill"></i> Fleet</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('crewactivity') ? 'active' : '' }}" href="{{ route('crewactivity') }}"><i class="bi bi-people-fill"></i> Crew Activity</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-globe-americas"></i> Live Map</a>
                    </li>
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
                            $activeAirline = session('activeairline');
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
                                @if(session('activeairline') && auth()->user()->isManagerOf(session('activeairline')))
                                    <li><a class="dropdown-item" href="{{ route('airline.settings') }}"><i class="bi bi-speedometer2 me-2 text-secondary"></i> Airline Management</a></li>
                                @endif
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

    <main class="container my-5 flex-grow-1">
        @yield('content')
    </main>

    <footer class="bg-white border-top mt-auto py-4">
        <div class="container d-flex justify-content-between align-items-center text-muted">
            <small>&copy; {{ date('Y') }} {{ $instanceName }} Virtual Airline Management</small>
            <small>
                @php($supportEmail = \App\Models\Setting::get('support_email'))
                @if($supportEmail)
                    <a href="mailto:{{ $supportEmail }}" class="text-decoration-none text-secondary"><i class="bi bi-envelope"></i> Contact</a> |
                @endif
                v{{ config('app.version') }} | <a href="https://www.github.com/YAAMSOrg/yaams-server/" target="_blank" class="text-decoration-none text-secondary"><i class="bi bi-github"></i> GitHub</a>
            </small>
        </div>
    </footer>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
