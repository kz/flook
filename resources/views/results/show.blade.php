@extends('layouts.master')

@section('content')

    <nav class="navbar navbar-default">
        <div class="row">
            <div class="nav-brand">
                <img src="images/logo.png">
            </div>
            <div class="form-group col-md-12 ">
                <form action="search" method="POST">
                    <input type="text" name="query" class="form-control input-normal"
                           placeholder="Search for 3D models"/>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                </form>
            </div>
        </div>
    </nav>
    <div class="row">
        @foreach($links as $key => $link)
            <div class="col-sm-4 col-md-4">
                <div class="thumbnail">
                    <a href="{{ $link }}">
                        <img src="{{ $images[$key] }}" alt="...">
                        <div class="caption">
                            <h3>{{ $names[$key] }}</h3>
                        </div>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endsection