@extends('adminlte::page')

@section('title', 'Raw Materials')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Raw Materials</h3>
                    <div class="card-tools">
                        @can('import-raw-vendor-price')
                        <a class="btn btn-light btn-sm" href="{{ route('raw.bulkPrice') }}"><i class="fas fa-file-import text-secondary"></i> Bulk Upload Price List</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="rawmaterials" class="table table-bordered table-striped" style="width:100%;">
                            <thead>
                                <tr id="material-header">
                                    {{-- <th>Sno.</th>
                                    <th>Part Code</th>
                                    <th>Description</th>
                                    <th>Commodity</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th>Make</th>
                                    <th>MPN</th>
                                    <th>Vendor 1</th>
                                    <th>Price 1</th>
                                    <th>Vendor 2</th>
                                    <th>Price 2</th>
                                    <th>Vendor 3</th>
                                    <th>Price 3</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                
                            </tbody>
                        </table>
                    </div>
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

            var table = $('#rawmaterials').DataTable({
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
                    "url": "{{ route('raw.fetchVendorPriceList') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    },
                    "success": function(resp){
                        console.log(resp);
                        // $("#material-header").append(`
                        //     <th>Sno.</th>
                        //     <th>Part Code</th>
                        //     <th>Description</th>
                        //     <th>Commodity</th>
                        //     <th>Category</th>
                        //     <th>Unit</th>
                        //     <th>Make</th>
                        //     <th>MPN</th>
                        //     <th>Vendor 1</th>
                        //     <th>Price 1</th>
                        //     <th>Vendor 2</th>
                        //     <th>Price 2</th>
                        //     <th>Vendor 3</th>
                        //     <th>Price 3</th>
                        // `);
                    }
                },
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
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { "data": "part_code", "name": "part_code" },
                    { "data": "description", "name": "description" },
                    { "data": "commodity", "name": "commodity_name" },
                    { "data": "category", "name": "category_name" },
                    { "data": "uom_shortcode", "name": "uom_shortcode" },
                    { "data": "make", "name": "make" },
                    { "data": "mpn", "name": "mpn" },
                    { "data": "vendor_1", "name": "vendor_1" },
                    { "data": "price_1", "name": "price_1" },
                    { "data": "vendor_2", "name": "vendor_2" },
                    { "data": "price_2", "name": "price_2" },
                    { "data": "vendor_3", "name": "vendor_3" },
                    { "data": "price_3", "name": "price_3" },
                ]
            });

        });
    </script>
@stop