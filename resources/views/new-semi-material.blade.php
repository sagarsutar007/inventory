@extends('adminlte::page')

@section('title', 'Create Semi Finished Material')

@section('content')
    <div class="row">
        <div class="col-md-7 mx-auto">
            <form action="{{ route('semi.store') }}" method="post" autocomplete="off" enctype="multipart/form-data">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Create Semi Finished Material</h3>
                    <div class="card-tools">
                        <a href="{{ route('semi') }}" class="btn btn-link btn-sm"><i class="fas fa-fw fa-box"></i> View List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="raw-material-item-container">
                        <div class="row">
                            <div class="col-12 col-md-8">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" name="description" placeholder="Enter material name">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" name="opening_balance" placeholder="Opening Bal.">
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
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <select class="form-control select2" id="uom" name="uom_id">
                                            <option value=""></option>
                                            @foreach($uom as $unit)
                                            <option value="{{$unit->uom_id}}" {{ old('uom_id') == $unit->uom_id ? 'selected' : '' }}>{{$unit->uom_text}}
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <span class="input-group-text add-uom">
                                                <i class="fas fa-plus"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <select class="form-control" id="commodity" name="commodity_id">
                                            <option value=""></option>
                                            @foreach($commodity as $cmdty)
                                            <option value="{{$cmdty->commodity_id}}">{{$cmdty->commodity_name}}</option>
                                            @endforeach 
                                        </select>
                                        <div class="input-group-append">
                                            <span class="input-group-text add-commodity">
                                                <i class="fas fa-plus"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <select class="form-control" id="category" name="category_id">
                                            <option value=""></option>
                                            @foreach($category as $ctg)
                                            <option value="{{$ctg->category_id}}">{{$ctg->category_name}}</option>
                                            @endforeach  
                                        </select>
                                        <div class="input-group-append">
                                            <span class="input-group-text add-category">
                                                <i class="fas fa-plus"></i>
                                            </span>
                                        </div>
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
                                <textarea class="form-control" name="additional_notes" placeholder="Additional notes"></textarea>
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

    <x-adminlte-modal id="modalAddUOM" title="Add Measurement Unit" icon="fas fa-edit">
        <form action="/" method="post" id="uom-form">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label for="txt-uom-ft">Measurement Unit FullText</label>
                    <input type="text" id="txt-uom-ft" name="uom_text" class="form-control" placeholder="Ex. Meter, Kilogram, Litre" required>
                </div>
                <div class="form-group">
                    <label for="txt-uom-st">Measurement Unit Shorttext</label>
                    <input type="text" id="txt-uom-st" name="uom_shortcode" class="form-control" placeholder="Ex. Mtr, Kg, Ltr" required>
                </div>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-uom" theme="outline-primary" label="Save"/>
        </x-slot>
        </form>
    </x-adminlte-modal>

    <x-adminlte-modal id="modalAddCommodity" title="Add New Commodity" icon="fas fa-edit">
        <form action="/" method="post" id="commodity-form">
        @csrf
        <input type="hidden" name="ajax" value="true">
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label for="txt-commodity">Commodity</label>
                    <input type="text" id="txt-commodity" name="commodity_name" class="form-control" placeholder="Enter Commodity" value="">
                </div>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-commodity" theme="outline-primary" label="Save"/>
        </x-slot>
        </form>
    </x-adminlte-modal>

    <x-adminlte-modal id="modalAddCategory" title="Add Category" icon="fas fa-edit">
        <form action="/" method="post" id="category-form">
        @csrf
        <input type="hidden" name="ajax" value="true">
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label for="txt-category">Category</label>
                    <input type="text" id="txt-category" name="category_name" class="form-control" placeholder="Enter Category" value="">
                </div>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-category" theme="outline-primary" label="Save"/>
        </x-slot>
        </form>
    </x-adminlte-modal>
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

            // Function to initialize Select2 for raw materials dropdowns
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
                            var selectedItems = [];
                            $('.raw-materials').each(function() {
                                selectedItems.push($(this).val());
                            });
                            return {
                                _token: '{{ csrf_token() }}',
                                q: params.term,
                                selected_values: selectedItems,
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

            $(document).on('click', '.add-uom', function(){
                $("#modalAddUOM").modal('show');
            });

            $(document).on('click', '.add-commodity', function(){
                $("#modalAddCommodity").modal('show');
            });

            $(document).on('click', '.add-category', function(){
                $("#modalAddCategory").modal('show');
            });

            $(document).on('click', '.btn-save-uom', function () {
                $('#uom-form .text-danger').remove();
                var uomText = $('#txt-uom-ft').val().trim();
                var uomShortcode = $('#txt-uom-st').val().trim();
                var isValid = true;
                if (uomText === '') {
                    $('#txt-uom-ft').after('<p class="text-danger">Unit FullText is required</p>');
                    isValid = false;
                }

                if (uomShortcode === '') {
                    $('#txt-uom-st').after('<p class="text-danger">Unit Shorttext is required</p>');
                    isValid = false;
                }

                if (isValid) {
                    $.ajax({
                        url: "{{ route('uom.store') }}",
                        method: 'POST',
                        data: $('#uom-form').serialize(),
                        success: function (response) {
                            loadSelect2($('#uom'), "unit", response.uom); 
                            $('#modalAddUOM').modal('hide');
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                }
            });

            $(document).on('click', '.btn-save-commodity', function () {
                $('#commodity-form .text-danger').remove();
                var commodity = $('#txt-commodity').val().trim();
                var isValid = true;

                if (commodity === '') {
                    $('#txt-commodity').after('<p class="text-danger">Commodity is required</p>');
                    isValid = false;
                }

                if (isValid) {
                    $.ajax({
                        url: "{{ route('commodities.save') }}",
                        method: 'POST',
                        data: $('#commodity-form').serialize(),
                        success: function (response) {
                            loadSelect2($('#commodity'), "commodity", response.commodity); 
                            $('#modalAddCommodity').modal('hide');
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                }
            });

            $(document).on('click', '.btn-save-category', function () {
                $('#category-form .text-danger').remove();
                var category = $('#txt-category').val().trim();
                var isValid = true;

                if (category === '') {
                    $('#txt-category').after('<p class="text-danger">Category is required</p>');
                    isValid = false;
                }

                if (isValid) {
                    $.ajax({
                        url: "{{ route('categories.save') }}",
                        method: 'POST',
                        data: $('#category-form').serialize(),
                        success: function (response) {
                            loadSelect2($('#category'), "category", response.category); 
                            $('#modalAddCategory').modal('hide');
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                }
            });

            function loadSelect2(formElement, type, selectedValue) {
                var ajaxUrl = "";
                var placeholderText = "";
                
                if (type === 'commodity') {
                    placeholderText = "Commodity";
                    ajaxUrl = "{{ route('commodities.search') }}";
                } else if (type === 'category') {
                    placeholderText = "Category";
                    ajaxUrl = "{{ route('categories.search') }}";
                } else {
                    placeholderText = "Unit";
                    ajaxUrl = "{{ route('uom.search') }}";
                }
                
                var data = [];
                if (ajaxUrl !== "") {
                    $.ajax({
                        url: ajaxUrl,
                        method: 'POST',
                        dataType: 'json',
                        async: false, 
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            data = response.results;
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                }

                formElement.select2({
                    placeholder: placeholderText,
                    theme: 'bootstrap4',
                    data: data,
                    cache: true
                });

                if (selectedValue) {
                    formElement.val(selectedValue.id).trigger('change');
                }
            }
        });

    </script>
@stop