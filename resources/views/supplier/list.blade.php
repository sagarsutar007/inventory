@extends('adminlte::page')

@section('title', 'Vendors')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Vendors</h3>
                    <div class="card-tools">
                        @can('add-vendor')
                        <button data-toggle="modal" data-target="#manage-vendor-modal" class="btn btn-default btn-sm" id="add-vendor-btn">Add Vendor</Button>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <table id="vendors-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="10%">Vendor Code</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>City</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-adminlte-modal id="manage-vendor-modal" title="Add Vendor" icon="fas fa-plus">
        <form action="" id="vendor-form" method="POST">
            @csrf
            <input type="hidden" name="vendor_id" value="">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="txt-vname">Vendor Name</label>
                        <input type="text" name="vendor_name" class="form-control" placeholder="Enter Vendor Name">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="txt-vcity">Vendor City</label>
                        <input type="text" name="vendor_city" class="form-control" placeholder="Enter City">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="txt-vaddr">Vendor Address</label>
                        <textarea name="vendor_address" class="form-control" placeholder="Enter Address"></textarea>
                    </div>
                </div>
            </div>
        </form>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-vendor" theme="primary" label="Save"/>
        </x-slot>
    </x-adminlte-modal>
@stop

@section('js')
    <script>
        $(function () {
            $('#vendors-table').DataTable({
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
                "stateSaveParams": function(settings, data) {
                    data.search.search = '';
                },
                "ajax": {
                    "url": "{{ route('vendor.get') }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    { "data": "vendor_code", "name": "vendor_code" },
                    { "data": "vendor_name", "name": "vendor_name" },
                    { "data": "vendor_city", "name": "vendor_city" },
                    { "data": "vendor_address", "name": "vendor_address" },
                    { 
                        "data": null,
                        "render": function (data, type, row) {
                            return '<button class="btn btn-link view-vendor-btn btn-sm" data-id="' + row.vendor_id + '" data-toggle="modal" data-target="#manage-vendor-modal"><i class="fas fa-eye text-primary"></i></button>' 
                            @can('edit-vendor')
                            + '/ <button class="btn btn-link edit-vendor-btn btn-sm" data-id="' + row.vendor_id + '" data-toggle="modal" data-target="#manage-vendor-modal"><i class="fas fa-edit text-primary"></i></button>'
                            @endcan 
                            @can('delete-vendor')
                            + '/ <button class="btn btn-link delete-vendor-btn btn-sm" data-id="' + row.vendor_id + '"><i class="fas fa-trash text-danger"></i></button>'
                            @endcan 
                            ;
                        }
                    }
                ],
                "lengthMenu": datatableLength,
                "searching": true,
                "ordering": true,
                "order": [[0, 'asc']],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                }
            });

            $(document).on('click', '.edit-vendor-btn', function() {
                $(".btn-save-vendor").show();
                var vendorId = $(this).data('id');
                $.ajax({
                    url: '{{ route("vendor.show") }}',
                    type: 'POST',
                    data: {
                        vendor_id: vendorId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            var vendor = response.vendor;
                            $('#vendor-form input[name="vendor_id"]').val(vendor.vendor_id).attr('disabled', false);
                            $('#vendor-form input[name="vendor_name"]').val(vendor.vendor_name).attr('disabled', false);
                            $('#vendor-form input[name="vendor_city"]').val(vendor.vendor_city).attr('disabled', false);
                            $('#vendor-form textarea[name="vendor_address"]').val(vendor.vendor_address).attr('disabled', false);
                            $('#vendors-table').DataTable().ajax.reload();
                            // Open the modal
                            $('#manage-vendor-modal').modal('show');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred while fetching vendor details.');
                        console.error(xhr.responseText);
                    }
                });
                $("#manage-vendor-modal").find('.modal-title').text("Edit Vendor");
            });

            $(document).on('click', '.view-vendor-btn', function() {
                var vendorId = $(this).data('id');
                $.ajax({
                    url: '{{ route("vendor.show") }}',
                    type: 'POST',
                    data: {
                        vendor_id: vendorId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            var vendor = response.vendor;
                            $('#vendor-form input[name="vendor_id"]').val(vendor.vendor_id).attr('disabled', true);
                            $('#vendor-form input[name="vendor_name"]').val(vendor.vendor_name).attr('disabled', true);
                            $('#vendor-form input[name="vendor_city"]').val(vendor.vendor_city).attr('disabled', true);
                            $('#vendor-form textarea[name="vendor_address"]').val(vendor.vendor_address).attr('disabled', true);

                            $(".btn-save-vendor").hide();
                            
                            // Open the modal
                            $('#manage-vendor-modal').modal('show');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred while fetching vendor details.');
                        console.error(xhr.responseText);
                    }
                });
                $("#manage-vendor-modal").find('.modal-title').text("View Vendor");
            });

            $(document).on('click', '.btn-save-vendor', function() {
                var formData = $('#vendor-form').serialize();
                $.ajax({
                    type: "POST",
                    url: "{{ route('vendor.save') }}",
                    data: formData,
                    success: function(response) {
                        $("#manage-vendor-modal").modal('hide');
                        toastr.success('Vendor Saved Successfully.');
                        $('#vendors-table').DataTable().ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        console.error(jsonResponse.message);
                        toastr.error(jsonResponse.message);
                    }
                });
            });

            $('#add-vendor-btn').click(function() {
                $('#vendor-form')[0].reset();
                $(".btn-save-vendor").show();
                $("#manage-vendor-modal").find('.modal-title').text("Add Vendor");
            });

            $(document).on('click', '.delete-vendor-btn', function() {
                var vendorId = $(this).data('id');
                if (confirm('Are you sure you want to delete this vendor?')) {
                    $.ajax({
                        url: "{{ route('vendor.delete') }}",
                        type: 'POST',
                        data: {
                            vendor_id: vendorId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status) {
                                $('#vendors-table').DataTable().ajax.reload();
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            toastr.error('An error occurred while deleting the vendor.');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });

        });
    </script>
@stop