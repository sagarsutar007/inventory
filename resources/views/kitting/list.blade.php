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
                            return '<button class="btn btn-link kitting-btn btn-sm" data-pon="' + row.po_number + '" data-id="' + row.po_id + '"><i class="fas fa-eye text-primary"></i></button>';
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
                $.ajax({
                    type: "GET",
                    url: "{{ route('kitting.viewKittingForm') }}",
                    data: {
                        'po_id': po_id,
                    },
                    success: function(response) {
                        $('#order-details-section').html(response.html);
                        $("#orderDetailsModal").find('.modal-title').text('Production Order: #' + po_num);
                        $("#orderDetailsModal").modal('show');

                        $('#bom-table').DataTable({
                            "paging": false,
                            "ordering": true,
                            "info": false,
                            "dom": 'Bfrtip',
                            "columnDefs": [
                                {
                                    "targets": [8],
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
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred while fetching order details.');
                    }
                });
            });

            $(document).on('click', '.btn-issue-order', function() {
                var formData = $('#issue-form').serialize();
                $.ajax({
                    type: "POST",
                    url: "{{ route('kitting.issue') }}",
                    data: formData,
                    success: function(response) {
                        $("#orderDetailsModal").modal('hide');
                        toastr.success('Material Issued Successfully.');
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred while saving issue.');
                    }
                });
            });
        });
    </script>
@stop