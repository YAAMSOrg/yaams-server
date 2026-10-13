@extends('layouts.error')
@section('title', 'Server Error')
@section('content')
    {{-- rescue(): the DB may be why we are here, so reading a setting must never fail twice. --}}
    <x-error-page code="500" icon="bi-exclamation-octagon" heading="Server Error"
        message="Something went wrong on our side. Please try again in a moment."
        :support-email="rescue(fn () => \App\Models\Setting::get('support_email'), null, false)" />
@endsection
