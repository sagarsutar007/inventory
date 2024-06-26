@extends('adminlte::page')

@section('title', 'Raw Materials')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Raw Materials</h3>
                    <div class="card-tools">
                        @can('add-raw-material')
                        <a class="btn btn-light btn-sm" href="{{ route('raw.add') }}"><i class="fa fa-plus text-secondary"></i> Add New</a>
                        <a class="btn btn-light btn-sm" href="{{ route('raw.bulk') }}"><i class="fas fa-file-import text-secondary"></i> Bulk Upload</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="rawmaterials" class="table table-bordered table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th width="5%">Sno.</th>
                                    <th class="text-center">Image</th>
                                    <th width="10%">Partcode</th>
                                    <th>Raw Material Name</th>
                                    <th>Unit</th>
                                    <th>Commodity</th>
                                    <th>Category</th>
                                    <th>Make</th>
                                    <th>MPN</th>
                                    <th>Dependent Material</th>
                                    <th>Frequency</th>
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

    <x-adminlte-modal id="modalEdit" title="Edit Raw Material" icon="fas fa-box" size='lg' scrollable>
        <form action="/" id="edit-material-form" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row" id="edit-material-modal">
                <div class="col-12">
                    <h2 class="text-secondary text-center">Loading...</h2>
                </div>
            </div>
            <x-slot name="footerSlot">
                <button type="button" class="btn btn-sm btn-outline-secondary add-vendor-price-item">
                    <i class="fas fa-fw fa-plus"></i> Add Vendor
                </button>
                <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
                <x-adminlte-button class="btn-sm btn-save-material" theme="outline-primary" label="Save"/>
            </x-slot>
        </form>
    </x-adminlte-modal>

    <x-adminlte-modal id="modalClone" title="Clone Raw Material" icon="fas fa-box" size='lg' scrollable>
        <form action="/" id="clone-material-form" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row" id="clone-material-modal">
                <div class="col-12">
                    <h2 class="text-secondary text-center">Loading...</h2>
                </div>
            </div>
            <x-slot name="footerSlot">
                <button type="button" class="btn btn-sm btn-outline-secondary add-vendor-price-item-clone">
                    <i class="fas fa-fw fa-plus"></i> Add Vendor
                </button>
                <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
                <x-adminlte-button class="btn-sm btn-save-clone-material" theme="outline-primary" label="Save"/>
            </x-slot>
        </form>
    </x-adminlte-modal>
    
    <x-modaltabs id="modalView" title="View Raw Material">
        <x-slot name="header">
            <ul class="nav nav-pills nav-fill">
                <li class="nav-item">
                    <a class="nav-link active" id="overview-tab" data-toggle="tab" data-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="documents-tab" data-toggle="tab" data-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="true">Documents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="vendors-tab" data-toggle="tab" data-target="#vendors" type="button" role="tab" aria-controls="vendors" aria-selected="true">Vendors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="used-tab" data-toggle="tab" data-target="#used" type="button" role="tab" aria-controls="used" aria-selected="true">Used In</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="reserved-tab" data-toggle="tab" data-target="#reserved" type="button" role="tab" aria-controls="reserved" aria-selected="true">Reserved</a>
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
@stop

@section('js')
    <script>
        $(function () {
            $('#rawmaterials').DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": true,
                "paging": true,
                "info": true,
                "autoWidth":true,
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
                "stateSaveParams": function(settings, data) {
                    data.search.search = '';
                },
                "ajax": {
                    "url": "{{ route('raw.fetchRawMaterials') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
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
                    { "data": "dependent", "name": "dependent" },
                    { "data": "frequency", "name": "frequency" },
                    { "data": "re_order", "name": "re_order" },
                    { "data": "actions", "name": "actions" },
                ],
                "lengthMenu": datatableLength,
                "searching": true,
                "ordering": true,
                "columnDefs": [
                    {
                        "targets": [0, 1, 12],
                        "orderable": false,
                    },
                    {
                        "targets": [7, 8],
                        "visible": false,
                    },
                    {
                        "targets": [11],
                        "className": 'dt-right'
                    },
                    // {
                    //     "targets": [4],
                    //     "className": 'dt-center'
                    // }
                ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
                "initComplete": function(settings, json) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            $('#modalEdit').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var materialId = button.data('matid');
                var modal = $(this)
                
                $.ajax({
                    url: '/app/raw-materials/' + materialId + '/edit',
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
                            $('#dependent').select2({
                                placeholder: 'Dependent Material',
                                theme: 'bootstrap4'
                            });
                        }
                        suggestVendor("#modalEdit");
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            $('#modalClone').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var materialId = button.data('matid');
                var modal = $(this)
                
                $.ajax({
                    url: '/app/raw-materials/' + materialId + '/edit',
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $("#clone-material-modal").html(response.html);
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
                        }
                        suggestVendor("#modalClone");
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            $('#modalView').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var materialId = button.data('matid');
                var modal = $(this)
                modal.find(".nav-link").removeClass('active');
                modal.find(".nav-link:first").addClass('active');
                $.ajax({
                    url: '/app/raw-materials/' + materialId + '/show',
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $("#view-material-modal").html(response.html);
                            $("#used-table").DataTable({
                                lengthMenu: datatableLength,
                                autoWidth: true,
                                language: {
                                    lengthMenu: "_MENU_"
                                },
                                buttons: [
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
                                dom: 'lBfrtip',
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            // Function to add a new category item
            $(document).on('click', '.add-vendor-price-item, .add-vendor-price-item-clone', function () {
                var clickedClass = event.target.classList[3];
                console.log("Clicked class:", clickedClass);

                var newItem = $(".vendor-with-price:first").clone();
                newItem.find('input').val('');
                $(".vendor-price-container").append(newItem);

                if (clickedClass === "add-vendor-price-item-clone") {
                    suggestVendor("#modalClone");
                } else {
                    suggestVendor("#modalEdit");
                }
                
            });

            // Function to remove the closest category item
            $(document).on('click', '.remove-vendor-price-item', function () {
                if ($('.vendor-with-price').length > 1) {
                    $(this).closest('.vendor-with-price').remove();
                } else {
                    alert("At least one vendor & price item should be present.");
                }
            });

            function suggestVendor(parent) {
                $(".sug-vendor").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: "/app/vendor/autocomplete",
                            dataType: "json",
                            data: {
                                term: request.term,
                            },
                            success: function (data) {
                                response(data);
                            },
                        });
                    },
                    appendTo: parent,
                    minLength: 2,
                });
            }

            $('.btn-save-material').click(function () {
                var materialId = $("#material-id").val();
                var formData = new FormData($('#edit-material-form')[0]);
                $.ajax({
                    url: '/app/raw-materials/' + materialId + '/update', 
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status) {
                            $('#modalEdit').modal('hide');
                            toastr.success(response.message);
                            $('#rawmaterials').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function (xhr, status, error) {
                        let response = JSON.parse(xhr.responseText);
                        toastr.error(response.message);
                    }
                });

                
            });

            $('.btn-save-clone-material').click(function () {
                var formData = new FormData($('#clone-material-form')[0]);
                $.ajax({
                    url: '{{ route("raw.store") }}', 
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#modalClone').modal('hide');
                            window.location.reload();
                        } else {
                            // Display validation errors using toastr
                            toastr.error('Failed to clone raw material');
                        }
                    },
                    error: function (xhr, status, error) {
                        let response = JSON.parse(xhr.responseText);
                        if (response.errors) {
                            $.each(response.errors, function (key, value) {
                                toastr.error(value);
                            });
                        } else {
                            toastr.error('An error occurred while processing your request');
                        }
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

            // Show Notices Message
            @if(session()->has('notice'))
                toastr.warning({{ session('notice') }});
            @endif
        });
    </script>
@stop