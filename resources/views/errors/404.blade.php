@extends('layouts.error')
@section('title', 'Page Not Found')
@section('content')
    <x-error-page code="404" icon="bi-signpost-split" heading="Page Not Found"
        message="This page is off the chart. It may have been moved, or the link you followed is wrong." />
@endsection
