@extends('layouts.error')
@section('title', 'Sign In Required')
@section('content')
    <x-error-page code="401" icon="bi-person-lock" heading="Sign In Required"
        message="You need to be signed in to view this page.">
        <x-slot:actions>
            <a href="javascript:history.back()" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Go back
            </a>
            <a href="{{ url('/login') }}" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
            </a>
        </x-slot:actions>
    </x-error-page>
@endsection
