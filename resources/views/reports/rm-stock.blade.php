@extends('adminlte::page')

@section('title', 'Raw Material Stock Report')

@section('content')
    <div class="container">
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
                                <th>Stock Qty</th>
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
@stop

@section('js')
    <script>
        $(function () {
            $('#rm-stock-tbl').DataTable({
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
                    "url": "{{ route('raw.fetchRmStockList') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { "data": "part_code", "name": "part_code" },
                    { "data": "description", "name": "description" },
                    { "data": "commodity", "name": "commodity" },
                    { "data": "category", "name": "category" },
                    { "data": "stock", "name": "stock" },
                    { "data": "uom", "name": "uom_shortcode" },
                    { "data": "reorder_qty", "name": "reorder_qty" },
                    { "data": "reorder", "name": "reorder" },
                ],
                "lengthMenu": [
                    [-1, 10, 25, 50, 100],
                    ['All', 10, 25, 50, 100]
                ],
                "searching": true,
                "ordering": true,
                // "order": [[0, 'desc']],
                // "columnDefs": [
                //     {
                //         "targets": [7],
                //         "orderable": false
                //     }
                // ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
            });
        });
    </script>
@stop