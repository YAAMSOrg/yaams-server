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
            /* Faint navigation-chart graticule over a pale sky wash. */
            background:
                repeating-linear-gradient(0deg, rgba(40, 46, 58, 0.045) 0 1px, transparent 1px 72px),
                repeating-linear-gradient(90deg, rgba(40, 46, 58, 0.045) 0 1px, transparent 1px 72px),
                radial-gradient(ellipse at 85% 15%, rgba(232, 165, 63, 0.14), transparent 55%),
                linear-gradient(180deg, #eef2f6 0%, #f6f4f0 100%);
            background-attachment: fixed;
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
        .form-signin {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            padding: 1.5rem;
            margin: auto;
        }

        /*
         * Decorative flight route: a dashed arc that passes behind the card and
         * ends at the Bootstrap Icons airplane (bi-airplane-fill), clear of the
         * form. The SVG stretches to any window shape (preserveAspectRatio=none)
         * while the plane is pinned at the same percentages as the route's end
         * point. The arc ends flying level, and a horizontal tangent stays
         * horizontal under any stretch, so the plane (rotated to face right)
         * always lines up with it. Dashes use non-scaling-stroke so they stay
         * even. The route draws itself once on load; reduced motion shows it
         * finished. Hidden on phones, where the card fills the width.
         */
        .auth-backdrop {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }
        .auth-backdrop svg {
            width: 100%;
            height: 100%;
        }
        .auth-backdrop .route {
            fill: none;
            stroke: #865F31;
            stroke-opacity: 0.45;
            stroke-width: 2;
            stroke-dasharray: 10 12;
            stroke-linecap: round;
            vector-effect: non-scaling-stroke;
        }
        .auth-backdrop .route-reveal {
            fill: none;
            stroke: #fff;
            stroke-width: 10;
            stroke-dasharray: 1;
            stroke-dashoffset: 1;
            animation: auth-route-draw 1.8s cubic-bezier(0.45, 0, 0.2, 1) 0.2s forwards;
        }
        .auth-backdrop .plane {
            position: absolute;
            left: 88%; /* Must match the route's end point (88, 18). */
            top: 18%;
            font-size: 64px;
            line-height: 1;
            color: #282E3A;
            transform: translate(-50%, -50%) rotate(90deg); /* Glyph points north. */
            opacity: 0;
            animation: auth-plane-in 0.6s ease-out 1.7s forwards;
        }
        .auth-backdrop .plane::before {
            display: block;
            vertical-align: 0;
        }
        @keyframes auth-route-draw {
            to { stroke-dashoffset: 0; }
        }
        @keyframes auth-plane-in {
            to { opacity: 1; }
        }
        @media (prefers-reduced-motion: reduce) {
            .auth-backdrop .route-reveal { animation: none; stroke-dashoffset: 0; }
            .auth-backdrop .plane { animation: none; opacity: 1; }
        }
        @media (max-width: 575.98px) {
            .auth-backdrop { display: none; }
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(134, 95, 49, 0.15); /* Softer focus ring */
        }
        .btn-primary {
            background-color: #865F31;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
        }
    </style>
    <link href="{{ asset('css/yaams-theme.css') }}" rel="stylesheet">
</head>

<body>
    <div class="auth-backdrop" aria-hidden="true">
        <svg viewBox="0 0 100 100" preserveAspectRatio="none" focusable="false">
            <defs>
                <mask id="auth-route-mask" maskUnits="userSpaceOnUse" x="-10" y="-10" width="120" height="120">
                    <path class="route-reveal" pathLength="1" d="M -3 88 C 45 88, 52 18, 88 18" />
                </mask>
            </defs>
            <path class="route" mask="url(#auth-route-mask)" d="M -3 88 C 45 88, 52 18, 88 18" />
        </svg>
        <i class="plane bi bi-airplane-fill"></i>
    </div>

    <main class="form-signin">
        @yield('content')
    </main>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
