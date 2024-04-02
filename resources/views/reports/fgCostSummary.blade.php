@extends('adminlte::page')

@section('title', 'Bill of Material Cost View')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list"></i>&nbsp;Finished Good Cost Summary
                    </h3> 
                </div>
                <div class="card-body">
                    <table id="fg-cost-tbl" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>FG Part Code</th>
                                <th>Description</th>
                                <th>Commodity</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>BOM Cost Lowest</th>
                                <th>BOM Cost Average</th>
                                <th>BOM Cost Highest</th>
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
        $(function(){
            $('#fg-cost-tbl').DataTable({
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
                    "url": "{{ route('bom.fetchFgCostSummary') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { "data": "code", "name": "part_code" },
                    { "data": "material_name", "name": "description" },
                    { "data": "commodity", "name": "commodity" },
                    { "data": "category", "name": "category" },
                    { "data": "unit", "name": "uom_text" },
                    { "data": "lowest", "name": "lowest" },
                    { "data": "average", "name": "average" },
                    { "data": "highest", "name": "highest" }
                ],
                "lengthMenu": [10, 25, 50, 75, 100],
                "searching": true,
                "ordering": true,
                "order": [[0, 'desc']],
                "columnDefs": [
                    {
                        "targets": [6,7,8],
                        "orderable": false
                    }
                ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
            });
        })
    </script>
@stop