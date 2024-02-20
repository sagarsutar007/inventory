@extends('adminlte::page')

@section('title', 'Add New Raw Material')

@section('content')
    <div class="row">
        <div class="col-md-7 mx-auto">
            <form action="{{ route('raw.store') }}" method="post" autocomplete="off" enctype="multipart/form-data">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-fw fa-plus"></i> Add New Raw Material</h3>
                    <div class="card-tools">
                        <a href="{{ route('raw') }}" class="btn btn-link btn-sm p-0"><i class="fas fa-fw fa-th-large"></i> View Raw Materials</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="raw-material-item-container">
                        <div class="row">
                            <div class="col-12 col-md-9">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" name="description" placeholder="Enter raw material name" value="{{ old('description') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" name="opening_balance" placeholder="Opening Balance" value="{{ old('opening_balance') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control" name="make" placeholder="Make" value="{{ old('make') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control" name="mpn" placeholder="MPN" value="{{ old('mpn') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" name="re_order" placeholder="Re-Order" value="{{ old('re_order') }}">
                                    </div>
                                </div>
                            </div>
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
                                            <option value="{{$cmdty->commodity_id}}" {{ old('commodity_id') == $cmdty->commodity_id ? 'selected' : '' }}>{{$cmdty->commodity_name}}
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
                                            <option value="{{$ctg->category_id}}" {{ old('category_id') == $ctg->category_id ? 'selected' : '' }}>{{$ctg->category_name}}
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
                                            <input type="file" name="doc" class="custom-file-input" id="material-doc">
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
                        <div class="vendor-price-container">
                            @if ($errors->any())
                                @foreach(old('vendor', ['']) as $index => $vendor)
                                <div class="vendor-with-price">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <input type="text" class="form-control sug-vendor" name="vendor[]" placeholder="Vendor name" value="{{ old('vendor.' . $index) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group mb-3">
                                                <input type="number" class="form-control" name="price[]" placeholder="Price" step="0.001" value="{{ old('price.' . $index) }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="fas fa-times remove-vendor-price-item"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                            <div class="vendor-with-price">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control sug-vendor" name="vendor[]" placeholder="Vendor name">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control" name="price[]" placeholder="Price" step="0.001" >
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-times remove-vendor-price-item"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>                            
                            </div>
                            <div class="vendor-with-price">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control sug-vendor" name="vendor[]" placeholder="Vendor name">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control" name="price[]" placeholder="Price" step="0.001">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-times remove-vendor-price-item"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>                            
                            </div>
                            <div class="vendor-with-price">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control sug-vendor" name="vendor[]" placeholder="Vendor name">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control" name="price[]" placeholder="Price" step="0.001">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-times remove-vendor-price-item"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>                            
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-outline-secondary add-vendor-price-item"><i class="fas fa-fw fa-plus"></i> New</button>
                    <a href="{{ route('commodities') }}" class="btn btn-outline-danger"><i class="fas fa-fw fa-times"></i> Cancel</a>
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
            if ($('#uom').length){
                loadSelect2($('#uom'), 'unit');
            }

            if ($('#commodity').length){
                loadSelect2($('#commodity'), 'commodity');
            }

            if ($('#category').length){
                loadSelect2($('#category'), 'category');
            }

            // Function to add a new category item
            $(".add-vendor-price-item").click(function () {
                var newItem = $(".vendor-with-price:first").clone();
                newItem.find('input').val('');
                $(".vendor-price-container").append(newItem);
            });

            // Function to remove the closest category item
            $(".vendor-price-container").on('click', '.remove-vendor-price-item', function () {
                if ($('.vendor-with-price').length > 1) {
                    $(this).closest('.vendor-with-price').remove();
                } else {
                    alert("At least one vendor & price item should be present.");
                }
            });

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

    </script>
@stop