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

    <header class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="{{ asset('img/yaams-temp-logo.png') }}" alt="YAAMS Logo" height="40">
            </a>

            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownFlights" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi-journal-album"></i> Flights
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownFlights">
                            <li><a class="dropdown-item" href="{{ route('flightlist') }}">My PIREPs</a></li>
                            <li><a class="dropdown-item" href="{{ route('addflight') }}">File a PIREP</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('fleetmanager') }}"><i class="bi-airplane-fill"></i> Fleet</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi-globe-americas"></i> Live map</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    @guest
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                    <a href="{{ route('register') }}" class="nav-link btn btn-warning">Sign-up</a>
                    @endguest
                    @auth
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi-person-circle"></i> {{ Auth::user()->name }} | {{ session('activeairline')->icao_callsign }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                            @role('Manager')
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Notifications</a></li>
                            <li><a class="dropdown-item" href="#">Manage pilots</a></li>
                            @endrole
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><a class="dropdown-item" href="{{ route('changeactiveairline') }}">Current airline: {{ session('activeairline')->name }}</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="post">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>
    <body>
        <div class="container mt-4">
            <div class="col-md-12">
                @yield('content')
            </div>
        </div>

        <footer class="bg-body-tertiary text-center fixed-bottom">
            <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
              YAAMS Version v0.0.1 | 
              <a class="text-body" href="https://www.github.com/flymia/YAAMS/">GitHub</a>
            </div>
        </footer>
    </body>
</html>
