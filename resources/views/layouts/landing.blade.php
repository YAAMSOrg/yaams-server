<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'YAAMS')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">

    <style>
        body {
            background-color: #f4f6f9;
            color: #333;
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
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="container my-5 flex-grow-1 d-flex flex-column">
        @yield('content')
    </main>

    <footer class="bg-white border-top mt-auto py-4">
        <div class="container d-flex justify-content-between align-items-center text-muted">
            <small>&copy; {{ date('Y') }} YAAMS Virtual Airline Management</small>
            <small>v0.0.1 | <a href="https://www.github.com/flymia/YAAMS/" target="_blank" class="text-decoration-none text-secondary"><i class="bi bi-github"></i> GitHub</a></small>
        </div>
    </footer>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
