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
                            <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#createOrderModal">Create</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="prod-orders" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>FG Partcode</th>
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

    <x-adminlte-modal id="orderDetailsModal" title="Order Details" icon="fas fa-info-circle" size='xl'>
        <div class="row">
            <div class="col-md-12" id="order-details-section">
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <x-adminlte-modal id="createOrderModal" title="Create Production Order" icon="fas fa-plus" size='lg'>
        <form action="" id="create-order-form" action="post">
            @csrf
            <div class="goods-container">
                <div class="goods-item">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <input type="text" name="part_code[]" class="form-control suggest-goods" placeholder="Part code">
                        </div>
                        <div class="col-md-5 mb-3">
                            <input type="text" class="form-control material-description" placeholder="Description" readonly>
                        </div>
                        <div class="col-md-2 mb-3">
                            <input type="text" class="form-control material-unit" placeholder="UOM" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="input-group">
                                <input type="number" name="quantity[]" class="form-control quantity" step="0.001" placeholder="Quantity">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-times remove-finished-goods"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-create-order" theme="primary" label="Save"/>
        </x-slot>
    </x-adminlte-modal>
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
                "ajax": {
                    "url": "{{ route('po.get') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    { "data": "po_number", "name": "po_number" },
                    { "data": "fg_partcode", "name": "fg_partcode" },
                    { "data": "description", "name": "description" },
                    { "data": "unit", "name": "uom_shortcode" },
                    { "data": "quantity", "name": "quantity" },
                    { "data": "created_at", "name": "created_at" },
                    { "data": "created_by", "name": "created_by" },
                    { "data": "status", "name": "status" },
                    { 
                        "data": null,
                        "render": function ( data, type, row ) {
                            return '<button class="btn btn-link view-btn btn-sm p-0" data-id="' + row.po_id + '"  data-pon="' + row.po_number + '" data-desc="'+ row.description +'" data-qty="'+ row.quantity +'" data-unit="'+ row.unit +'"><i class="fas fa-eye text-primary"></i></button>' 
                            @can('delete-po')
                            + ' / <button class="btn btn-link delete-btn btn-sm p-0" data-id="' + row.po_id + '" data-pon="' + row.po_number + '" data-desc="'+ row.description +'" data-qty="'+ row.quantity +'" data-unit="'+ row.unit +'"><i class="fas fa-trash text-danger"></i></button>'
                            @endcan
                            ;
                        }
                    }
                ],
                "lengthMenu": datatableLength,
                "searching": true,
                "ordering": true,
                "order": [[0, 'desc']],
                "columnDefs": [
                    {
                        "targets": [8],
                        "orderable": false
                    },
                    {
                        "targets": [4],
                        "className": 'dt-right'
                    },
                    {
                        "targets": [3],
                        "className": 'dt-center'
                    }
                ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
            });

            $(document).on('click', '.delete-btn', function() {
                let po_id = $(this).data('id');
                $.ajax({
                    type: "DELETE",
                    url: "{{ route('po.removeOrder') }}",
                    data: {
                        'po_id': po_id,
                        '_token': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        $('#prod-orders').DataTable().ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred';
                        toastr.error(errorMessage);
                    }
                });
            });

            $(document).on('click', '.view-btn', function() {
                let po_id = $(this).data('id');
                let po_num = $(this).data('pon');
                let po_desc = $(this).data('desc');
                let po_qty = $(this).data('qty');
                let po_unit = $(this).data('unit');
                $.ajax({
                    type: "GET",
                    url: "{{ route('po.viewOrder') }}",
                    data: {
                        'po_id': po_id,
                    },
                    success: function(response) {
                        $('#order-details-section').html(response.html);
                        $("#orderDetailsModal").modal('show');
                        $("#orderDetailsModal").find('.modal-title').html(
                            `<div class="d-flex align-items-center justify-content-between"><span>#${po_num}</span><span class="ml-auto">${po_desc} ${po_qty} ${po_unit}</span></div>`
                        );
                        $("#bom-table").DataTable({
                            "paging": false,
                            "ordering": true,
                            "info": false,
                            "dom": 'Bfrtip',
                            "columnDefs": [
                                // {
                                //     "targets": [9],
                                //     "orderable": false
                                // }
                            ],
                            "buttons": [
                                {
                                    extend: 'excel',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Production Order: #' + po_num + "-" + po_desc + "(" + po_qty + po_unit + ")",
                                },
                                {
                                    extend: 'pdf',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Production Order: #' + po_num + "-" + po_desc + "(" + po_qty + po_unit + ")",
                                },
                                {
                                    extend: 'print',
                                    exportOptions: {
                                        columns: ':visible:not(.exclude)'
                                    },
                                    title: 'Production Order: #' + po_num + "-" + po_desc + "(" + po_qty + po_unit + ")",
                                },
                                'colvis',
                            ],
                            stateSave: true,
                        });
                    },
                    error: function(xhr, status, error) {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        console.error(jsonResponse.message);
                        toastr.error(jsonResponse.message);
                    }
                });
            });

            function initializeAutocomplete(element) {
                element.autocomplete({
                    source: function (request, response) {
                        var existingPartCodes = $('.suggest-goods').map(function() {
                            return request.term != this.value ? this.value : null;
                        }).get();

                        $.ajax({
                            url: "{{ route('material.getFinishedGoods') }}",
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
                                    };
                                }));
                            },
                        });
                    },
                    appendTo: "#createOrderModal",
                    minLength: 2,
                    focus: function (event, ui) {
                        element.val(ui.item.label);
                        return false;
                    },
                    select: function (event, ui) {
                        element.val(ui.item.label);
                        element.closest('.goods-item').find('.material-description').val(ui.item.desc);
                        element.closest('.goods-item').find('.material-unit').val(ui.item.unit);
                        return false;
                    },
                }).autocomplete("instance")._renderItem = function (ul, item) {
                    return $("<li>")
                    .append("<div>" + item.label + " - " + item.desc + "</div>")
                    .appendTo(ul);
                };
            }

            initializeAutocomplete($(".suggest-goods"));

            $(document).on('blur', ".suggest-goods", function() {
                let _obj = $(this);
                let part_code = _obj.val();
                let materialNameInput = _obj.closest(".row").find(".material-description");
                let duplicate = checkDuplicate(part_code);
                if (materialNameInput.val() === '' && part_code.length == 12 && duplicate) {
                    $.ajax({
                        url: "{{ route('material.getFinishedGoods') }}",
                        method: "POST",
                        data: {
                            term: part_code,
                            existingPartCodes: [],
                            _token: '{{ csrf_token() }}',
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.length) {
                                let material = response[0];
                                _obj.closest(".row").find(".material-description").val(material.desc);
                                _obj.closest(".row").find(".material-unit").val(material.unit);
                            } else {
                                _obj.closest(".row").find(".material-description").val('');
                                _obj.closest(".row").find(".material-unit").val('');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                }
            });

            function checkDuplicate(partCode) {
                let partCodes = [];
                let status = true;
                $(document).find(".suggest-goods").each(function() {
                    let currentPartCode = $(this).val();
                    if (partCodes.includes(currentPartCode)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Duplicate Entry',
                            text: 'This part code has already been entered.',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        $(this).val('');
                        $(this).closest(".row").find(".material-description").val('');
                        $(this).closest(".row").find(".material-unit").val('');
                        status = false;
                    } else {
                        if (currentPartCode.length != 0) {
                            partCodes.push(currentPartCode);
                        }
                    }
                });
                return status;
            }

            $(document).on('click', ".btn-create-order", function () {

                $(this)
                .html('<div class="spinner-grow text-light spinner-grow-sm" role="status"><span class="sr-only">Loading...</span></div> Loading...')
                .attr('disabled', true);


                $('.validation-error').remove();
                var isValid = true;
                $('.suggest-goods').each(function() {
                    if ($(this).val() === '') {
                        $(this).after('<span class="text-danger validation-error">Partcode is required.</span>');
                        isValid = false;
                    }
                });

                $('.quantity').each(function() {
                    if ($(this).val() === '') {
                        $(this).closest('.input-group').after('<span class="text-danger validation-error">Quantity is required.</span>');
                        isValid = false;
                    }

                    let val = parseFloat($(this).val());
                    let max = parseFloat($(this).attr('max'));

                    if (val > max) {
                        $(this).closest('.input-group').after('<span class="text-danger validation-error">Quantity can\'t exceed more than '+$(this).attr('max')+'. </span>');
                        isValid = false;
                    }
                });

                if (!isValid) return;

                var formData = $("#create-order-form").serialize();

                $.ajax({
                    type: "POST",
                    url: "{{ route('po.createOrder') }}", 
                    data: formData,
                    success: function(response){
                        if (response.status) {
                            toastr.success(response.message);
                            $("#createOrderModal").modal('hide');
                            $('#prod-orders').DataTable().ajax.reload();
                            $(".btn-create-order")
                            .html("Create Order")
                            .removeAttr('disabled');
                            $('#create-order-form')[0].reset();
                        } else {
                            toastr.error(response.message);
                            $(".btn-create-order")
                            .html("Create Order")
                            .removeAttr('disabled');
                        }
                    },
                    error: function(xhr, status, error){
                        var jsonResponse = JSON.parse(xhr.responseText);
                        toastr.error(jsonResponse.message);
                        console.error(jsonResponse.error);
                        $(".btn-create-order")
                        .html("Create Order")
                        .removeAttr('disabled');
                    }
                });

            });
        });
    </script>
@stop