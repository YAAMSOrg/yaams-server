<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>@yield('title')</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}">

        <!-- TODO: LOCAL Delivery of these objects. I don't want to stream them from the cloud. This is just for dev! -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>

    <body class="d-flex flex-column min-vh-100">
        <!-- Navbar -->
        <header class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <img src="{{ asset('img/yaams-temp-logo.png') }}" alt="YAAMS Logo" height="40">
                </a>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownFlights" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi-journal-album"></i> Flights
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownFlights">
                                <li><a class="dropdown-item" href="{{ route('flightlist') }}">My PIREPs</a></li>
                                <li><a class="dropdown-item" href="{{ route('flightadd') }}">File a PIREP</a></li>
                                @can('review flight')
                                <li><a class="dropdown-item" href="{{ route('flightreviewindex') }}">Review flights</a></li>
                                @endcan
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('fleetmanager') }}"><i class="bi bi-airplane-fill"></i> Fleet</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="bi bi-globe-americas"></i> Live Map</a>
                        </li>
                    </ul>

                    <!-- User Auth -->
                    <ul class="navbar-nav">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link btn btn-light btn-sm" href="{{ route('login') }}">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-primary btn-sm" href="{{ route('register') }}">Sign Up</a>
                            </li>
                        @endguest

                        @auth
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i> {{ Auth::user()->name }} | {{ session('activeairline')->icao_callsign }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                                    <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                                    @role('Manager')
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('usernotifications') }}">Notifications</a></li>
                                        <li><a class="dropdown-item" href="#">Manage Pilots</a></li>
                                    @endrole
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#">Settings</a></li>
                                    <li><a class="dropdown-item" href="{{ route('changeactiveairline') }}">Current airline: {{ session('activeairline')->name }}</a></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="container mt-4">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="bg-dark text-white mt-auto py-3">
            <div class="container text-center">
                <small>YAAMS Version v0.0.1 | <a href="https://www.github.com/flymia/YAAMS/" class="text-decoration-none text-white">GitHub</a></small>
            </div>
        </footer>
    </body>
</html>
