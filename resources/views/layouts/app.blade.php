<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@hasSection('title')@yield('title') - {{ $instanceName }}@else{{ $instanceName }}@endif</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">

    <style>
        body {
            background-color: #f4f6f9; /* Soft off-white for contrast against white cards */
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
            box-shadow: 0 0 0 0.25rem rgba(134, 95, 49, 0.15); /* Softer focus ring */
        }
    </style>
    <link href="{{ asset('css/yaams-theme.css') }}" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    @include('layouts._navbar')

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
                <a href="{{ url('/docs') }}" target="_blank" class="text-decoration-none text-secondary"><i class="bi bi-journal-code"></i> API Reference</a> |
                v{{ config('app.version') }} | <a href="https://www.github.com/YAAMSOrg/yaams-server/" target="_blank" class="text-decoration-none text-secondary"><i class="bi bi-github"></i> GitHub</a>
            </small>
        </div>
    </footer>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
