<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Yaams</title>

        <link rel="stylesheet" href="{{ asset('css/app.css') }}"
    </head>
    <body class="bg-gray-200">
        <nav class="p-6 bg-white flex justify-between mb-6">
            <ul class="flex items-center">
                <li>
                    <a href="" class="p-3">Home</a>
                </li>
                <li>
                    <a href="" class="p-3">About Us</a>
                </li>
                <li>
                    <a href="" class="p-3">Fleet</a>
                </li>
            </ul>
            <ul class="flex items-center">
                @auth
                <li>
                    <a href="" class="p-3">Example Pilot</a>
                </li>
                @endauth
                @auth
                <li>
                    <a href="" class="p-3">New...</a>
                </li>
                @endauth
                <li>
                    <a href="" class="p-3">Login</a>
                </li>
                <li>
                    <a href="{{ route('register') }}" class="p-3">Register</a>
                </li>
                @auth
                <li>
                    <a href="" class="p-3">Logout</a>
                </li>
                @endauth
            </ul>
        </nav>
        @yield('content')
    </body>
</html>
