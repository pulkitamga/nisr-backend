@extends('blog::layouts.master')

@section('content')
    <h1>{{ __("Hello World") }}</h1>

    <p>{{ __("Module") }}: {!! config('blog.name') !!}</p>
@endsection
