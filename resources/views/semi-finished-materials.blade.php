@extends('adminlte::page')

@section('title', 'Semi Finished Materials')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Semi Finished Materials</h3>
                    <div class="card-tools">
                        @can('add-semi-material')
                        <a class="btn btn-light btn-sm" href="{{ route('semi.add') }}"><i class="fa fa-plus text-secondary"></i> Add New</a>
                        <a class="btn btn-light btn-sm" href="{{ route('semi.bulk') }}"><i class="fas fa-file-import text-secondary"></i> Bulk Upload</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="materials" class="table table-bordered table-striped" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th width="5%">Sno.</th>
                                    <th class="text-center">Image</th>
                                    <th width="10%">Code</th>
                                    <th>Material Name</th>
                                    <th>Unit</th>
                                    <th>Commodity</th>
                                    <th>Category</th>
                                    <th>Make</th>
                                    <th>MPN</th>
                                    <th>Re Order</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                // "scrollY": "440px",
                // "scrollCollapse": true,
                "ajax": {
                    "url": "{{ route('semi.fetchSemiMaterials') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                        d.type = 'semi-finished';
                    }
                },
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { "data": "image", "name": "image" },
                    { "data": "part_code", "name": "part_code" },
                    { "data": "description", "name": "description" },
                    { "data": "unit", "name": "uom_shortcode" },
                    { "data": "commodity_name", "name": "commodity_name" },
                    { "data": "category_name", "name": "category_name" },
                    { "data": "make", "name": "make" },
                    { "data": "mpn", "name": "mpn" },
                    { "data": "re_order", "name": "re_order" },
                    { "data": "actions", "name": "actions" },
                ],
                "lengthMenu": datatableLength,
                "searching": true,
                "ordering": true,
                "columnDefs": [
                    {
                        "targets": [0, 1, 10],
                        "orderable": false,
                    },
                    {
                        "targets": [9],
                        "className": 'dt-right'
                    }
                ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
                "initComplete": function(settings, json) {
                    $('[data-toggle="tooltip"]').tooltip();
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
                                        title: materialPartcode,
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
                            }).buttons().container().appendTo('#boms-table_wrapper .col-md-6:eq(0)');
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
                            $('#modalEdit').modal('hide');
                        }
                    },
                    error: function (xhr, status, error) {
                        decodedText = JSON.parse(xhr.responseText);
                        console.error(xhr.responseText);
                        toastr.error(decodedText.message);
                    }
                });

                
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
                        var anchor = document.createElement('a');
                        anchor.href = downloadUrl;
                        anchor.style.display = 'none';
                        anchor.setAttribute('download', '');
                        document.body.appendChild(anchor);
                        anchor.click();
                        document.body.removeChild(anchor);
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
                        if (response.status) {
                            toastr.success(response.message);
                            $("#modalUploadBOM").modal('hide');
                        } else {
                            response.message.forEach(function(errorMsg) {
                                toastr.error(errorMsg);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        console.error(jsonResponse.message);
                        if (jsonResponse.errors) {
                            jsonResponse.errors.forEach(function(errorMsg) {
                                toastr.error(errorMsg);
                            });
                        } else {
                            toastr.error(jsonResponse.message);
                        }
                    }
                });
            });
        });
    </script>
@stop