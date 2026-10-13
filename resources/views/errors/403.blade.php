@extends('layouts.error')
@section('title', 'Access Denied')
@section('content')
    <x-error-page code="403" icon="bi-shield-lock" heading="Access Denied"
        :message="$exception->getMessage() ?: 'You don\'t have permission to access this page.'" />
@endsection
