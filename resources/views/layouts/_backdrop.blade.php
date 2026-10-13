{{-- Decorative chart-grid background with a flight route ending at the airplane icon.
     Pass ['animated' => true] to draw the route once on load (auth pages only). --}}
<div class="yaams-backdrop @if($animated ?? false) yaams-backdrop--animated @endif" aria-hidden="true">
    <svg viewBox="0 0 100 100" preserveAspectRatio="none" focusable="false">
        <defs>
            <mask id="yaams-route-mask" maskUnits="userSpaceOnUse" x="-10" y="-10" width="120" height="120">
                <path class="route-reveal" pathLength="1" d="M -3 88 C 45 88, 52 18, 88 18" />
            </mask>
        </defs>
        <path class="route" mask="url(#yaams-route-mask)" d="M -3 88 C 45 88, 52 18, 88 18" />
    </svg>
    <i class="plane bi bi-airplane-fill"></i>
</div>
