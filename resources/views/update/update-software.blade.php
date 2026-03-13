@extends('layouts.blank')

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="mar-ver pad-btm text-center mb-4">
                        <h1 class="h3">{{ "dynamic Software Update" }}</h1>
                    </div>

                    <form method="POST" action="{{route('update-system')}}">
                        @csrf
                        <div class="bg-light p-4 rounded mb-4">
                            <div class="px-xl-2 pb-sm-3">

                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-dark px-sm-5">{{ "Update" }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
