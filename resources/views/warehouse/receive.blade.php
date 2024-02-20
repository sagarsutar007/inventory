@extends('adminlte::page')

@section('title', 'Receive Material')

@section('content')
    <div class="row">
        <div class="col-md-7 mx-auto">
            <form action="{{ route('wh.receive') }}" method="post" autocomplete="off" enctype="multipart/form-data">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-fw fa-plus"></i> Receive Material</h3>
                    <div class="card-tools">
                        <a href="{{ route('wh') }}" class="btn btn-link btn-sm p-0">
                            <i class="fas fa-fw fa-th-large"></i> View Warehouse
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="material-quantity-container">
                        <div class="material-with-quantity">
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <select name="material_id[]" class="form-control select2" style="width: 100%;">

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input name="quantity[]" type="number" class="form-control" placeholder="Enter Qty" step="0.001">
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
                    <button type="button" class="btn btn-outline-secondary add-material-quantity-item"><i class="fas fa-fw fa-plus"></i> New</button>
                    <a href="{{ route('wh') }}" class="btn btn-outline-danger"><i class="fas fa-fw fa-times"></i> Cancel</a>
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
            // Function to add a new category item
            $(".add-material-quantity-item").click(function () {
                var newItem = `
                    <div class="material-with-quantity">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <select name="material_id[]" required class="form-control select2" style="width: 100%;"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input name="quantity[]" required type="number" class="form-control" placeholder="Enter Qty" step="0.001">
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
                initializeRawMaterialsSelect2($newItem.find('select'));
            });

            // Function to remove the closest category item
            $(".material-quantity-container").on('click', '.remove-material-quantity-item', function () {
                if ($('.material-with-quantity').length > 1) {
                    $(this).closest('.material-with-quantity').remove();
                } else {
                    alert("At least one material & quantity item should be present.");
                }
            });

            function initializeRawMaterialsSelect2(selectElement) {
                selectElement.select2({
                    placeholder: 'Select Material',
                    theme: 'bootstrap4',
                    ajax: {
                        url: '{{ route("materials.get") }}',
                        dataType: 'json',
                        method: 'POST',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                _token: '{{ csrf_token() }}'

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

            initializeRawMaterialsSelect2($('.select2'));

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