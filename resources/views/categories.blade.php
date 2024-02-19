@extends('adminlte::page')

@section('title', 'Categories')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Categories</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-light dropdown-toggle dropdown-icon-disabled btn-sm" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" role="menu">
                                <a class="dropdown-item" href="{{ route('categories.add') }}">
                                    <i class="fa fa-plus text-secondary"></i> Multiple Records
                                </a>
                                <a class="dropdown-item" href="{{ route('categories.bulk') }}">
                                    <i class="fas fa-file-import text-secondary"></i> Bulk Upload
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="categories" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <!-- <th width="5%">Sno.</th> -->
                                <th width="10%">Code</th>
                                <th>Category</th>
                                <th>RM</th>
                                <th>SFG</th>
                                <th>FG</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <!-- <td width="5%">{{ $loop->iteration }}</td> -->
                                <td width="10%">{{ $category->category_number }}</td>
                                <td>{{ $category->category_name }}</td>
                                <td>{{ $category->raw_count }}</td>
                                <td>{{ $category->semi_finished_count }}</td>
                                <td>{{ $category->finished_count }}</td>
                                <td width="10%">
                                    <a href="#" role="button" data-comid="{{ $category->category_id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit">
                                        <i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit Category"></i>
                                    </a> /<form action="{{ route('categories.destroy', $category->category_id) }}" method="post" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link p-0" onclick="return confirm('Are you sure you want to delete this record?')">
                                            <i class="fas fa-trash text-danger" data-toggle="tooltip" data-placement="top" title="Delete Category"></i>
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
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    }
                ],
                "stateSave": true
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