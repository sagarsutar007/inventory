@extends('adminlte::page')

@section('title', 'Raw Material Issuance Report')

@section('content')
    <div class="container-fluid">
        <!-- <h2 class="text-center display-4">Select Material and Daterange</h2> -->
        <div class="row">
            <div class="col-md-8 offset-md-2 mt-3">
                <form action="" id="material-search" method="post">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </div>                                
                                <input type="text" id="daterange" class="form-control form-control-lg" placeholder="Select Range">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="search" id="term" class="form-control form-control-lg" placeholder="Type your keywords here">
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
                        <i class="fas fa-clipboard-list"></i>&nbsp;Raw Material Issuance Report
                    </h3> 
                </div>
                <div class="card-body">
                    <table id="rm-pur-tbl" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>RM Part Code</th>
                                <th>Description</th>
                                <th>Commodity</th>
                                <th>Category</th>
                                <th>Issue Date</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Price 3</th>
                                <th>Amount</th>
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

            $('#material-search').submit(function(e) {
                e.preventDefault(); // Prevent default form submission

                // Get form values
                var startDate = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                var endDate = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                var searchTerm = $('#term').val();

                // Reload DataTable with new parameters
                $('#rm-pur-tbl').DataTable().ajax.reload(null, false);
            });

            $('#rm-pur-tbl').DataTable({
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
                "lengthMenu": datatableLength,
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                "ajax": {
                    "url": "{{ route('raw.fetchPurchaseList') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                        d.startDate = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.endDate = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.searchTerm = $('#term').val();
                        d.type = "issued";
                    }
                },
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { "data": "part_code", "name": "part_code" },
                    { "data": "description", "name": "description" },
                    { "data": "commodity", "name": "commodity" },
                    { "data": "category", "name": "category" },
                    { "data": "receipt_date", "name": "receipt_date" },
                    { "data": "quantity", "name": "quantity" },
                    { "data": "unit", "name": "unit" },
                    { "data": "price_3", "name": "price_3" },
                    { "data": "amount", "name": "amount" },
                ],
                "searching": true,
                "ordering": true,
                // "order": [[0, 'desc']],
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
        });
    </script>
@stop