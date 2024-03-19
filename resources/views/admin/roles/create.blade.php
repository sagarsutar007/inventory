@extends('adminlte::page')

@section('title', 'Create Role')

@section('content')
    <div class="row">
        <div class="col-6 mx-auto">
            <form action="{{ route('roles.store') }}" method="post" autocomplete="off">
                @csrf
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Create Role</h3>
                        <div class="card-tools">
                            <a class="btn btn-default btn-sm" href="{{ route('roles') }}">
                                <i class="fa fa-list"></i> View Roles
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="role">Role Name:</label>
                                    <input type="text" id="role" class="form-control" name="name" placeholder="Role name">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="permissions">Permissions:</label>
                                    <select id="permissions" class="select2" name="permissions[]" multiple="multiple" data-placeholder="Select Permission" style="width: 100%;">
                                        @foreach($permissions as $permission)
                                            <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
@endsection


@section('js')
    <script>
        $(function () {
            $("#permissions").select2({
                theme: 'bootstrap4',
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