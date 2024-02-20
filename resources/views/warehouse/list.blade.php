@extends('adminlte::page')

@section('title', 'Warehouse')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Warehouse</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-light dropdown-toggle dropdown-icon-disabled btn-sm" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" role="menu">
                                <a class="dropdown-item" href="{{ route('wh.issue') }}"><i class="fa fa-minus text-danger"></i> Issue</a>
                                <a class="dropdown-item" href="{{ route('wh.receive') }}"><i class="fa fa-plus text-primary"></i> Receive</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="materials" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <!-- <th width="5%">Sno.</th> -->
                                <th width="10%">Part Code</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit</th>
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
            $('#materials').DataTable({
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
                    "url": "{{ route('wh.getWarehouseRecords') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    // { "data": "sno", "name": "sno"},
                    { "data": "code", "name": "part_code" },
                    { "data": "material_name", "name": "description" },
                    { "data": "quantity", "name": "quantity" },
                    { "data": "unit", "name": "uom_text" },
                    { "data": "action" },
                ],
                "lengthMenu": [10, 25, 50, 75, 100],
                "searching": true,
                "ordering": true,
                "columnDefs": [
                    {
                        "targets": [4],
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

            $('#modalIssue').on('show.bs.modal', function (event) {
                initializeRawMaterialsSelect2($('#issue-material'));
            });

            $('#modalReceived').on('show.bs.modal', function (event) {
                initializeRawMaterialsSelect2($('#receive-material'));
            });

            function initializeRawMaterialsSelect2(selectElement) {
                selectElement.select2({
                    placeholder: 'Raw materials',
                    theme: 'bootstrap4',
                    ajax: {
                        url: '{{ route("semi.getRawMaterials") }}',
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                _token : '{{ csrf_token() }}',
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data, function (item) {
                                    return {
                                        id: item.material_id,
                                        text: item.description + "-" + item.part_code
                                    };
                                })
                            };
                        },
                        cache: true
                    }
                });
            }

            $('.btn-issue-material').click(function () {
                var _url = '{{ route('wh.issue') }}';
                var _formData = new FormData($('#issue-material-form')[0]);
                makeRequest(_url, _formData);

                $('#modalIssue').modal('hide');
            });

            $('.btn-receive-material').click(function () {
                var _url = '{{ route('wh.receive') }}';
                var _formData = new FormData($('#recieve-material-form')[0]);
                makeRequest(_url, _formData);
                $('#modalReceived').modal('hide');
            });

            function makeRequest(_url, _formData) {
                $.ajax({
                    url: _url, 
                    type: 'POST',
                    data: _formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status) {
                            toastr.success(response.message);
                            window.location.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                        var response = JSON.parse(xhr.responseText);
                        toastr.error(response.message);
                    }
                });
            }
        });
    </script>
@stop