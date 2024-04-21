@extends('adminlte::page')

@section('title', 'Production Order Shortage Report')

@section('content')
    <div class="container-fluid">
        <!-- <h2 class="text-center display-4">Select Material and Daterange</h2> -->
        {{-- <div class="row">
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
                        </div><div class="col-md-8">
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
        </div> --}}
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list"></i>&nbsp;Production Order Shortage Report Consolidated
                    </h3> 
                </div>
                <div class="card-body">
                    <table id="po-report-tbl" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>RM Part Code</th>
                                <th>Description</th>
                                <th>Make</th>
                                <th>MPN</th>
                                <th><div title="Consolidated PO Quantity">CPO Qty</div></th>
                                <th>Stock Qty</th>
                                <th><div title="Balance to Issue Quantity">BTI Qty</div></th>
                                <th>Shortage Qty</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-adminlte-modal id="view-modal" title="View Details" icon="fas fa-eye" size='xl' scrollable>
        <div id="view-details">

        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>
@stop

@section('js')
    <script>
        $(function(){

            var currentdate = new Date();
            var datetime = "Generated on: " + currentdate.getDate() + "-"
                        + (currentdate.getMonth()+1)  + "-"
                        + currentdate.getFullYear() + " " 
                        + currentdate.getHours() + ":" 
                        + currentdate.getMinutes() + ":"
                        + currentdate.getSeconds();


            $('#daterange').daterangepicker({
                timePicker: false,
                showDropdowns: true,
                locale: {
                    format: 'DD/MM/YYYY'
                }
            });

            $('#po-report-tbl').DataTable({
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
                "ajax": {
                    "url": "{{ route('po.fetchPoConsolidatedShortageReport') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                        d.startDate = ""; // $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.endDate = ""; // $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        // d.searchTerm = $('#term').val();
                        // d.status = $("#status").val();
                    }
                },
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { 
                        "data": "part_code", 
                        "name": "part_code",
                        "render": function(data, type, row, meta) {
                            return '<a href="#" data-partcode="'+data+'" data-description="'+row.description+'" data-toggle="modal" data-target="#view-modal">' + data + '</a>';
                        }
                    },
                    { "data": "description", "name": "description" },
                    { "data": "make", "name": "make" },
                    { "data": "mpn", "name": "mpn" },
                    { "data": "quantity", "name": "quantity" },
                    { "data": "stock", "name": "stock" },
                    { "data": "balance", "name": "balance" },
                    { "data": "shortage", "name": "shortage",
                        "render": function(data, type, row, meta) {
                            if (row.stock >= row.balance) {
                                return "0.000";
                            } else {
                                return Math.abs(row.stock - row.balance);
                            }
                        }
                    },
                    { "data": "unit", "name": "unit" },
                ],
                "lengthMenu": datatableLength,
                "columnDefs": [
                    {
                        "targets": [0],
                        "orderable": false
                    },
                    {
                        "targets": [5,6,7,8],
                        "className": 'dt-right'
                    },
                    {
                        "targets": [9],
                        "className": 'dt-center'
                    }
                ],
                "searching": true,
                "ordering": true,
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
            });

            $("#material-search").on('submit', function(e){
                e.preventDefault();
                var startDate = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                var endDate = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                var searchTerm = $('#term').val();
                var status = $("#status").val();
                
                var dataTable = $('#po-report-tbl').DataTable();
                
                dataTable.ajax.reload(null, false);
                dataTable.settings()[0].ajax.data = function (d) {
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.searchTerm = searchTerm;
                    d._token = '{{ csrf_token() }}';
                };
                
                dataTable.ajax.reload();
            });

            $(document).on('show.bs.modal', '#view-modal', function (event) {
                var button = $(event.relatedTarget);
                var partcode = button.data('partcode');
                var description = button.data('description');
                // var startDate = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                // var endDate = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');

                var modal = $(this);
                modal.find('.modal-title').text('View: #' + partcode + ' - ' + description);

                $.ajax({
                    url: "{{ route('po.fetchMaterialShortageConsolidated') }}",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        partcode: partcode,
                        // startDate: startDate,
                        // endDate: endDate,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#view-details').html(response.html);
                            $('#material-shortage-tbl').DataTable({
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
                                "scrollY": "320px",
                                "scrollCollapse": true,
                                "lengthMenu": [10, 25, 50, 75, 100],
                                "searching": true,
                                "ordering": true,
                                "dom": 'lBfrtip',
                                "language": {
                                    "lengthMenu": "_MENU_"
                                },
                            });
                        } else {
                            console.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });

            });

            
        })
    </script>
@stop