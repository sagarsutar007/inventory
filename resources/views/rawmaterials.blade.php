@extends('adminlte::page')

@section('title', 'Raw Materials')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Raw Materials</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-light dropdown-toggle dropdown-icon-disabled btn-sm" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" role="menu">
                                <a class="dropdown-item" href="{{ route('raw.add') }}"><i class="fa fa-plus text-secondary"></i> Add New</a>
                                <a class="dropdown-item" href="{{ route('raw.bulk') }}"><i class="fas fa-file-import text-secondary"></i> Bulk Upload</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="rawmaterials" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <!-- <th width="5%">Sno.</th> -->
                                <th>Image</th>
                                <th width="10%">Code</th>
                                <th>Raw Material Name</th>
                                <th>Unit</th>
                                <th>Commodity</th>
                                <th>Category</th>
                                <th width="13%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($rawmaterials as $material)
                            <tr>
                                <!-- <td width="5%">{{ $loop->iteration }}</td> -->
                                <td class="text-center">
                                    @php
                                        $imageAttachment = $material->attachments()->where('type', 'image')->first();
                                    @endphp
                                    @if($imageAttachment)
                                        <img src="{{ asset('assets/uploads/materials/' . $imageAttachment->path) }}" class="mt-2" width="30px" height="30px">
                                    @else
                                        <img src="{{ asset('assets/img/default-image.jpg') }}" class="mt-2" width="30px" height="30px">
                                    @endif
                                </td>
                                <td width="10%">{{ $material->part_code }}</td>
                                <td>{{ $material->description }}</td>
                                <td>{{ $material->uom->uom_text }}</td>
                                <td>{{ $material->commodity->commodity_name }}</td>
                                <td>{{ $material->category->category_name }}</td>
                                <td width="13%" class="text-center">
                                    <a href="#" role="button" data-matid="{{ $material->material_id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView"><i class="fas fa-eye" data-toggle="tooltip" data-placement="top" title="View"></i></a> / 
                                    <a href="#" role="button" data-matid="{{ $material->material_id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit"><i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit"></i></a> /
                                    <a href="#" role="button" data-matid="{{ $material->material_id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalClone"><i class="fas fa-copy" data-toggle="tooltip" data-placement="top" title="Clone"></i></a>
                                    /<form action="{{ route('raw.destroy', $material->material_id) }}" method="post" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Are you sure you want to delete this record?')"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></button>
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
            $("#rawmaterials").DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: true,
                paging: true,
                info: true,
                stateSave: true,
                scrollY: "320px",
                scrollCollapse: true,
                language: {
                    "lengthMenu": "_MENU_"
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
                    'colvis'
                ]
            }).buttons().container().appendTo('#rawmaterials_wrapper .col-md-6:eq(0)');

            
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
                        }
                    },
                    error: function (xhr, status, error) {
                        let response = JSON.parse(xhr.responseText);
                        console.error(xhr.responseText);
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
        });
    </script>
@stop