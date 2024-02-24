@extends('adminlte::page')

@section('title', 'Transactions')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Transactions</h3>
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
                                <th>Transaction ID</th>
                                <th>Vendor</th>
                                <th>PO / Purchase Number</th>
                                <th>Type</th>
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

    <x-adminlte-modal id="modalView" title="View" icon="fas fa-eye">
        <div class="row">
            <div class="col-md-12" id="view-transaction-section">
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <x-adminlte-modal id="modalEdit" title="Edit Transaction" icon="fas fa-edit" size='lg'>
        <div class="row">
            <div class="col-md-12" id="edit-transaction-section">
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-transaction" theme="outline-primary" label="Save"/>
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
                "scrollY": "320px",
                "scrollCollapse": true,
                "ajax": {
                    "url": "{{ route('wh.getWarehouseTransactions') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    // { "data": "sno", "name": "sno"},
                    { "data": "transaction_id", "name": "transaction_id" },
                    { "data": "vendor", "name": "vendor" },
                    { "data": "popn", "name": "popn" },
                    { "data": "type", "name": "type" },
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

            $('#modalEdit').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var transId = button.data('warehouseid');
                var type = button.data('type');
                var modal = $(this)

                if (type === 'issue') {
                    _url = '/app/warehouse/' + transId + '/editIssue';
                } else {
                    _url = '/app/warehouse/' + transId + '/editReceipt';
                }
                
                $.ajax({
                    url: _url,
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $("#edit-transaction-section").html(response.html);
                            $(".select2").select2({
                                placeholder: 'Choose Vendor',
                                theme: 'bootstrap4'
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            $('.btn-save-transaction').on('click', function(){
                event.preventDefault();
                var formData = $('#update-form').serialize();

                // Send an AJAX request
                $.ajax({
                    url: $('#update-form').attr('action'), 
                    type: 'POST',
                    data: formData, 
                    success: function(response) {
                        // Handle success response
                        console.log(response);
                        // Close the modal if needed
                        $('#modalEdit').modal('hide');
                        // You can add additional logic here, such as showing a success message or refreshing the page
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        console.error(error);
                        // You can show an error message or handle the error as needed
                    }
                });
            });

        });
    </script>
@stop