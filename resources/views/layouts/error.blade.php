<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- Error pages may render outside the web middleware group (no session) or with
         the DB down, so nothing here may depend on session, auth or a DB query. --}}
    <title>@hasSection('title')@yield('title') - {{ $instanceName ?? config('app.name') }}@else{{ $instanceName ?? config('app.name') }}@endif</title>

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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
        }
        .error-page {
            width: 100%;
            max-width: 480px;
            padding: 1.5rem;
            margin: auto;
        }
        .error-code {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 4.5rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0.05em;
            color: #865F31;
        }
        .error-icon {
            font-size: 1.75rem;
            color: var(--yaams-accent, #E8A53F);
        }
    </style>
    <link href="{{ asset('css/yaams-theme.css') }}" rel="stylesheet">
</head>

<body>
    @include('layouts._backdrop')

    <main class="error-page">
        @yield('content')
    </main>
</body>
</html>
