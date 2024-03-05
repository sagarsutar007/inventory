@extends('adminlte::page')

@section('title', 'Create Production Order')

@section('content')
    <div class="row">
        <div class="col-10 mx-auto">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Create Production Order</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <a href="{{ route('po') }}" class="btn btn-link btn-sm">View List</a>
                            <button type="button" class="btn btn-link btn-sm" id="minimize-bom-card" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="" id="get-bom-form" action="post">
                        @csrf
                        <div class="goods-container">
                            <div class="goods-item">
                                <div class="row">
                                    <div class="col-md-2 mb-3">
                                        <input type="text" name="part_code[]" class="form-control suggest-goods" placeholder="Part code">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" class="form-control material-description" placeholder="Description" readonly>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <input type="text" class="form-control material-unit" placeholder="UOM" readonly>
                                    </div>
                                    <div class="col-md-2 mb-3">
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
                                <button type="button" class="btn btn-secondary" id="add-finished-goods">Add Item</button>
                                <button type="button" class="btn btn-primary" id="fetch-bom">Get BOM</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="bom" style="display:block;">
                <div class="bom-section mt-3 d-flex align-items-center justify-content-between">
                    <h5 class="text-secondary">Required Materials</h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary btn-combined">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="btn btn-sm btn-default btn-individual">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body" id="table-section">

                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('po.create') }}" class="btn btn-outline-danger">Cancel</a>
                        <button type="button" class="btn btn-primary">Create Order</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function(){
            $("#add-finished-goods").on('click', function(){
                var newItem = `<div class="goods-item"><div class="row"><div class="col-md-2 mb-3"><input type="text" name="part_code[]" class="form-control suggest-goods" placeholder="Part code"></div><div class="col-md-6 mb-3"><input type="text" class="form-control material-description" placeholder="Description" readonly></div><div class="col-md-2 mb-3"><input type="text" class="form-control material-unit" placeholder="UOM" readonly></div><div class="col-md-2 mb-3"><div class="input-group"><input type="number" name="quantity[]" class="form-control quantity" step="0.001" placeholder="Quantity"><div class="input-group-append"><span class="input-group-text"><i class="fas fa-times remove-finished-goods"></i></span></div></div></div></div></div>`;

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

            $("#fetch-bom").on('click', function(){

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

                $(this)
                .html('<div class="spinner-grow text-light spinner-grow-sm" role="status"><span class="sr-only">Loading...</span></div> Loading...')
                .attr('disabled', true);

                var formData = $("#get-bom-form").serialize();

                $.ajax({
                    type: "POST",
                    url: "{{ route('po.getBom') }}", 
                    data: formData,
                    success: function(response){
                        if (response.status) {
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
                        $("#fetch-bom")
                        .html("Get BOM")
                        .removeAttr('disabled');

                        $("#minimize-bom-card").trigger('click');
                    }
                });

            });

            function initializeBomTable() {
                $('#bom-table').DataTable({
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
                    "processing": true,
                    "scrollY": "320px",
                    "scrollCollapse": true,
                    "searching": false,
                    "ordering": true,
                    "dom": 'Bfrtip',
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

            $(document).on('click', '.btn-individual', function(){
                $('.btn-combined').removeClass('btn-primary').addClass('btn-default');
                $(this).removeClass('btn-default').addClass('btn-primary');
            });

            $(document).on('click', '.btn-combined', function(){
                $('.btn-individual').removeClass('btn-primary').addClass('btn-default');
                $(this).removeClass('btn-default').addClass('btn-primary');
            });
        })
    </script>
@stop