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
                                <th width="12%">Transaction ID</th>
                                <th>Vendor</th>
                                <th>PO / Purchase Number</th>
                                <th>Type</th>
                                <th>Reason</th>
                                <th>Date</th>
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

    <x-adminlte-modal id="modalView" title="View" icon="fas fa-eye" size='lg'>
        <div class="row">
            <div class="col-md-12" id="view-transaction-section">
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="info" label="Print & Save" id="printAndSaveBtn"/>
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <x-adminlte-modal id="modalEdit" title="Edit Transaction" icon="fas fa-edit" size='lg'>
        <div class="row">
            <div class="col-md-12" id="edit-transaction-section">
            </div>
        </div>
        <x-slot name="footerSlot">
            <button type="button" class="btn btn-sm btn-secondary add-material-quantity-item"><i class="fas fa-fw fa-plus"></i> Add Item </button>
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-transaction" theme="primary" label="Save"/>
        </x-slot>
    </x-adminlte-modal>
@stop

@section('js')
    <script>
        $(function () {
            $('#materials').DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
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
                    { "data": "reason", "name": "reason" },
                    { "data": "date", "name": "date" },
                    { "data": "action" },
                ],
                "lengthMenu": datatableLength,
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

            $('#modalView').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var warehouseId = button.data('warehouseid');
                var type = button.data('type');
                var transId = button.data('transactionid');
                var modal = $(this);
                

                $.ajax({
                    url: '/app/warehouse/' + warehouseId + '/viewTransaction',
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            var title = "";
                            if (response.material.length > 0) {
                                title += " - " + response.material + " (<b>" + response.quantity + "</b>)";
                            }

                            modal.find('.modal-title').html("Transaction Details: # <b>" + transId + "</b>" + title);
                            $("#view-transaction-section").html(response.html);
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
                $.ajax({
                    url: $('#update-form').attr('action'), 
                    type: 'POST',
                    data: formData, 
                    success: function(response) {
                        console.log(response);
                        $('#modalEdit').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

            $('#printAndSaveBtn').click(function() {
                var content = $('#view-transaction-section').html();
                var printWindow = window.open('', '_blank');
                printWindow.document.open();
                printWindow.document.write('<html><head><title>Print</title></head><body>' + content + '</body></html>');
                printWindow.document.close();
                printWindow.print();
            });

            $(".add-material-quantity-item").click(function () {
                addMaterialQuantityItem();
            });
            
            function addMaterialQuantityItem() {
                var newItem = `
                    <div class="material-with-quantity">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="text" name="part_code[]" class="form-control suggest-partcode" placeholder="Partcode">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <input type="text" class="form-control material-name" placeholder="Material name" disabled>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="text" class="form-control material-unit" placeholder="Unit" disabled>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input name="quantity[]" type="number" class="form-control quantity" placeholder="Qty." step="0.001">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-times remove-material-quantity-item"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>                            
                    </div>
                `;
                var $newItem = $(newItem);
                $(".material-quantity-container").append($newItem);

                // Initialize autocomplete for the newly added item
                initializeAutocomplete($newItem.find(".suggest-partcode"));
            }

            function initializeAutocomplete(element) {
                element.autocomplete({
                    source: function (request, response) {
                        var existingPartCodes = $('.suggest-partcode').map(function() {
                            return request.term != this.value ? this.value : null;
                        }).get();

                        $.ajax({
                            url: "{{ route('wh.getMaterials') }}",
                            method: "POST",
                            dataType: "json",
                            data: {
                                term: request.term,
                                existingPartCodes: existingPartCodes
                            },
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                            },
                            success: function (data) {
                                response(data.map(function (item) {
                                    return {
                                        label: item.value,
                                        value: item.value,
                                        unit: item.unit,
                                        desc: item.desc,
                                        closing_bal: item.closing_balance,
                                    };
                                }));
                            },
                        });
                    },
                    minLength: 2,
                    appendTo: "#modalEdit",
                    focus: function (event, ui) {
                        if (ui.item.closing_bal > 0 && $('#form-type').val() === 'issue') {
                            element.val(ui.item.label);
                        } else if ($('#form-type').val() === 'receipt') {
                            element.val(ui.item.label);
                        }
                        return false;
                    },
                    select: function (event, ui) {
                        if (ui.item.closing_bal > 0 && $('#form-type').val() === 'issue') {
                            element.val(ui.item.label);
                            element.closest('.material-with-quantity').find('.material-name').val(ui.item.desc);
                            element.closest('.material-with-quantity').find('.material-unit').val(ui.item.unit);
                            element.closest(".material-with-quantity").find(".quantity").attr('max', ui.item.closing_bal);
                        } else if ($('#form-type').val() === 'receipt') {
                            element.val(ui.item.label);
                            element.closest('.material-with-quantity').find('.material-name').val(ui.item.desc);
                            element.closest('.material-with-quantity').find('.material-unit').val(ui.item.unit);
                            element.closest(".material-with-quantity").find(".quantity").attr('max', ui.item.closing_bal);
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "ERROR",
                                text: "This item can't be issued due to no closing balance",
                            });
                        }
                        return false;
                    },
                }).autocomplete("instance")._renderItem = function (ul, item) {
                    // <=0 show text-danger
                    if (item.closing_bal <= 0 && $('#form-type').val() === 'issue') {
                        return $("<li>")
                        .append("<div class=\"bg-danger\">" + item.label + " - " + item.desc + "</div>")
                        .appendTo(ul);
                    } else {
                        return $("<li>")
                        .append("<div>" + item.label + " - " + item.desc + "</div>")
                        .appendTo(ul);
                    }
                    
                };
            }

            $(document).on('click', '.remove-material-quantity-item', function () {
                if ($('.material-with-quantity').length > 1) {
                    $(this).closest('.material-with-quantity').remove();
                } else {
                    alert("At least one material & quantity item should be present.");
                }
            });

        });
    </script>
@stop