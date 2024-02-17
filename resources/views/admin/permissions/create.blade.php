@extends('adminlte::page')

@section('title', 'Create Permission')

@section('content')
    <div class="row">
        <div class="col-4 mx-auto">
            <form action="{{ route('permissions.store') }}" method="post" autocomplete="off">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Create Permission</h3>
                    <div class="card-tools">
                        <a class="btn btn-primary" href="{{ route('permissions') }}" target="_blank">
                            <i class="fa fa-user text-white"></i> View Permissions
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="permission-item-container">
                        <div class="permission-item d-flex align-items-center">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="permissions[]" placeholder="Permission name" required>
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-times remove-permission-item"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-outline-secondary add-permission-item"><i class="fas fa-fw fa-plus"></i> New</button>
                    <a href="{{ route('permissions') }}" class="btn btn-outline-danger"><i class="fas fa-fw fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-fw fa-check"></i> Submit</button>
                </div>
            </div>
            </form>
        </div>
    </div>
@stop


@section('js')
    <script>
        $(function () {
            // Function to add a new permission item
            $(".add-permission-item").click(function () {
                var newItem = $(".permission-item:first").clone();
                newItem.find('input').val('');
                $(".permission-item-container").append(newItem);
            });

            // Function to remove the closest permission item
            $(".permission-item-container").on('click', '.remove-permission-item', function () {
                if ($('.permission-item').length > 1) {
                    $(this).closest('.permission-item').remove();
                } else {
                    alert("At least one permission item should be present.");
                }
            });

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