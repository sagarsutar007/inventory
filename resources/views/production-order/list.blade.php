@extends('adminlte::page')

@section('title', 'Production Orders')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Production Orders</h3>
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
                            return '<button class="btn btn-link view-btn btn-sm" data-id="' + row.po_id + '"><i class="fas fa-eye text-primary"></i></button>' + '/ <button class="btn btn-link delete-btn btn-sm" data-id="' + row.po_id + '"><i class="fas fa-trash text-danger"></i></button>';
                        }
                    }
                ],
                "lengthMenu": [10, 25, 50, 75, 100],
                "searching": true,
                "ordering": true,
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