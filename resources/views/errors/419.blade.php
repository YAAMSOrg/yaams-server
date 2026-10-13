@extends('layouts.error')
@section('title', 'Page Expired')
@section('content')
    <x-error-page code="419" icon="bi-hourglass-bottom" heading="Page Expired"
        message="Your session has expired. Please reload the page and try again, or sign in again.">
        <x-slot:actions>
            <a href="javascript:location.reload()" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise me-1"></i> Reload page
            </a>
            <a href="{{ url('/login') }}" class="btn btn-outline-primary">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
            </a>
        </x-slot:actions>
    </x-error-page>
@endsection
