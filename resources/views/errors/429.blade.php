@extends('layouts.error')
@section('title', 'Too Many Requests')
@section('content')
    <x-error-page code="429" icon="bi-speedometer" heading="Too Many Requests"
        message="You're going a little too fast. Please wait a moment and try again." />
@endsection
