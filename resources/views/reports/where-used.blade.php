@extends('adminlte::page')

@section('title', 'Where Used')

@section('content')
    <div class="row">
        <div class="col-10 mx-auto">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Where Used</h3>
                </div>
                <div class="card-body">
                    <form action="" id="get-list" action="post">
                        @csrf
                        <div class="goods-container">
                            <div class="goods-item">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <input type="text" name="part_code[]" class="form-control suggest-goods" placeholder="Part code">
                                    </div>
                                    <div class="col-md-7 mb-3">
                                        <input type="text" class="form-control material-description" placeholder="Description" readonly>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <input type="text" class="form-control material-unit" placeholder="UOM" readonly>
                                    </div>
                                    <div class="col-md-2 mb-3 d-none">
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
                                <!-- <button type="button" class="btn btn-secondary" id="add-finished-goods">Add Item</button> -->
                                <button type="button" class="btn btn-primary" id="fetch-bom">Get Materials</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="bom" style="display: none;">
                <div class="bom-section mt-3 d-flex align-items-center justify-content-between">
                    <h5 class="text-secondary">Used in Materials</h5>
                    <div class="btn-group">
                        {{-- <button class="btn btn-sm btn-primary btn-combined">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="btn btn-sm btn-default btn-individual">
                            <i class="fas fa-list"></i>
                        </button> --}}
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body" id="table-section">

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function(){

            var currentUserName = "{{ auth()->user()->name }}";
            var userStamp = userTimeStamp(currentUserName);
            var safeStamp = $(userStamp).text();

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
                            url: "{{ route('materials.getRaw') }}",
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
                                        label: item.part_code,
                                        value: item.part_code,
                                        unit: item.uom.uom_shortcode,
                                        desc: item.description,
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
                        $(this).val('1');
                    }
                });

                if (!isValid) return;

                $(this)
                .html('<div class="spinner-grow text-light spinner-grow-sm" role="status"><span class="sr-only">Loading...</span></div> Loading...')
                .attr('disabled', true);

                var formData = $("#get-list").serialize();

                $.ajax({
                    type: "POST",
                    url: "{{ route('raw.getMaterialsFromRaw') }}", 
                    data: formData,
                    success: function(response){
                        if (response.status) {
                            $("#bom").show();
                            $("#table-section").html(response.html);
                            initializeBomTable();
                        } else {
                            $("#table-section").html('<p>Materials not found.</p>');
                        }
                    },
                    error: function(xhr, status, error){
                        $("#table-section").html('<p>Error fetching materials.</p>');
                    },
                    complete: function(){
                        $("#fetch-bom")
                        .html("Get Materials")
                        .removeAttr('disabled');

                        $("#minimize-bom-card").trigger('click');
                    }
                });

            });

            function initializeBomTable() {

                var partcode = $(".suggest-goods").val();
                var description = $(".material-description").val();
                var unit = "(" + $(".material-unit").val() + ")";

                $('#materials-tbl').DataTable({
                    "responsive": true,
                    "lengthChange": true,
                    "paging": true,
                    "info": true,
                    "footer": true,
                    "buttons": [
                        {
                            extend: 'excel',
                            exportOptions: {
                                columns: ':visible:not(.exclude)'
                            },
                            title: partcode + " - " + description + unit + " Used Report",
                            messageBottom: safeStamp,
                        },
                        {
                            extend: 'pdf',
                            exportOptions: {
                                columns: ':visible:not(.exclude)'
                            },
                            title: partcode + " - " + description + unit + " Used Report",
                            messageBottom: safeStamp,
                        },
                        {
                            extend: 'print',
                            exportOptions: {
                                columns: ':visible:not(.exclude)'
                            },
                            title: partcode + " - " + description + unit + " Used Report",
                            messageBottom: userStamp,
                        },
                        'colvis',
                    ],
                    "processing": true,
                    "searching": true,
                    "ordering": true,
                    "dom": 'lBfrtip',
                    "autoWidth": true,
                    "lengthMenu": datatableLength,
                    "language": {
                        "lengthMenu": "_MENU_"
                    },
                    "columnDefs": [
                        {
                            "targets": [4,5,6,7, 8,9],
                            "visible": false,
                        }
                    ],
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
                if (materialNameInput.val() === '' && part_code.length == 10 && duplicate) {
                    $.ajax({
                        url: "{{ route('materials.getRaw') }}",
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
                                _obj.closest(".row").find(".material-description").val(material.description);
                                _obj.closest(".row").find(".material-unit").val(material.uom.uom_shortcode);
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
        })
    </script>
@stop