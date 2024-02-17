@extends('adminlte::page')

@section('title', 'Semi Finished Materials')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Semi Finished Materials</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-light dropdown-toggle dropdown-icon-disabled btn-sm" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" role="menu">
                                <a class="dropdown-item" href="{{ route('semi.add') }}"><i class="fa fa-plus text-secondary"></i> Add New</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="materials" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">Sno.</th>
                                <th width="10%">Code</th>
                                <th>Material Name</th>
                                <th>Unit</th>
                                <th>Commodity</th>
                                <th>Category</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($materials as $material)
                            <tr>
                                <td width="5%">{{ $loop->iteration }}</td>
                                <td width="10%">{{ $material->part_code }}</td>
                                <td>{{ $material->description }}</td>
                                <td>{{ $material->uom->uom_text }}</td>
                                <td>{{ $material->commodity->commodity_name }}</td>
                                <td>{{ $material->category->category_name }}</td>
                                <td width="10%">
                                    <a href="#" role="button" data-matid="{{ $material->material_id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView">
                                        <i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View Material"></i>
                                    </a> / 
                                    <a href="#" role="button" data-matid="{{ $material->material_id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit"><i class="fas fa-edit"></i></a> / 
                                    <form action="{{ route('semi.destroy', $material->material_id) }}" method="post" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Are you sure you want to delete this material?')"><i class="fas fa-trash"></i></button>
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

    <x-adminlte-modal id="modalView" title="View Material" icon="fas fa-box" scrollable>
        <div class="row" id="view-material-modal">
            <div class="col-12">
                <h2 class="text-secondary text-center">Loading...</h2>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <x-adminlte-modal id="modalEdit" title="Edit Material" icon="fas fa-box" size='lg' scrollable>
        <form action="/" id="edit-material-form" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row" id="edit-material-modal">
                <div class="col-12">
                    <h2 class="text-secondary text-center">Loading...</h2>
                </div>
            </div>
            <x-slot name="footerSlot">
                <button type="button" class="btn btn-sm btn-outline-secondary add-raw-quantity-item">
                    <i class="fas fa-fw fa-plus"></i> Add BOM Item
                </button>
                <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
                <x-adminlte-button class="btn-sm btn-save-material" theme="outline-primary" label="Save"/>
            </x-slot>
        </form>
    </x-adminlte-modal>
@stop


@section('js')
    <script>
        $(function () {

            $("#materials").DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": true,
                "paging": true,
                "info": true,
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
            }).buttons().container().appendTo('#materials_wrapper .col-md-6:eq(0)');


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

            $('#modalView').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var materialId = button.data('matid');
                var modal = $(this)
                
                $.ajax({
                    url: '/app/semi-finished-materials/' + materialId + '/show',
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $("#view-material-modal").html(response.html);
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            $('#modalEdit').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var materialId = button.data('matid');
                var modal = $(this)
                
                $.ajax({
                    url: '/app/semi-finished-materials/' + materialId + '/edit',
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $("#edit-material-modal").html(response.html);

                            $('#uom').select2({
                                placeholder: 'Unit',
                                theme: 'bootstrap4'
                            });
                            $('#commodity').select2({
                                placeholder: 'Commodity',
                                theme: 'bootstrap4'
                            });
                            $('#category').select2({
                                placeholder: 'Category',
                                theme: 'bootstrap4'
                            });
                            $('.raw-materials').each(function() {
                                initializeRawMaterialsSelect2($(this));
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            function initializeRawMaterialsSelect2(selectElement) {
                selectElement.select2({
                    placeholder: 'Raw materials',
                    theme: 'bootstrap4',
                    ajax: {
                        url: '{{ route("semi.getRawMaterials") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term 
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data, function (item) {
                                    return {
                                        id: item.material_id,
                                        text: item.description + "-" + item.part_code
                                    };
                                })
                            };
                        },
                        cache: true
                    }
                });
            }

            // Function to add a new raw material item
            $(document).on('click', ".add-raw-quantity-item", function () {
                var newItem = `
                    <div class="raw-with-quantity">
                        <div class="row">
                            <div class="col-md-8">
                                <select class="form-control raw-materials" name="raw[]" style="width:100%;">
                                    <option value=""></option>  
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group mb-3">
                                    <input type="number" class="form-control" name="quantity[]" placeholder="Quantity">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-times remove-raw-quantity-item"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>                            
                    </div>
                `;
                var $newItem = $(newItem);
                $(".raw-materials-container").append($newItem);
                
                var newSelect = $newItem.find('.raw-materials');
                initializeRawMaterialsSelect2(newSelect);
            });

            // Function to remove the closest raw material item
            $(document).on('click', '.remove-raw-quantity-item', function () {
                if ($('.raw-with-quantity').length > 1) {
                    $(this).closest('.raw-with-quantity').remove();
                } else {
                    alert("At least one Raw Material item should be present.");
                }
            });

            $('.btn-save-material').click(function () {
                var materialId = $("#material-id").val();
                var formData = new FormData($('#edit-material-form')[0]);
                $.ajax({
                    url: '/app/semi-finished-materials/' + materialId + '/update', 
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status) {
                            toastr.success(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });

                $('#modalEdit').modal('hide');
            });
        });
    </script>
@stop