@extends('adminlte::page')

@section('title', 'Planned Order Shortage Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Planned Order Shortage Report</h3>
                </div>
                <div class="card-body">
                    <form action="" id="get-bom-form" action="post">
                        @csrf
                        <div class="goods-container">
                            <div class="goods-item">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <input type="text" name="part_code[]" class="form-control suggest-goods" placeholder="Part code">
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <input type="text" class="form-control material-description" placeholder="Description" readonly>
                                    </div>
                                    <div class="col-md-1 mb-3">
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
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-outline-secondary" id="add-finished-goods">Add Item</button>
                                <button type="button" class="btn btn-primary" id="fetch-report">Get Shortage Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="bom" style="display: none;">
                <div class="bom-section mt-3 d-flex align-items-center justify-content-between">
                    <h5 class="text-secondary">Consolidated Materials</h5>
                </div>
                <div id="table-section">

                </div>
            </div>
        </div>
    </div>
</div>

<x-adminlte-modal id="view-modal" title="View Reserved Quantity" icon="fas fa-box" size='lg' scrollable>
    <div class="row">
        <div class="col-12" id="view-modal-section">
            <h4 class="text-secondary text-center">Loading...</h4>
        </div>
    </div>
    <x-slot name="footerSlot">
        <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
    </x-slot>
</x-adminlte-modal>

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
        $(function(){
            $("#add-finished-goods").on('click', function(){
                var newItem = `
                <div class="goods-item">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <input type="text" name="part_code[]" class="form-control suggest-goods" placeholder="Part code">
                        </div>
                        <div class="col-md-5 mb-3">
                            <input type="text" class="form-control material-description" placeholder="Description" readonly>
                        </div>
                        <div class="col-md-1 mb-3">
                            <input type="text" class="form-control material-unit" placeholder="UOM" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="input-group">
                                <input type="number" name="quantity[]" class="form-control quantity" step="0.001" placeholder="Quantity">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-times remove-finished-goods"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                var $newItem = $(newItem);

                $(".goods-container").append($newItem);
                initializeAutocomplete($newItem.find(".suggest-goods"));
            });

            $(document).on('click', '.remove-finished-goods', function(){
                $(this).closest('.goods-item').remove();
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

            $("#fetch-report").on('click', function(){

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
                        $(this).val('1');
                    }
                });

                if (!isValid) return;

                $(this)
                .html('<div class="spinner-grow text-light spinner-grow-sm" role="status"><span class="sr-only">Loading...</span></div> Loading...')
                .attr('disabled', true);

                var formData = $("#get-bom-form").serialize();

                $.ajax({
                    type: "POST",
                    url: "{{ route('po.fetchPlannedShortage') }}", 
                    data: formData,
                    success: function(response){
                        if (response.status) {
                            $("#bom").show();
                            $("#table-section").html(response.html);
                            initializeBomTable();
                        } else {
                            $("#table-section").html('<p>No BOM records found.</p>');
                        }
                    },
                    error: function(xhr, status, error){
                        $("#table-section").html('<p>Error fetching BOM records.</p>');
                    },
                    complete: function(){
                        $("#fetch-report")
                        .html("Get Shortage Report")
                        .removeAttr('disabled');

                        $("#minimize-bom-card").trigger('click');
                    }
                });

            });

            function initializeBomTable() {
                $("table").DataTable({
                    "responsive": true,
                    "lengthChange": true,
                    "autoWidth": true,
                    "paging": true,
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
                    "processing": true,
                    "scrollY": "320px",
                    "scrollCollapse": true,
                    "searching": true,
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

            $('#view-modal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var partcode = button.data('partcode');
                var modal = $(this)
                $.ajax({
                    url: '/app/production-orders/calculate-reserved-quantity',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        partcode: partcode
                    },
                    success: function(response) {
                        if (response.status) {
                            $("#view-modal-section").html(response.html);

                            $("#view-shortage-qty").DataTable({
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
                                "ordering": false,
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

            $('#view-stock').on('hide.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                $("#view-stock-section").html(`
                    <h4 class="text-secondary text-center">Loading...</h4>
                `);
            });

            $(document).on('click', '.view-stock', function(e){
                e.preventDefault();
                var partcode = $(this).data('partcode');
                $.ajax({
                    url: '/app/production-orders/show-stock-transactions',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        partcode: partcode
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
                            $("#view-stock").modal('show');
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });
        })
    </script>
@stop