@extends('adminlte::page')

@section('title', 'Material Receipt')

@section('content')
    <div class="row">
        <div class="col-md-9 mx-auto">
            <form id="receiptForm" action="{{ route('wh.receive') }}" method="post" autocomplete="off" enctype="multipart/form-data">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-fw fa-plus"></i> Material Receipt</h3>
                    <div class="card-tools">
                        <a href="{{ route('wh') }}" class="btn btn-link btn-sm p-0">
                            <i class="fas fa-fw fa-th-large"></i> View Stock
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="vendor">Vendor</label>
                                <select name="vendor" id="vendor" class="form-control select2" style="width: 100%;">
                                    <option value=""></option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->vendor_id  }}"> {{ $vendor->vendor_name }} </option>
                                    @endforeach
                                </select>
                                <div id="err-vendor"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date">Purchase number:</label><br />
                                <input type="text" name="popn" class="form-control" placeholder="Enter Purchase Number" value="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date">Issue Date: *</label><br />
                                <input type="text" name="date" class="form-control" placeholder="Enter Date" value="{{ date('d-m-Y') }}" readonly required>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="material-quantity-container">
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
                                        <input name="quantity[]" required type="number" class="form-control quantity" placeholder="Qty." step="0.001">
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
                    <button type="button" class="btn btn-outline-secondary add-material-quantity-item"><i class="fas fa-fw fa-plus"></i> New Item</button>
                    <a href="{{ route('wh.issue') }}" class="btn btn-outline-danger"><i class="fas fa-fw fa-times"></i> Cancel</a>
                    <button id="submitForm" type="button" class="btn btn-outline-primary"><i class="fas fa-fw fa-check"></i> Submit</button>
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
            // Function to add a new category item
            $(".add-material-quantity-item").click(function () {
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
                                    <input name="quantity[]" required type="number" class="form-control quantity" placeholder="Qty." step="0.001">
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
            });

            function initializeAutocomplete(element) {
                element.autocomplete({
                    source: function (request, response) {
                        var existingPartCodes = $('.suggest-partcode').map(function() {
                            return request.term != this.value ? this.value : null;
                        }).get();

                        $.ajax({
                          url: "/app/warehouse/get-all-materials",
                          method: "POST",
                          dataType: "json",
                          data: {
                            term: request.term,
                            existingPartCodes: existingPartCodes,
                          },
                          headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                              "content"
                            ),
                          },
                          success: function (data) {
                            response(
                              data.map(function (item) {
                                return {
                                  label: item.value,
                                  value: item.value,
                                  unit: item.unit,
                                  desc: item.desc,
                                };
                              })
                            );
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
                        element.closest('.material-with-quantity').find('.material-name').val(ui.item.desc);
                        element.closest('.material-with-quantity').find('.material-unit').val(ui.item.unit);
                        return false;
                    },
                }).autocomplete("instance")._renderItem = function (ul, item) {
                    return $("<li>")
                    .append("<div>" + item.label + " - " + item.desc + "</div>")
                    .appendTo(ul);
                };
            }

            // Initialize autocomplete for existing elements
            initializeAutocomplete($(".suggest-partcode"));

            let partCodes = [];

            $(document).on('blur', ".suggest-partcode", function() {
                let _obj = $(this);
                let partCode = _obj.val();
                
                if (partCodes.includes(partCode)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Entry',
                        text: 'This part code has already been entered.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    _obj.val('');
                    _obj.closest(".row").find(".material-name").val('');
                    _obj.closest(".row").find(".material-unit").val('');
                } else {
                    if (partCode.length != 0) {
                        partCodes.push(partCode);
                    }
                }
            });

            // Function to remove the closest category item
            $(".material-quantity-container").on('click', '.remove-material-quantity-item', function () {
                if ($('.material-with-quantity').length > 1) {
                    $(this).closest('.material-with-quantity').remove();
                } else {
                    alert("At least one material & quantity item should be present.");
                }
            });

            $('#submitForm').click(function() {
                var _obj = $(this)
                $('.validation-error').remove();
                var isValid = true;

                var selectedVendor = $('#vendor').val();
                if (!selectedVendor) {
                    $('#err-vendor').append('<span class="text-danger validation-error">Vendor is required.</span>');
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
                });

                if (!isValid) return;

                var formData = $('#receiptForm').serialize();
                $.ajax({
                    type: 'POST',
                    url: $('#receiptForm').attr('action'),
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

            $(document).on('blur', ".suggest-partcode", function() {
                let _obj = $(this);
                let part_code = _obj.val();
                if (part_code.length != 0) {
                    $.ajax({
                        url: "/app/material/get-details",
                        method: "POST",
                        data: {
                            part_code: part_code,
                            _token: '{{ csrf_token() }}',
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.success) {
                                let material = response.data;
                                _obj.closest(".row").find(".material-name").val(material.description);
                                _obj.closest(".row").find(".material-unit").val(material.uom.uom_text);
                            } else {
                                _obj.closest(".row").find(".material-name").val('');
                                _obj.closest(".row").find(".material-unit").val('');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                }
            });

            $(document).on('input', ".suggest-partcode", function() {
                let _obj = $(this);
                _obj.closest(".row").find(".material-name").val('');
                _obj.closest(".row").find(".material-unit").val('');
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
    </script>
@stop