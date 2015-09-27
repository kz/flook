@extends('layouts.master')

@section('content')
    <div class="image-wrapper">
        <div class="jumbotron vertical-center">
            <div class="container">
                <div class=" container col-md-10">
                    <a href="/">
                        <img src="/images/logo.png">
                    </a>
                    <div class="row">
                        <div class="form-group col-md-10 ">
                            <form action="search" method="POST">
                                <input name="query" type="text" class="form-control input-normal"
                                       placeholder="Search the web for 3D models"/>
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection