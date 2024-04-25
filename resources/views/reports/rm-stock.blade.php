@extends('adminlte::page')

@section('title', 'Raw Material Stock Report')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2 mt-3">
                <form action="" id="material-search" method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>                                
                                <input type="text" id="daterange" class="form-control form-control-lg" placeholder="Select Range">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="search" id="term" class="form-control form-control-lg" placeholder="Type Partcode or Description here">
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
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list"></i>&nbsp;Raw Material Stock Report
                    </h3> 
                </div>
                <div class="card-body">
                    <table id="rm-stock-tbl" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>RM Part Code</th>
                                <th>Description</th>
                                <th>Commodity</th>
                                <th>Category</th>
                                <th>Opening</th>
                                <th>Issued</th>
                                <th>Received</th>
                                <th>Balance</th>
                                <th>Unit</th>
                                <th>Re Order Qty</th>
                                <th>Re Order</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-adminlte-modal id="view-stock" title="View Stock Quantity" icon="fas fa-box" size='xl' scrollable>
        <div class="row">
            <div class="col-12" id="view-stock-section">
                <h4 class="text-secondary text-center">Loading...</h4>
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
        $(function () {
            var currentUserName = "{{ auth()->user()->name }}";
            var userStamp = userTimeStamp(currentUserName);
            var safeStamp = $(userStamp).text();

            var currentDate = new Date();

            var currentYear = currentDate.getFullYear();
            var fiscalYearStartMonth = 4; 
            var fiscalYearStartDate = new Date(currentYear, fiscalYearStartMonth - 1, 1);
            
            $('#daterange').daterangepicker({
                timePicker: false,
                showDropdowns: true,
                locale: {
                    format: 'DD/MM/YYYY'
                },
                startDate: fiscalYearStartDate,
                endDate: currentDate
            });

            dataTable = $('#rm-stock-tbl').DataTable({
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
                        messageBottom: safeStamp,
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        },
                        messageBottom: safeStamp,
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        },
                        messageBottom: userStamp,
                    },
                    'colvis',
                ],
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                "ajax": {
                    "url": "{{ route('raw.fetchRmStockList') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                        d.startDate = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.endDate = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.searchTerm = $('#term').val();
                    }
                },
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { 
                        "data": "part_code", 
                        "name": "part_code",
                        "render": function ( data, type, row ) {
                            return `<button class="view-stock btn btn-link p-0" 
                            data-partcode="${data}" 
                            data-desc="${row.description}"
                            >${data}</button>`;
                        }
                     },
                    { "data": "description", "name": "description" },
                    { "data": "commodity", "name": "commodity" },
                    { "data": "category", "name": "category" },
                    { "data": "opening", "name": "opening" },
                    { "data": "issued", "name": "issued" },
                    { "data": "receipt", "name": "receipt" },
                    { "data": "stock", "name": "stock" },
                    { "data": "uom", "name": "uom_shortcode" },
                    { "data": "reorder_qty", "name": "reorder_qty" },
                    { "data": "reorder", "name": "reorder" },
                ],
                "lengthMenu": datatableLength,
                "searching": true,
                "ordering": true,
                // "order": [[0, 'desc']],
                "columnDefs": [
                    {
                        "targets": [6,7,11],
                        "orderable": false
                    },
                    {
                        "targets": [9],
                        "className": "dt-center"
                    },
                    {
                        "targets": [5,6,7,8,10],
                        "className": "dt-right"
                    }
                ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
            });

            $('#view-stock').on('hide.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                $("#view-stock-section").html(`
                    <h4 class="text-secondary text-center">Loading...</h4>
                `);
            });

            $(document).on('click', '.view-stock', function(e){
                e.preventDefault();
                var partcode = $(this).data('partcode');
                var description = $(this).data('desc');

                var startDate = $('#daterange').data('daterangepicker').startDate.format('DD-MM-YYYY');
                var endDate = $('#daterange').data('daterangepicker').endDate.format('DD-MM-YYYY');

                var title = "View Stock of " + partcode + " - " + description + " from " + startDate + " to " + endDate;

                $.ajax({
                    url: "{{ route('material.stockDetail') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        partcode: partcode,
                        startDate: $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD'),
                        endDate: $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD'),
                    },
                    success: function(response) {
                        if (response.status) {
                            var cleanedHtml = response.html.replace(/\\/g, '');
                            $("#view-stock-section").html(cleanedHtml);
                            $("#view-stock-qty").DataTable({
                                "responsive": true,
                                "lengthChange": false,
                                "autoWidth": true,
                                "paging": false,
                                "info": false,
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
                                "processing": false,
                                "searching": false,
                                "ordering": false,
                                "dom": 'Brt',
                                "language": {
                                    "lengthMenu": "_MENU_"
                                },
                                "lengthMenu": [
                                    [ -1, 10, 25, 50, 100],
                                    ['All', 10, 25, 50, 100]
                                ]
                            });
                            $("#view-stock").find('.modal-title').text(title);
                            $("#view-stock").modal('show');
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            $("#material-search").on('submit', function(e){
                e.preventDefault();
                var startDate = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                var endDate = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                var searchTerm = $('#term').val();
                var status = $("#status").val();
                
                var dataTable = $('#rm-stock-tbl').DataTable();
                
                dataTable.ajax.reload(null, false);
                dataTable.settings()[0].ajax.data = function (d) {
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.searchTerm = searchTerm;
                    d._token = '{{ csrf_token() }}';
                };
                
                dataTable.ajax.reload();
            });
        });
    </script>
@stop