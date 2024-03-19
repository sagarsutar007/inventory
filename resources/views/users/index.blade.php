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
                                <th>User</th>
                                <th>Permissions</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td></td>
                                <td width="10%">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-link p-0">
                                        <i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit User"></i>
                                    </a> /<form action="{{ route('user.destroy', $user->id) }}" method="post" style="display: inline;">
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

    <x-adminlte-modal id="modalEdit" title="Edit Category" icon="fas fa-edit">
        <div class="row">
            <div class="col-12">
                <input type="hidden" id="edit-category-id" value="">
                <input type="hidden" id="old-category-name" value="">
                <div class="form-group">
                    <label for="edit-category">Enter Category Name</label>
                    <input type="text" id="edit-category" name="category_name" class="form-control" placeholder="Loading..." value="">
                </div>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-category" theme="outline-primary" label="Save"/>
        </x-slot>
    </x-adminlte-modal>
@stop


@section('js')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
            $("#categories").DataTable({
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
            }).buttons().container().appendTo('#categories_wrapper .col-md-6:eq(0)');

            $('#modalEdit').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var categoryId = button.data('comid');
                var modal = $(this)
                
                $.ajax({
                    url: '/app/categories/' + categoryId + '/edit',
                    method: 'GET',
                    success: function(response) {
                        if (response.category) {
                            modal.find('.modal-body input#edit-category-id').val(response.category.category_id);
                            modal.find('.modal-body input#edit-category').val(response.category.category_name);
                            modal.find('.modal-body input#old-category-name').val(response.category.category_name);
                        } else {
                            toastr.error('category not found!');
                        }
                        modal.find('.modal-body input#edit-category').attr("placeholder", "Enter category name");
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            })

            $(document).on('click', '.btn-save-category', function(e) {
                e.preventDefault();
                
                let category_name = $("#edit-category").val();
                let category_id = $("#edit-category-id").val();
                let old_category_name = $("#old-category-name").val();
                
                if (category_name === "") {
                    toastr.error('Please enter category name!');
                } else {
                    if (category_id === "") {
                        toastr.error('Something went wrong! Please reload the page.');
                    } else {
                        $.ajax({
                            url: '/app/categories/' + category_id + '/update',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                category_name: category_name
                            },
                            success: function(response) {
                                $('td:contains("'+old_category_name+'")').text(category_name);
                                toastr.success('category updated successfully');
                            },
                            error: function(error) {
                                console.error(error);
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