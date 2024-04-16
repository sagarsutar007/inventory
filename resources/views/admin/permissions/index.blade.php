@extends('adminlte::page')

@section('title', 'Permissions')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Permissions</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-light dropdown-toggle dropdown-icon-disabled btn-sm" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" permission="menu">
                                <a class="dropdown-item" href="{{ route('permissions.add') }}" target="_blank">
                                    <i class="fa fa-plus text-secondary"></i> Create Permission
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="permissions" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">Sno.</th>
                                <th>Name</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($permissions as $permission)
                            <tr>
                                <td width="5%">{{ $loop->iteration }}</td>
                                <td>{{ $permission->label }}</td>
                                <td width="10%">
                                    {{-- <a href="#" permission="button" data-comid="{{ $permission->id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit">
                                        <i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit Permission"></i>
                                    </a> --}} <form action="{{ route('permissions.destroy', $permission->id) }}" method="post" style="display: inline;"> 
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link" onclick="return confirm('Are you sure you want to delete this permission?')">
                                            <i class="fas fa-trash text-danger" data-toggle="tooltip" data-placement="top" title="Delete Permission"></i>
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

    <x-adminlte-modal id="modalEdit" title="Edit Permission" icon="fas fa-edit">
        <div class="row">
            <div class="col-12">
                <input type="hidden" id="edit-permission-id" value="">
                <input type="hidden" id="old-permission-name" value="">
                <div class="form-group">
                    <label for="edit-permission">Enter Permission Name</label>
                    <input type="text" id="edit-permission" name="permission_name" class="form-control" placeholder="Loading..." value="">
                </div>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-permission" theme="outline-primary" label="Save"/>
        </x-slot>
    </x-adminlte-modal>
@stop


@section('js')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
            $("#permissions").DataTable({"responsive":1,"lengthChange":1,"autoWidth":1,"paging":1,"info":1,"language": {"lengthMenu": "_MENU_"},"buttons":[{extend:'excel',exportOptions:{columns:[0,1]}},{extend:'pdf',exportOptions:{columns:[0,1]}},{extend:'print',exportOptions:{columns:[0,1]}}],}).buttons().container().appendTo('#permissions_wrapper .col-md-6:eq(0)');
            $('#modalEdit').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var permissionId = button.data('comid');
                var modal = $(this)
                
                $.ajax({
                    url: '/app/permissions/' + permissionId + '/edit',
                    method: 'GET',
                    success: function(response) {
                        if (response.permission) {
                            modal.find('.modal-body input#edit-permission-id').val(response.permission.id);
                            modal.find('.modal-body input#edit-permission').val(response.permission.name);
                            modal.find('.modal-body input#old-permission-name').val(response.permission.name);
                        } else {
                            toastr.error('permission not found!');
                        }
                        modal.find('.modal-body input#edit-permission').attr("placeholder", "Enter permission name");
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            })

            $(document).on('click', '.btn-save-permission', function(e) {
                e.preventDefault();
                
                let permission_name = $("#edit-permission").val();
                let permission_id = $("#edit-permission-id").val();
                let old_permission_name = $("#old-permission-name").val();
                
                if (permission_name === "") {
                    toastr.error('Please enter permission name!');
                } else {
                    if (permission_id === "") {
                        toastr.error('Something went wrong! Please reload the page.');
                    } else {
                        $.ajax({
                            url: '/app/permissions/' + permission_id + '/update',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                permission_name: permission_name
                            },
                            success: function(response) {
                                $('td:contains("'+old_permission_name+'")').text(permission_name);
                                toastr.success('permission updated successfully');
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