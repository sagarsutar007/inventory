@extends('adminlte::page')

@section('title', 'Raw Material Price List Report')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list"></i>&nbsp;Raw Material Price List Report
                    </h3> 
                </div>
                <div class="card-body">
                    <table id="rm-price-tbl" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>RM Part Code</th>
                                <th>Description</th>
                                <th>Commodity</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Make</th>
                                <th>Mpn</th>
                                <th>Price 1</th>
                                <th>Price 2</th>
                                <th>Price 3</th>
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

            $('#rm-price-tbl').DataTable({
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
                        messageBottom: userStamp,
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        },
                        messageBottom: userStamp,
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
                    "url": "{{ route('raw.fetchPriceList') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { "data": "part_code", "name": "part_code" },
                    { "data": "description", "name": "description" },
                    { "data": "commodity", "name": "commodity_name" },
                    { "data": "category", "name": "category_name" },
                    { "data": "uom_shortcode", "name": "uom_shortcode" },
                    { "data": "make", "name": "make" },
                    { "data": "mpn", "name": "mpn" },
                    { "data": "price_1", "name": "min_price" },
                    { "data": "price_2", "name": "avg_price" },
                    { "data": "price_3", "name": "max_price" }
                ],
                "lengthMenu": [10, 25, 50, 75, 100],
                "searching": true,
                "ordering": true,
                // "order": [[0, 'desc']],
                "columnDefs": [
                    {
                        "targets": [0],
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