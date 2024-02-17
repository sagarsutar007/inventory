@extends('adminlte::page')

@section('title', 'Create Role')

@section('content')
    <div class="row">
        <div class="col-4 mx-auto">
            <form action="{{ route('categories.store') }}" method="post" autocomplete="off">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Create Role</h3>
                    <div class="card-tools">
                        <a class="btn btn-primary" href="{{ route('roles') }}" target="_blank">
                            <i class="fa fa-user text-white"></i> View Roles
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 d-flex align-items-center">
                            Role Name:
                        </div>
                        <div class="col-md-9 pt-3">
                            <div class="form-group mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Role name">
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            </form>
        </div>
    </div>
@stop


@section('js')
    <script>
        $(function () {
            
        });
    </script>
@stop