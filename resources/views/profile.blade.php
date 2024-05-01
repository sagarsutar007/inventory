@extends('adminlte::page')

@section('title', 'Profile')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <form action="" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">My profile</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <input type="text" class="form-control" placeholder="Enter Name" name="name" value="{{ $profile->name }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input type="email" class="form-control" placeholder="Enter Email" name="email" value="{{ $profile->email }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Phone</label>
                                    <input type="number" class="form-control" placeholder="Enter Phone" name="phone" value="{{ $profile->phone }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-primary btn-sm">Save Changes</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
@stop


@section('js')
    <script>
        $(function () {

            // Show Error Messages
            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif

            // Show Success Message
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
        });
    </script>
@stop