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
                            <a href="{{ route('po.create') }}" class="btn btn-default btn-sm">Create</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="prod-order" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Production Number</th>
                                <th>Material</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created Date</th>
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
            $('#prod-order').DataTable({
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
                    "url": "{{ route('po.getProdOrderRecords') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    { "data": "po_number", "name": "po_number" },
                    { "data": "material_name", "name": "material_name" },
                    { "data": "quantity", "name": "quantity" },
                    { "data": "status", "name": "status" },
                    { "data": "created_by", "name": "created_by" },
                    { "data": "created_at", "name": "created_at" },
                    { "data": "action" },
                ],
                "lengthMenu": [10, 25, 50, 75, 100],
                "searching": true,
                "ordering": true,
                "columnDefs": [
                    {
                        "targets": [6],
                        "orderable": false
                    }
                ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
            });

            // Show Error Messages
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error('{{ $error }}');
                @endforeach
            @endif

            // Show Success Message
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
        });
    </script>
@stop