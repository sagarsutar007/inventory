@extends('adminlte::page')

@section('title', 'Finished Good Cost Summary')

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

    <x-adminlte-modal id="view-modal" title="View BOM Cost" icon="fas fa-box" size='xl' scrollable>
        <div class="row">
            <div class="col-12" id="view-modal-section">
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
        $(function(){
            var currentUserName = "{{ auth()->user()->name }}";
            var userStamp = userTimeStamp(currentUserName);
            var safeStamp = $(userStamp).text();

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
                        messageBottom: safeStamp,
                    },
                    'colvis',
                ],
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                "ajax": {
                    "url": "{{ route('bom.fetchFgCostSummary') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    { "data": "serial", "name": "serial" },
                    { 
                        "data": "code", 
                        "name": "part_code",
                        "render": function(data, type, row, meta) {
                            return '<a href="#" data-partcode="'+data+'" data-description="'+row.material_name+'" data-unit="'+row.unit+'" data-toggle="modal" data-target="#view-modal">' + data + '</a>';
                        }
                    },
                    { "data": "material_name", "name": "description" },
                    { "data": "commodity", "name": "commodity" },
                    { "data": "category", "name": "category" },
                    { "data": "unit", "name": "uom_text" },
                    { "data": "lowest", "name": "lowest" },
                    { "data": "average", "name": "average" },
                    { "data": "highest", "name": "highest" }
                ],
                "lengthMenu": datatableLength,
                "searching": true,
                "ordering": true,
                // "order": [[0, 'desc']],
                "columnDefs": [
                    {
                        "targets": [0],
                        "orderable": false
                    },
                    {
                        "targets": [6,7,8],
                        "className": 'dt-right'
                    }
                ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
            });
            
            $('#view-modal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var partcode = button.data('partcode');
                var description = button.data('description');
                var unit = button.data('unit');
                var modal = $(this);

                modal.find('.modal-title').text("View BOM Cost of #" + partcode + "-" + description + "(" + unit + ")");

                var part_code_array = [partcode];
                var quantity_array = [1];

                // Creating data object
                var data = {
                    _token: '{{ csrf_token() }}',
                    part_code: part_code_array,
                    quantity: quantity_array
                };

                // Convert data object to JSON string
                var jsonData = data;

                $.ajax({
                    url: "{{ route('bom.getBomRecords') }}",
                    method: 'POST',
                    data: jsonData,
                    success: function(response) {
                        if (response.status) {
                            $("#view-modal-section").html(response.html);

                            $("#bom-table").DataTable({
                                "responsive": true,
                                "lengthChange": true,
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
                                "ordering": true,
                                "dom": 'lBfrtip',
                                "language": {
                                    "lengthMenu": "_MENU_"
                                },
                                "lengthMenu": [
                                    [ -1, 10, 25, 50, 100],
                                    ['All', 10, 25, 50, 100]
                                ]
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            $('#view-modal').on('hide.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                $("#view-modal-section").html(`
                    <h4 class="text-secondary text-center">Loading...</h4>
                `);
            });
        })
    </script>
@stop