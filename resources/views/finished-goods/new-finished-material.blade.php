@extends('adminlte::page')

@section('title', 'Create Finished Material')

@section('content')
    <div class="row">
        <div class="col-md-7 mx-auto">
            <form action="{{ route('finished.store') }}" method="post" autocomplete="off" enctype="multipart/form-data">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Create Finished Material</h3>
                    <div class="card-tools">
                        <a href="{{ route('finished') }}" class="btn btn-link btn-sm"><i class="fas fa-fw fa-box"></i> View List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="raw-material-item-container">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="partcode" name="part_code" placeholder="Enter Part Code" value="{{ old('part_code') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" name="description" placeholder="Enter material name" value="{{ old('description') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" name="opening_balance" placeholder="Opening Bal." value="{{ old('opening_balance') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" name="re_order" placeholder="Re-Order" value="{{ old('re_order') }}">
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" name="mpn" placeholder="Make/MPN">
                                    </div>
                                </div>
                            </div> -->
                        </div>
                        <div class="row m-0 p-0">
                            <div class="col-12 m-0 p-0">
                                <p class="partcode_msg"></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <select class="form-control select2" id="uom" name="uom_id">
                                            <option value=""></option>
                                            @foreach($uom as $unit)
                                            <option value="{{$unit->uom_id}}" {{ old('uom_id') == $unit->uom_id ? 'selected' : '' }}>{{$unit->uom_text}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <select class="form-control" id="commodity" name="commodity_id">
                                            <option value=""></option>
                                            @foreach($commodity as $cmdty)
                                            <option value="{{$cmdty->commodity_id}}" {{ old('commodity_id') == $cmdty->commodity_id ? 'selected' : '' }}>{{$cmdty->commodity_name}}</option>
                                            @endforeach 
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <select class="form-control" id="category" name="category_id">
                                            <option value=""></option>
                                            @foreach($category as $ctg)
                                            <option value="{{$ctg->category_id}}" {{ old('category_id') == $ctg->category_id ? 'selected' : '' }}>{{$ctg->category_name}}</option>
                                            @endforeach  
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="photo" class="custom-file-input" id="material-img" accept="image/*">
                                            <label class="custom-file-label" for="material-img">Material Photo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="pdf" class="custom-file-input" id="material-pdf" accept="application/pdf">
                                            <label class="custom-file-label" for="material-pdf">Material PDF</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="doc" class="custom-file-input" id="material-doc" accept=".doc, .docx, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, .xls, .xlsx, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                                            <label class="custom-file-label" for="material-doc">Material Document</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <textarea class="form-control" name="additional_notes" placeholder="Additional notes">{{ old('additional_notes') }}</textarea>
                            </div>
                        </div>
                        <h6>Bill of Material</h6>
                        <div class="raw-materials-container">
                            <div class="raw-with-quantity">
                                <div class="row">
                                    <div class="col-md-8">
                                        <select class="form-control raw-materials" name="raw[]">
                                            <option value=""></option>  
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control" name="quantity[]" placeholder="Quantity" step="0.001">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-times remove-raw-quantity-item"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>                            
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-outline-secondary add-raw-quantity-item"><i class="fas fa-fw fa-plus"></i> Add BOM Item</button>
                    <a href="{{ route('semi') }}" class="btn btn-outline-danger"><i class="fas fa-fw fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-fw fa-check"></i> Submit</button>
                </div>
            </div>
            </form>
        </div>
    </div>
@stop


@section('js')
    <script>
        $(function () {
            // Initialize Select2 for uom, commodity, and category dropdowns
            $('#uom').select2({
                placeholder: 'Unit',
                theme: 'bootstrap4'
            });
            $('#commodity').select2({
                placeholder: 'Commodity',
                theme: 'bootstrap4'
            });
            $('#category').select2({
                placeholder: 'Category',
                theme: 'bootstrap4'
            });

            $(document).on('blur', '#partcode', function() {
                validatePartcode($(this).val());
            });

            $('#commodity, #category').on('change input', function() {
                suggestPartcode();
            });

            function validatePartcode(partcode) {
                if (partcode) {
                    if (partcode.length >= 12) {
                        

                        // Make AJAX request to check if part code exists
                        $.ajax({
                            url: '{{ route("finished.checkPartcode") }}',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                part_code: partcode
                            },
                            success: function(response) {
                                if (response.exists) {
                                    // Part code exists
                                    $('#partcode').css({
                                        'border': '1px solid red',
                                        'background': '#FF000014'
                                    });
                                    $(".partcode_msg")
                                        .addClass('text-danger')
                                        .text("Part code already exists.");
                                } else {
                                    $('#partcode').css({
                                        'border': '1px solid #1a8300',
                                        'background': '#56ff001f'
                                    });
                                    $(".partcode_msg").text("Part code is available.");
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                                // Handle error if necessary
                            }
                        });
                    } else {
                        $('#partcode').css({
                            'border': '1px solid red',
                            'background': '#FF000014'
                        });
                        $(".partcode_msg")
                            .addClass('text-danger')
                            .text("Part code must be at least 12 characters long.");
                    }
                } else {
                    $('#partcode').css({
                        'border': '1px solid red',
                        'background': '#FF000014'
                    });
                }
            }

            $('#commodity, #category').change(function() {
                suggestPartcode();
            });

            function suggestPartcode() {
                let commodityId = $('#commodity').val();
                let categoryId = $('#category').val();

                // Make AJAX request to fetch suggested part code

                if (commodityId.length > 0 && categoryId.length > 0) {
                    $.ajax({
                        url: '{{ route("finished.suggest.partcode") }}',
                        type: 'GET',
                        data: {
                            commodity_id: commodityId,
                            category_id: categoryId
                        },
                        success: function(response) {
                            $('.partcode_msg').addClass("text-success").html(`Suggested: <span class="suggested_partcode">` + response.suggested_part_code + `</span>`);
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
            }

            $(document).on('click', '.suggested_partcode', function(){
                $("#partcode")
                .css({
                    'border': '1px solid #1a8300',
                    'background': '#56ff001f'
                })
                .val($(this).text());
                $(this).parent().empty();
            });

            // Function to initialize Select2 for raw materials dropdowns
            function initializeRawMaterialsSelect2(selectElement) {
                selectElement.select2({
                    placeholder: 'Raw materials',
                    theme: 'bootstrap4',
                    ajax: {
                        url: '{{ route("finished.getMaterials") }}',
                        dataType: 'json',
                        method: 'POST',
                        delay: 250,
                        data: function (params) {
                            return {
                                _token: '{{ csrf_token() }}',
                                q: params.term 
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

            // Initialize Select2 for existing raw material dropdowns
            $('.raw-materials').each(function() {
                initializeRawMaterialsSelect2($(this));
            });

            // Function to add a new raw material item
            $(".add-raw-quantity-item").click(function () {
                var newItem = `
                    <div class="raw-with-quantity">
                        <div class="row">
                            <div class="col-md-8">
                                <select class="form-control raw-materials" name="raw[]">
                                    <option value=""></option>  
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group mb-3">
                                    <input type="number" class="form-control" name="quantity[]" placeholder="Quantity" step="0.001">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-times remove-raw-quantity-item"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>                            
                    </div>
                `;
                var $newItem = $(newItem);
                $(".raw-materials-container").append($newItem);
                
                var newSelect = $newItem.find('.raw-materials');
                initializeRawMaterialsSelect2(newSelect);
            });

            // Function to remove the closest raw material item
            $(".raw-materials-container").on('click', '.remove-raw-quantity-item', function () {
                if ($('.raw-with-quantity').length > 1) {
                    $(this).closest('.raw-with-quantity').remove();
                } else {
                    alert("At least one Raw Material item should be present.");
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

    </script>
@stop