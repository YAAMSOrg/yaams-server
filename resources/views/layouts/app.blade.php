<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>@yield('title')</title>
        
        <link rel="icon" href="{{ asset('favicon.ico') }}">

        <!-- TODO: LOCAL Delivery of these objects. I don't want to stream them from the cloud. This is just for dev! -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

        
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
                            Flights
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownFlights">
                            <li><a class="dropdown-item" href="{{ route('flightlist') }}">My PIREPs</a></li>
                            <li><a class="dropdown-item" href="{{ route('addflight') }}">File a PIREP</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('fleetmanager') }}">Fleet</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Live map</a>
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
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li><a class="dropdown-item" href="#">Edit profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Current airline: Lufthansa</a></li>
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
