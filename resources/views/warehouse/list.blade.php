@extends('adminlte::page')

@section('title', 'Real Time Stock')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Real Time Stock</h3>
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
                                <th>Commodity</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Opening</th>
                                <th>Receipt</th>
                                <th>Issue</th>
                                <th>Stock Qty.</th>
                                <th>Re Order</th>
                                <th>RO Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-adminlte-modal id="view-stock" title="View Stock Quantity" icon="fas fa-box" size='xl' scrollable>
        <div class="row">
            <div class="col-12" id="view-stock-section">
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
                "stateSaveParams": function(settings, data) {
                    data.search.search = '';
                },
                "ajax": {
                    "url": "{{ route('wh.getWarehouseRecords') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    { 
                        "data": "code", 
                        "name": "part_code",
                        "render": function ( data, type, row ) {
                            return `<span class="view-stock text-primary p-0" 
                            data-partcode="${data}" 
                            data-desc="${row.material_name}"
                            >${data}</span>`;
                        }
                    },
                    { "data": "material_name", "name": "description" },
                    { "data": "commodity", "name": "commodity" },
                    { "data": "category", "name": "category" },
                    { "data": "unit", "name": "uom_text" },
                    { "data": "opening_balance", "name": "opening_balance" },
                    { "data": "receipt_qty", "name": "receipt_qty" },
                    { "data": "issue_qty", "name": "issue_qty" },
                    { "data": "closing_balance", "name": "closing_balance" },
                    { "data": "re_order", "name": "re_order" },
                    { "data": "re_order_status", "name": "re_order_status" },
                ],
                "lengthMenu": datatableLength,
                "searching": true,
                "ordering": true,
                "columnDefs": [
                    {
                        "targets": [5, 9],
                        "visible": false,
                    },
                    // {
                    //     "targets": [2],
                    //     "className": 'dt-center'
                    // },
                    {
                        "targets": [3,4,5,6,7],
                        "className": 'dt-right'
                    }
                ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
                "initComplete": function(settings, json) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
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

            $('#view-stock').on('hide.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                $("#view-stock-section").html(`
                    <h4 class="text-secondary text-center">Loading...</h4>
                `);
            });

            $(document).on('click', '.view-stock', function(e){
                e.preventDefault();
                var partcode = $(this).data('partcode');
                var description = $(this).data('desc');
                var today = new Date();
                var startDate = financialYear();
                var endDate = (today.getDate() < 10 ? '0' : '') + today.getDate() + '-' + ((today.getMonth() + 1) < 10 ? '0' : '') + (today.getMonth() + 1) + '-' + today.getFullYear();

                var title = `View Stock of <strong>` + partcode + `</strong> <br/> ` + description + `<br/> <strong>` + startDate + `</strong> to <strong>` + endDate + `</strong>`;

                $.ajax({
                    url: "{{ route('material.stockDetail') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        partcode: partcode,
                        startDate: startDate,
                        endDate: endDate,
                    },
                    success: function(response) {
                        if (response.status) {
                            var cleanedHtml = response.html.replace(/\\/g, '');
                            $("#view-stock-section").html(cleanedHtml);
                            $("#view-stock-qty").DataTable({
                                "responsive": true,
                                "lengthChange": false,
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
                                "ordering": false,
                                "dom": 'Brt',
                                "language": {
                                    "lengthMenu": "_MENU_"
                                },
                                "lengthMenu": [
                                    [ -1, 10, 25, 50, 100],
                                    ['All', 10, 25, 50, 100]
                                ]
                            });
                            $("#view-stock").find('.modal-title').addClass('text-center').html(title);
                            $("#view-stock").modal('show');
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            function financialYear(){
                var currentDate = new Date();
                var financialYearStartMonth = 3;                 
                var financialYearStartDay = 1;
                
                if (currentDate.getMonth() >= financialYearStartMonth && currentDate.getDate() >= financialYearStartDay) {
                    var startDate = new Date(currentDate.getFullYear(), financialYearStartMonth, financialYearStartDay);
                } else {
                    var startDate = new Date(currentDate.getFullYear() - 1, financialYearStartMonth, financialYearStartDay);
                }
                var formattedStartDate = (startDate.getDate() < 10 ? '0' : '') + startDate.getDate() + '-' + ((startDate.getMonth() + 1) < 10 ? '0' : '') + (startDate.getMonth() + 1) + '-' + startDate.getFullYear();
                console.log(formattedStartDate);
                return formattedStartDate;
            }
        });
    </script>
@stop