@extends('layouts.error')
@section('title', 'Service Unavailable')
@section('content')
    {{-- rescue(): the DB may be why we are here, so reading a setting must never fail twice. --}}
    <x-error-page :code="$exception->getStatusCode()" icon="bi-exclamation-octagon" heading="Service Unavailable"
        message="The service is temporarily unavailable. Please try again in a few minutes."
        :support-email="rescue(fn () => \App\Models\Setting::get('support_email'), null, false)" />
@endsection
