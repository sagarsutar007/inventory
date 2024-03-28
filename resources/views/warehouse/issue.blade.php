@extends('adminlte::page')

@section('title', 'Issue Material')

@section('content')
    <div class="row">
        <div class="col-md-9 mx-auto">
            <form id="issueForm" action="{{ route('wh.issue') }}" method="post" autocomplete="off" enctype="multipart/form-data">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-fw fa-minus"></i> Issue Material (Manual)</h3>
                    <div class="card-tools">
                        <a href="{{ route('wh') }}" class="btn btn-link btn-sm p-0"><i class="fas fa-fw fa-th-large"></i> View Stock</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="vendor">Vendor</label>
                                <select name="vendor" id="vendor" class="form-control select2" style="width: 100%;" required>
                                    <option value=""></option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->vendor_id  }}"> {{ $vendor->vendor_name }} </option>
                                    @endforeach
                                </select>
                                <div id="err-vendor"></div>
                            </div>
                        </div>
                        <!-- <div class="col-md-4">
                            <div class="form-group">
                                <label for="ponum">PO number:</label><br />
                                <input type="text" id="ponum" name="popn" class="form-control" placeholder="Enter PO Number" value="">
                            </div>
                        </div> -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="reason">Reason</label>
                                <select name="reason" id="reason" class="form-control" style="width:100%;">
                                    <option value=""></option>
                                    <option value="Rejection in PO">Rejection in PO</option>
                                    <option value="Additional Issue">Additional Issue</option>
                                    <option value="Return to Vendor">Return to Vendor</option>
                                    <option value="Others">Others</option>
                                </select>
                                <div id="err-reason"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @php
                                $defaultTimeZone = date_default_timezone_get();
                                date_default_timezone_set('Asia/Kolkata');
                                $dateIST = date('d-m-Y');
                                date_default_timezone_set($defaultTimeZone);
                            @endphp
                            <div class="form-group">
                                <label for="date">Issue Date: *</label><br /> 
                                <input type="text" id="date" name="date" class="form-control" placeholder="Enter Date" value="{{ $dateIST }}" readonly required>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="material-quantity-container">
                        <div class="material-with-quantity">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="text" name="part_code[]" class="form-control suggest-partcode" placeholder="Partcode" required>
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
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-secondary add-material-quantity-item"><i class="fas fa-fw fa-plus"></i> Add Item </button>
                    <a href="{{ route('wh.issue') }}" class="btn btn-danger btn-spinner"><i class="fas fa-fw fa-times"></i> Cancel</a>
                    <button id="submitForm" type="button" class="btn btn-primary"><i class="fas fa-fw fa-check"></i> Submit</button>
                </div>
            </div>
            </form>
        </div>
    </div>
@stop


@section('js')
    <script>
        $(function () {
            $(".select2").select2({
                placeholder: 'Select Vendor',
                theme: 'bootstrap4',
            });

            $("#reason").select2({
                placeholder: 'Select Reason',
                minimumResultsForSearch: Infinity,
                theme: 'bootstrap4',
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
            
            function handleShortcutKey(event) {
                if (event.shiftKey && event.key === 'N') {
                    event.preventDefault();
                    addMaterialQuantityItem();
                }
            }

            $(document).on('keydown', handleShortcutKey);

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
                    focus: function (event, ui) {
                        if (ui.item.closing_bal > 0) {
                            element.val(ui.item.label);
                        }
                        return false;
                    },
                    select: function (event, ui) {
                        if (ui.item.closing_bal > 0) {
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
                    if (item.closing_bal <= 0) {
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

            initializeAutocomplete($(".suggest-partcode"));

            $(".material-quantity-container").on('click', '.remove-material-quantity-item', function () {
                if ($('.material-with-quantity').length > 1) {
                    $(this).closest('.material-with-quantity').remove();
                } else {
                    alert("At least one material & quantity item should be present.");
                }
            });

            $('#reason').change(function() {
                $('#err-reason').empty();
            });

            $('#submitForm').click(function() {
                var _obj = $(this)
                $('.validation-error').remove();
                var isValid = true;

                var selectedReason = $('#reason').val();
                if (!selectedReason) {
                    $('#err-reason').append('<span class="text-danger validation-error">Reason is required.</span>');
                    isValid = false;
                }

                $('.suggest-partcode').each(function() {
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

                var formData = $('#issueForm').serialize();
                $.ajax({
                    type: 'POST',
                    url: $('#issueForm').attr('action'),
                    data: formData,
                    success: function(response) {
                        if (response.status === true) {
                            _obj.addClass('btn-spinner');
                            toastr.success(response.message);
                            setTimeout(function() { location.reload(); }, 1500);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        toastr.error(jsonResponse.message);
                        console.error(jsonResponse.error);
                    }
                });
            });

            $(document).on('input', ".suggest-partcode", function() {
                let _obj = $(this);
                _obj.closest(".row").find(".material-name").val('');
                _obj.closest(".row").find(".material-unit").val('');
            });
            
            function checkDuplicate(partCode) {
                let partCodes = [];
                $(document).find(".suggest-partcode").each(function() {
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
                        $(this).closest(".row").find(".material-name").val('');
                        $(this).closest(".row").find(".material-unit").val('');
                    } else {
                        if (currentPartCode.length != 0) {
                            partCodes.push(currentPartCode);
                        }
                    }
                });
            }

            $(document).on('blur', ".quantity", function() {
                let _obj = $(this);
                let val = parseFloat(_obj.val());
                let max = parseFloat(_obj.attr('max'));

                if (val != '') { _obj.closest('.col-md-3').find('.validation-error').empty(); }
                
                if (!isNaN(val) && !isNaN(max)) {
                    if (val > max) {
                        _obj.val('');
                        _obj.closest('.input-group').after(`<span class="text-danger validation-error">Quantity can't exceed more than ${max}.</span>`);
                    } else {
                        _obj.closest('.col-md-3').find('.validation-error').empty();
                    }
                } else {
                    _obj.closest('.col-md-3').find('.validation-error').empty();
                }
            });

            $(document).on('blur', ".suggest-partcode", function() {
                let _obj = $(this);
                let part_code = _obj.val();
                let materialNameInput = _obj.closest(".row").find(".material-name");
                checkDuplicate(part_code);
                if (materialNameInput.val() === '' && part_code.length == 10) {
                    $.ajax({
                        url: "{{ route('wh.getMaterials') }}",
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
                                if (material.closing_balance ?? 0 > 0) {
                                    _obj.closest(".row").find(".material-name").val(material.desc);
                                    _obj.closest(".row").find(".material-unit").val(material.unit);
                                    _obj.closest(".row").find(".quantity").attr('max', material.closing_balance);
                                } else {
                                    _obj.closest(".row").find(".material-name").val('');
                                    _obj.closest(".row").find(".material-unit").val('');
                                    _obj.closest(".row").find(".quantity").removeAttr('max');
                                    _obj.val('');
                                    Swal.fire({
                                        icon: "error",
                                        title: "ERROR",
                                        text: "This item can't be issued due to no closing balance",
                                    });
                                }
                            } else {
                                _obj.closest(".row").find(".material-name").val('');
                                _obj.closest(".row").find(".material-unit").val('');
                                _obj.closest(".row").find(".quantity").removeAttr('max');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
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
        });

        // $(document).on('focus', ".suggest-partcode", function () {
        //     initializeAutocomplete($(this));
        // });
    </script>
@stop