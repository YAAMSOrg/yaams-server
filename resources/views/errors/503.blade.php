@extends('layouts.error')
@section('title', 'Service Unavailable')
@section('content')
    {{-- Also shown during `php artisan down`. rescue(): the DB may be unavailable too. --}}
    <x-error-page code="503" icon="bi-tools" heading="Service Unavailable"
        message="We're performing maintenance or the service is temporarily unavailable. Please try again in a few minutes."
        :support-email="rescue(fn () => \App\Models\Setting::get('support_email'), null, false)" />
@endsection
