@extends('adminlte::page')

@section('title', 'Production Orders')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Production Orders</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <a href="{{ route('po.create') }}" class="btn btn-default btn-sm">Create</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')

@stop