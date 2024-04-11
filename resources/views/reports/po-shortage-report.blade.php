@extends('adminlte::page')

@section('title', 'Production Order Shortage Report')

@section('content')
    {{-- <div class="container-fluid">
        <h2 class="text-center display-4">Select Material and Daterange</h2>
        <div class="row">
            <div class="col-md-8 offset-md-2 mt-3">
                <form action="" id="material-search" method="post">
                    <div class="row">
                        <div class="col-md-4 mx-auto">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>                                
                                <input type="text" id="daterange" class="form-control form-control-lg" placeholder="Select Range">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-lg btn-default">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="status" id="status" class="form-control form-control-lg">
                                <option value="">All</option>
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="Partially Issued">Partially Issued</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="search" id="term" class="form-control form-control-lg" placeholder="Type Production Order Number">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-lg btn-default">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list"></i>&nbsp;Production Order Shortage Report
                    </h3> 
                </div>
                <div class="card-body">
                    <table id="po-report-tbl" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>PO No</th>
                                <th>PO Date</th>
                                <th>FG Partcode</th>
                                <th>RM Partcode</th>
                                <th>Description</th>
                                <th>Make</th>
                                <th>MPN</th>
                                <th>Unit</th>
                                <th><div title="Consolidated PO Quantity">CPO Qty</div></th>
                                <th>Issued Qty</th>
                                <th><div title="Balance to Issue Quantity">BTI Qty</div></th>
                                <th>Stock Qty</th>
                                <th>Shortage Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-adminlte-modal id="orderDetailsModal" title="Order Details" icon="fas fa-info-circle" size='xl'>
        <div class="row">
            <div class="col-md-12" id="order-details-section">
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
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
        var dataTable;
        $(function(){
            $('#daterange').daterangepicker({
                timePicker: false,
                showDropdowns: true,
                locale: {
                    format: 'DD/MM/YYYY'
                }
            });
            dataTable = $('#po-report-tbl').DataTable({
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
                        },
                        "messageBottom": datetime,
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        },
                        "messageBottom": datetime,
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        },
                        "messageBottom": datetime,
                    },
                    'colvis',
                ],
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                "scrollY": "320px",
                "scrollCollapse": true,
                "ajax": {
                    "url": "{{ route('po.fetchPoShortageReport') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                        // d.startDate = ""; //$('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD')
                        // d.endDate = ""; //$('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD')
                        // d.searchTerm = $('#term').val();
                    }
                },
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { 
                        "data": "po_number", 
                        "name": "po_number",
                        "render": function(data, type, row, meta) {
                            return '<a href="#" data-poid="'+row.po_id+'" data-ponum="'+data+'" class="view-btn">' + data + '</a>';
                        }
                    },
                    { "data": "po_date", "name": "po_date" },
                    { "data": "fg_partcode", "name": "fg_partcode" },
                    { "data": "part_code", "name": "part_code" },
                    { "data": "description", "name": "description" },
                    { "data": "make", "name": "make" },
                    { "data": "mpn", "name": "mpn" },
                    { "data": "unit", "name": "unit" },
                    { "data": "quantity", "name": "quantity" },
                    { 
                        "data": "issued", 
                        "name": "issued",
                        "render": function(data, type, row, meta) {
                            return '<a href="#" data-poid="'+row.po_id+'" data-ponum="'+row.po_number+'" data-item="'+ row.part_code +'"  class="records-btn">' + data + '</a>';
                        }
                    },
                    { "data": "balance", "name": "balance" },
                    { "data": "stock", "name": "stock" },
                    { "data": "shortage", "name": "shortage" },
                    
                    // { "data": "status", "name": "status" },
                ],
                "lengthMenu": [10, 25, 50, 75, 100],
                "searching": true,
                "ordering": true,
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
            });

            // $("#material-search").on('submit', function(e){
            //     e.preventDefault();
            //     var startDate = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
            //     var endDate = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
            //     var searchTerm = $('#term').val();
            //     var status = $("#status").val();
                
            //     var dataTable = $('#po-report-tbl').DataTable();
                
            //     dataTable.ajax.reload(null, false);
            //     dataTable.settings()[0].ajax.data = function (d) {
            //         d.startDate = startDate;
            //         d.endDate = endDate;
            //         d.searchTerm = searchTerm;
            //         d._token = '{{ csrf_token() }}';
            //     };
                
            //     dataTable.ajax.reload();
            // });

            $(document).on('click', '.view-btn', function() {
                let po_num = $(this).data('ponum');
                let po_id = $(this).data('poid');
                $.ajax({
                    type: "GET",
                    url: "{{ route('po.viewOrder') }}",
                    data: {
                        'po_id': po_id,
                    },
                    success: function(response) {

                        var po_desc = response.info.material.description;
                        var po_qty = response.info.quantity;
                        var po_unit = response.info.material.uom.uom_shortcode;

                        $('#order-details-section').html(response.html);
                        $("#orderDetailsModal").modal('show');
                        $("#orderDetailsModal").find('.modal-title').html(
                            `<div class="d-flex align-items-center justify-content-between"><span>#${po_num}</span><span class="ml-auto">${po_desc} ${po_qty} ${po_unit}</span></div>`
                        );
                        $("#bom-table").DataTable({
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
                                    title: 'Production Order: #' + po_num + "-" + po_desc + "(" + po_qty + po_unit + ")",
                                },
                                {
                                    extend: 'pdf',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Production Order: #' + po_num + "-" + po_desc + "(" + po_qty + po_unit + ")",
                                },
                                {
                                    extend: 'print',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Production Order: #' + po_num + "-" + po_desc + "(" + po_qty + po_unit + ")",
                                },
                                'colvis',
                            ],
                            "initComplete": function (settings, json) {
                                // Search for the item upon initialization
                                // if (item) {
                                    // this.api().search(item).draw();
                                // }
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        console.error(jsonResponse.message);
                        toastr.error(jsonResponse.message);
                    }
                });
            });

            $(document).on('click', '.records-btn', function() {
                let po_id = $(this).data('poid');
                let po_num = $(this).data('ponum');
                let item = $(this).data('item');
                
                $.ajax({
                    type: "GET",
                    url: "{{ route('kitting.warehouseRecords') }}",
                    data: {
                        'po_id': po_id,
                    },
                    success: function(response) {

                        let po_desc = response.info.material.description;
                        let po_partcode = response.info.material.part_code;
                        let po_qty = response.info.quantity;
                        let po_unit = response.info.material.uom.uom_shortcode;
                        let po_status =  response.info.status;

                        let status = showKittingStatus(po_status);

                        $('#records-section').html(response.html);

                        $("#recordsModal").find('.modal-title').html(
                            `<div class="d-flex align-items-center justify-content-between">
                                <span> Issue/Reciept PO: #${po_num} - ${po_partcode} - ${po_desc} - ${po_qty} ${po_unit}</span>
                                ${status}
                            </div>`
                        ); 

                        $("#recordsModal").modal('show');

                        $('#records-table').DataTable({
                            "paging": false,
                            "ordering": true,
                            "info": false,
                            "dom": 'Bfrtip',
                            "autoWidth": true,
                            "buttons": [
                                {
                                    extend: 'excel',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Kitting Issue/Reciept: #' + po_num,
                                },
                                {
                                    extend: 'pdf',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Kitting Issue/Reciept: #' + po_num,
                                },
                                {
                                    extend: 'print',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Kitting Issue/Reciept: #' + po_num,
                                },
                                'colvis',
                            ],
                            stateSave: true,
                            order: [[2, 'desc']],
                            "initComplete": function (settings, json) {
                                // Search for the item upon initialization
                                if (item) {
                                    this.api().search(item).draw();
                                }
                            }
                        });

                        $('[data-toggle="tooltip"]').tooltip();
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred while fetching records.');
                    }
                });
            });
        })
    </script>
@stop