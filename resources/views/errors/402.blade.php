@extends('layouts.error')
@section('title', 'Payment Required')
@section('content')
    <x-error-page code="402" icon="bi-credit-card" heading="Payment Required"
        message="This feature is not available for your account." />
@endsection
