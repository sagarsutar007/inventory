@extends('adminlte::page')

@section('title', 'Warehouse')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Warehouse</h3>
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