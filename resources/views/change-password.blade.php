@extends('adminlte::page')

@section('title', 'Change Password')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-6 mx-auto">
                <form action="" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Change Password</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Current Password</label>
                                    <input type="password" class="form-control" placeholder="Enter Current Password" name="current_pass" value="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">New Password</label>
                                    <input type="password" class="form-control" placeholder="Enter New Password" name="new_pass" value="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Confirm Password</label>
                                    <input type="password" class="form-control" placeholder="Enter Confirm Password" name="conf_pass" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-primary btn-sm">Update Password</button>
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
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error('{{ $error }}');
                @endforeach
            @endif

            // Show Success Message
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
        });
    </script>
@stop
