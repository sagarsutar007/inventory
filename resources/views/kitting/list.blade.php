@extends('adminlte::page')

@section('title', 'Kitting')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Kitting</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <a href="{{ route('po.new') }}" class="btn btn-default btn-sm">Create</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="prod-orders" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Material</th>
                                <th>Unit</th>
                                <th>Quantity</th>
                                <th>Created On</th>
                                <th>Created By</th>
                                <th>Status</th>
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

    <x-adminlte-modal id="orderDetailsModal" title="Order Details" icon="fas fa-info-circle" size='xl' scrollable>
        <div class="row">
            <div class="col-md-12" id="order-details-section">
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-issue-order" theme="primary" label="Save"/>
        </x-slot>
    </x-adminlte-modal>

    <x-adminlte-modal id="recordsModal" title="Issue/Reciept Details" icon="fas fa-info-circle" size='xl' scrollable>
        <div class="row">
            <div class="col-md-12" id="records-section">
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>
@stop

@section('js')
    <script>
        $(function () {
            $('#prod-orders').DataTable({
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
                "scrollY": "320px",
                "scrollCollapse": true,
                "ajax": {
                    "url": "{{ route('po.get') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    { "data": "po_number", "name": "po_number" },
                    { "data": "description", "name": "description" },
                    { "data": "unit", "name": "uom_shortcode" },
                    { "data": "quantity", "name": "quantity" },
                    { "data": "created_at", "name": "created_at" },
                    { "data": "created_by", "name": "created_by" },
                    { "data": "status", "name": "status" },
                    { 
                        "data": null,
                        "render": function ( data, type, row ) {
                            return '<button class="btn btn-link kitting-btn btn-sm" data-pon="' + row.po_number + '" data-id="' + row.po_id + '" data-desc="'+ row.description +'" data-qty="'+ row.quantity +'" data-unit="'+ row.unit +'"><i class="fas fa-edit text-primary"></i></button>' +" / "+
                            '<button class="btn btn-link records-btn btn-sm" data-pon="' + row.po_number + '" data-id="' + row.po_id + '" data-desc="'+ row.description +'" data-qty="'+ row.quantity +'" data-unit="'+ row.unit +'"><i class="fas fa-eye text-primary"></i></button>'
                            ;
                        }
                    }
                ],
                "lengthMenu": [10, 25, 50, 75, 100],
                "searching": true,
                "ordering": true,
                "order": [[0, 'desc']],
                "columnDefs": [
                    {
                        "targets": [7],
                        "orderable": false
                    }
                ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
            });

            $(document).on('click', '.kitting-btn', function() {
                let po_id = $(this).data('id');
                let po_num = $(this).data('pon');
                let po_desc = $(this).data('desc');
                let po_qty = $(this).data('qty');
                let po_unit = $(this).data('unit');
                $.ajax({
                    type: "GET",
                    url: "{{ route('kitting.viewKittingForm') }}",
                    data: {
                        'po_id': po_id,
                    },
                    success: function(response) {
                        $('#order-details-section').html(response.html);
                        $("#orderDetailsModal").find('.modal-title').html(
                            `<div class="d-flex align-items-center justify-content-between"><span>#${po_num}</span><span class="ml-auto">${po_desc} ${po_qty} ${po_unit}</span></div>`
                        ); //
                        $("#orderDetailsModal").modal('show');

                        $('#bom-table').DataTable({
                            "paging": false,
                            "ordering": true,
                            "info": false,
                            "dom": 'Bfrtip',
                            "columnDefs": [
                                {
                                    "targets": [9],
                                    "orderable": false
                                }
                            ],
                            "buttons": [
                                {
                                    extend: 'excel',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Production Order: #' + po_num,
                                },
                                {
                                    extend: 'pdf',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Production Order: #' + po_num,
                                },
                                {
                                    extend: 'print',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Production Order: #' + po_num,
                                },
                                'colvis',
                            ],
                            stateSave: true,
                        });
                        $('[data-toggle="tooltip"]').tooltip();
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred while fetching order details.');
                    }
                });
            });

            $(document).on('click', '.btn-issue-order', function() {
                var status = true;
                $('input[name="issue[]"]').each(function() {
                    var inputValue = $(this).val();
                    var maxAttributeValue = $(this).attr('max');
                    if (maxAttributeValue && inputValue > maxAttributeValue) {
                        toastr.error('Issue quantity cannot exceed maximum allowed quantity!');
                        status = false;
                        return false;
                    }
                });

                if (status) {
                    var formData = $('#issue-form').serialize();
                    $.ajax({
                        type: "POST",
                        url: "{{ route('kitting.issue') }}",
                        data: formData,
                        success: function(response) {
                            if (response.status) {
                                toastr.success('Material Issued Successfully.');
                                $("#orderDetailsModal").modal('hide');
                                $('#prod-orders').DataTable().ajax.reload();
                            } else {
                                if (response.message != undefined) {
                                    toastr.error(response.message);
                                } else if (response.error) {
                                    $.each(response.error, function (indexInArray, valueOfElement) { 
                                        toastr.error( valueOfElement.message );
                                    });
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            var jsonResponse = JSON.parse(xhr.responseText);
                            console.error(jsonResponse.message);
                            if (jsonResponse.message != undefined) {
                                toastr.error(jsonResponse.message);
                            } else if (jsonResponse.error) {
                                $.each(jsonResponse.error, function (indexInArray, valueOfElement) { 
                                    toastr.error( valueOfElement.message );
                                });
                            }
                            
                        }
                    });
                }
            });

            $(document).on('click', '.records-btn', function() {
                let po_id = $(this).data('id');
                let po_num = $(this).data('pon');
                let po_desc = $(this).data('desc');
                let po_qty = $(this).data('qty');
                let po_unit = $(this).data('unit');
                $.ajax({
                    type: "GET",
                    url: "{{ route('kitting.warehouseRecords') }}",
                    data: {
                        'po_id': po_id,
                    },
                    success: function(response) {
                        $('#records-section').html(response.html);
                        // $("#orderDetailsModal").find('.modal-title').html(
                        //     `<div class="d-flex align-items-center justify-content-between"><span>#${po_num}</span><span class="ml-auto">${po_desc} ${po_qty} ${po_unit}</span></div>`
                        // ); 
                        $("#recordsModal").modal('show');

                        $('#records-table').DataTable({
                            "paging": false,
                            "ordering": true,
                            "info": false,
                            "dom": 'Bfrtip',
                            "buttons": [
                                {
                                    extend: 'excel',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    // title: 'Production Order: #' + po_num,
                                },
                                {
                                    extend: 'pdf',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    // title: 'Production Order: #' + po_num,
                                },
                                {
                                    extend: 'print',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    // title: 'Production Order: #' + po_num,
                                },
                                'colvis',
                            ],
                            stateSave: true,
                        });

                        $('[data-toggle="tooltip"]').tooltip();
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred while fetching records.');
                    }
                });
            });

            $(document).on('click', '.reverse-btn', function(){

                var poid = $(this).data('poid');
                var matid = $(this).data('matid');

                Swal.fire({
                    title: "Reversing Item",
                    input: "number",
                    inputLabel: "Please enter quantity",
                    showCancelButton: true,
                    preConfirm: (quantity) => {
                        if (!quantity || isNaN(quantity) || quantity <= 0) {
                            Swal.showValidationMessage('Please enter a valid quantity');
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        var csrfToken = $('meta[name="csrf-token"]').attr('content');

                        $.ajax({
                            type: "POST",
                            url: "{{ route('kitting.reverse') }}",
                            data: {
                                _token: csrfToken,
                                po_id: poid,
                                material_id: matid,
                                reverse_qty: result.value
                            },
                            success: function(response) {
                                if (response.status) {
                                    toastr.success(response.message);
                                    $("#orderDetailsModal").modal('hide');
                                } else {
                                    if (response.message != undefined) {
                                        toastr.error(response.message);
                                    } else if (response.error) {
                                        $.each(response.error, function (indexInArray, valueOfElement) { 
                                            toastr.error( valueOfElement.message );
                                        });
                                    }
                                }
                            },
                            error: function(xhr, status, error) {
                                var jsonResponse = JSON.parse(xhr.responseText);
                                console.error(jsonResponse.message);
                                if (jsonResponse.message != undefined) {
                                    toastr.error(jsonResponse.message);
                                } else if (jsonResponse.error) {
                                    $.each(jsonResponse.error, function (indexInArray, valueOfElement) { 
                                        toastr.error( valueOfElement.message );
                                    });
                                }
                                
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop