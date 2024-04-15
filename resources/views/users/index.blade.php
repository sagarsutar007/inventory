@extends('adminlte::page')

@section('title', 'Users')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Users</h3>
                    <div class="card-tools">
                        <a class="btn btn-default btn-sm" href="{{ route('users.add') }}">
                            <i class="fas fa-plus text-secondary"></i> Create User
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="users-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Gender</th>
                                <th>Total Permissions</th>
                                <th width="8%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->employee_id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->role->role->name }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->gender }}</td>
                                <td>{{ $user->permissions->count() }}</td>
                                <td width="8%" class="text-center">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-link p-0">
                                        <i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit User"></i>
                                    </a> /
                                    <a href="{{ route('users.permission', $user->id) }}" class="btn btn-sm btn-link p-0">
                                        <i class="fas fa-universal-access" data-toggle="tooltip" data-placement="top" title="Edit Permission"></i>
                                    </a> /
                                    <form action="{{ route('user.destroy', $user->id) }}" method="post" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link p-0" onclick="return confirm('Are you sure you want to delete this record?')">
                                            <i class="fas fa-trash text-danger" data-toggle="tooltip" data-placement="top" title="Delete User"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop


@section('js')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();

            $("#users-table").DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": true,
                "paging": true,
                "info": true,
                "language": {
                    "lengthMenu": "_MENU_"
                },
                "buttons": [
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        }
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        }
                    },
                    'colvis',
                ],
                "stateSave": true,
                "scrollY": "320px",
                "scrollCollapse": true
            }).buttons().container().appendTo('#users-table_wrapper .col-md-6:eq(0)');
            
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