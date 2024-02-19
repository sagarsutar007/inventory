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
                                <!-- <th width="5%">Sno.</th> -->
                                <th width="10%">Code</th>
                                <th>Material Name</th>
                                <th>Unit</th>
                                <th>Commodity</th>
                                <th>Category</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($materials as $material)
                            <tr>
                                <!-- <td width="5%">{{ $loop->iteration }}</td> -->
                                <td width="10%">{{ $material->part_code }}</td>
                                <td>{{ $material->description }}</td>
                                <td>{{ $material->uom->uom_text }}</td>
                                <td>{{ $material->commodity->commodity_name }}</td>
                                <td>{{ $material->category->category_name }}</td>
                                <td width="15%">
                                    <a href="#" role="button" data-partcode="{{ $material->part_code }}" data-desc="{{ $material->description }}" data-matid="{{ $material->material_id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView">
                                        <i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View Material"></i>
                                    </a> / 
                                    <a href="#" role="button" data-matid="{{ $material->material_id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit"><i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit"></i></a> / 
                                    <form action="{{ route('semi.destroy', $material->material_id) }}" method="post" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Are you sure you want to delete this material?')"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></button>
                                    </form> / 
                                    <button role="button" data-matid="{{ $material->material_id }}" class="btn btn-sm btn-link text-success p-0 btn-export-bom"><i class="fas fa-file-excel" data-toggle="tooltip" data-placement="top" title="Export BOM"></i></button> / 
                                    <button role="button" data-desc="{{ $material->description }}" data-matid="{{ $material->material_id }}" data-toggle="modal" data-target="#modalUploadBOM" class="btn btn-sm btn-link text-warning p-0 btn-import-bom"><i class="fas fa-file-excel" data-toggle="tooltip" data-placement="top" title="Import BOM"></i></i></button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- <x-adminlte-modal id="modalView" title="View Material" icon="fas fa-box" scrollable>
        <div class="row" id="view-material-modal">
            <div class="col-12">
                <h2 class="text-secondary text-center">Loading...</h2>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal> -->

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

    <x-modaltabs id="modalView" title="View Semi Finished Material">
        <x-slot name="header">
            <ul class="nav nav-pills nav-fill">
                <li class="nav-item">
                    <a class="nav-link active" id="overview-tab" data-toggle="tab" data-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="documents-tab" data-toggle="tab" data-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">Documents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="boms-tab" data-toggle="tab" data-target="#boms" type="button" role="tab" aria-controls="boms" aria-selected="false">Bill of Materials</a>
                </li>
            </ul>
        </x-slot>
        <x-slot name="body">
            <div class="tab-content" id="view-material-modal">
                
            </div>
        </x-slot>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </x-slot>
    </x-modaltabs>

    <x-adminlte-modal id="modalUploadBOM" title="Upload Bill of Material" icon="fas fa-box">
        <form action="/" id="upload-bom-form" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row" id="edit-material-modal">
                <div class="col-12">
                    <input type="hidden" name="material_id" value="" id="emid">
                    <div class="form-group w-100">
                        <label for="excel-file">Upload Excel</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="file" id="excel-file" accept=".csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                                <label class="custom-file-label" for="excel-file">Choose file</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <x-slot name="footerSlot">
            <a href="{{ asset('assets/formats/bom-format.xlsx') }}" type="button" class="btn btn-outline-success">
                <i class="fas fa-file-export"></i> Download Format
            </a>
            <button type="button" class="btn btn-outline-primary btn-submit-import">
                <i class="fas fa-check"></i> Submit
            </button>
        </x-slot>
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
                "scrollY": "320px",
                "scrollCollapse": true,
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
                var materialPartcode = button.data('partcode');
                var materialDesc = button.data('desc');
                var modal = $(this)
                modal.find(".nav-link").removeClass('active');
                modal.find(".nav-link:first").addClass('active');
                $.ajax({
                    url: '/app/semi-finished-materials/' + materialId + '/show',
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $("#view-material-modal").html(response.html);
                            $("#boms-table").DataTable({
                                "responsive": true,
                                "lengthChange": false,
                                "autoWidth": true,
                                "paging": false,
                                "searching": true,
                                "info": false,
                                "buttons": [
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: ':visible:not(.exclude)'
                                        },
                                        title: materialPartcode + " - " + materialDesc + " - BOM",
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: ':visible:not(.exclude)'
                                        },
                                        title: materialPartcode + " - " + materialDesc + " - BOM",
                                    },
                                    {
                                        extend: 'print',
                                        exportOptions: {
                                            columns: ':visible:not(.exclude)'
                                        },
                                        title: materialPartcode + " - " + materialDesc + " - BOM",
                                    },
                                    'colvis'
                                ],
                            }).buttons().container().appendTo('#export-section');
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
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                _token: '{{ csrf_token() }}',
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
                $(this).closest('.raw-with-quantity').remove();
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

            $(document).on('click', '.btn-destroy-attachment', function() {
                var attachmentId = $(this).data('attid');
                var _obj = $(this);
                $.ajax({
                    url: '/app/material-attachment/' + attachmentId + '/destroy',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')            
                    },
                    success: function(response) {
                        if (response.status) {
                            toastr.success(response.message);
                            _obj.closest('.col-md-4').remove();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        console.error(jsonResponse.message);
                        toastr.error(jsonResponse.message);
                    }
                });
            });

            $(document).on('click', '.btn-export-bom', function() {
                var materialId = $(this).data('matid');
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": true,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
                toastr.success("Your download will begin shortly.");
                $.ajax({
                    url: '/app/material/' + materialId + '/export-bom',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')            
                    },
                    success: function(response) {
                        var downloadUrl = response.downloadUrl;
                        window.location.href = downloadUrl;
                    },
                    error: function(xhr, status, error) {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        console.error(jsonResponse.message);
                        toastr.error(jsonResponse.message);
                    }
                });
            });

            $(document).on('click', '.btn-import-bom', function() {
                var materialId = $(this).data('matid');
                var materialDesc = $(this).data('desc');
                $("#emid").val(materialId);
                $("#modalUploadBOM").find('.modal-title').html('Upload ' + materialDesc + ' BOM');
                $("#modalUploadBOM").modal('show');
            });

            $(".btn-submit-import").on('click', function(e){
                $('#upload-bom-form').submit();
            });

            $('#upload-bom-form').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                var material = $("#emid").val();
                $.ajax({
                    url: 'material/'+material+'/import-bom/',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if(response.status){
                            toastr.success(response.message);
                            $("#modalUploadBOM").modal('hide');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        console.error(jsonResponse.message);
                        toastr.error(jsonResponse.message);
                    }
                });
            });
        });
    </script>
@stop