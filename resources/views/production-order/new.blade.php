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
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('po.initOrder') }}" id="create-order-form" method="post">
                        @csrf
                        <div class="goods-container">
                            <div class="goods-item">
                                <div class="row">
                                    <div class="col-md-2 mb-3">
                                        <input type="text" name="part_code[]" class="form-control suggest-goods" placeholder="Part code" value="{{ old('part_code.0') }}">
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
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-primary" id="create-order">Create Order</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function(){

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

            $(document).on('click', "#create-order", function () {
                $(this)
                    .html(`
                        <div class="spinner-grow text-light spinner-grow-sm" role="status">
                            <span class="sr-only">Loading...</span>
                        </div> Loading...
                    `)
                    .attr('disabled', true);

                $("#create-order-form").submit();
            });

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error('{{ $error }}');
                @endforeach
            @endif

            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
        })
    </script>
@stop