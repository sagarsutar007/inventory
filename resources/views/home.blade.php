@extends('adminlte::page')

@section('title', 'Inventory')

@section('content_header')
    <h1 class="m-0 text-dark">Hi {{ Auth::user()->name }}!</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">You are logged in!</p>
                </div>
            </div>
        </div>
    </div>
@stop
