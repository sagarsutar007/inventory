@extends('adminlte::page')

@section('title', 'Production Order Report')

@section('content')
    <div class="container-fluid">
        <!-- <h2 class="text-center display-4">Select Material and Daterange</h2> -->
        <div class="row">
            <div class="col-md-8 offset-md-2 mt-3">
                <form action="" id="material-search" method="post">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>                                
                                <input type="text" id="daterange" class="form-control form-control-lg" placeholder="Select Range">
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
                        <i class="fas fa-clipboard-list"></i>&nbsp;Production Order Report
                    </h3> 
                </div>
                <div class="card-body">
                    <table id="po-report-tbl" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>PO No</th>
                                <th>PO Date</th>
                                <th>FG Part Code</th>
                                <th>PO Quantity</th>
                                <th>Unit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        var dataTable 
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

            dataTable =  $('#po-report-tbl').DataTable({
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
                    "url": "{{ route('po.fetchPoReport') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                        d.startDate = ""; // $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD')
                        d.endDate = ""; // $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD')
                        d.searchTerm = $('#term').val();
                        d.status = $("#status").val();
                    }
                },
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { "data": "po_number", "name": "po_number" },
                    { "data": "po_date", "name": "po_date" },
                    { "data": "part_code", "name": "part_code" },
                    { "data": "quantity", "name": "quantity" },
                    { "data": "unit", "name": "unit" },
                    { "data": "status", "name": "status" },
                ],
                "lengthMenu": [10, 25, 50, 75, 100],
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
        })
    </script>
@stop