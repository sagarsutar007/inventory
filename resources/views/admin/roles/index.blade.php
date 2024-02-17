@extends('adminlte::page')

@section('title', 'Roles')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Roles</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-light dropdown-toggle dropdown-icon-disabled btn-sm" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" role="menu">
                                <a class="dropdown-item" href="{{ route('roles.add') }}" target="_blank">
                                    <i class="fa fa-plus text-secondary"></i> Create Role
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="roles" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">Sno.</th>
                                <th width="10%">Name</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($roles->slice(1) as $role)
                            <tr>
                                <td width="5%">{{ $loop->iteration }}</td>
                                <td width="10%">{{ $role->name }}</td>
                                <td width="10%">
                                    <a href="#" role="button" data-comid="{{ $role->id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit">
                                        <i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit Role"></i>
                                    </a> /<form action="{{ route('roles.destroy', $role->id) }}" method="post" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link p-0" onclick="return confirm('Are you sure you want to delete this role?')">
                                            <i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete Role"></i>
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

    <x-adminlte-modal id="modalEdit" title="Edit Role" icon="fas fa-edit">
        <div class="row">
            <div class="col-12">
                <input type="hidden" id="edit-role-id" value="">
                <input type="hidden" id="old-role-name" value="">
                <div class="form-group">
                    <label for="edit-role">Enter Role Name</label>
                    <input type="text" id="edit-role" name="role_name" class="form-control" placeholder="Loading..." value="">
                </div>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-role" theme="outline-primary" label="Save"/>
        </x-slot>
    </x-adminlte-modal>
@stop


@section('js')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
            $("#roles").DataTable({"responsive":1,"lengthChange":1,"autoWidth":1,"paging":1,"info":1,"language": {"lengthMenu": "_MENU_"},"buttons":[{extend:'excel',exportOptions:{columns:[0,1]}},{extend:'pdf',exportOptions:{columns:[0,1]}},{extend:'print',exportOptions:{columns:[0,1]}}],}).buttons().container().appendTo('#roles_wrapper .col-md-6:eq(0)');
            $('#modalEdit').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var roleId = button.data('comid');
                var modal = $(this)
                
                $.ajax({
                    url: '/app/roles/' + roleId + '/edit',
                    method: 'GET',
                    success: function(response) {
                        if (response.role) {
                            modal.find('.modal-body input#edit-role-id').val(response.role.id);
                            modal.find('.modal-body input#edit-role').val(response.role.name);
                            modal.find('.modal-body input#old-role-name').val(response.role.name);
                        } else {
                            toastr.error('role not found!');
                        }
                        modal.find('.modal-body input#edit-role').attr("placeholder", "Enter role name");
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            })

            $(document).on('click', '.btn-save-role', function(e) {
                e.preventDefault();
                
                let role_name = $("#edit-role").val();
                let role_id = $("#edit-role-id").val();
                let old_role_name = $("#old-role-name").val();
                
                if (role_name === "") {
                    toastr.error('Please enter role name!');
                } else {
                    if (role_id === "") {
                        toastr.error('Something went wrong! Please reload the page.');
                    } else {
                        $.ajax({
                            url: '/app/roles/' + role_id + '/update',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                role_name: role_name
                            },
                            success: function(response) {
                                $('td:contains("'+old_role_name+'")').text(role_name);
                                toastr.success('role updated successfully');
                            },
                            error: function(error) {
                                if (error.responseJSON && error.responseJSON.message) {
                                    toastr.error(error.responseJSON.message);
                                }
                            }
                        });
                    }
                }

                $('#modalEdit').modal('hide');
            });
        });
    </script>
@stop