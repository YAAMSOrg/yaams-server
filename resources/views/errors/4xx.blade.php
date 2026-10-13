@extends('layouts.error')
@section('title', 'Request Error')
@section('content')
    <x-error-page :code="$exception->getStatusCode()" icon="bi-exclamation-circle" heading="Request Error"
        message="We couldn't process this request. Please check the address and try again." />
@endsection
