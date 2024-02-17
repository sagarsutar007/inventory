@extends('adminlte::page')

@section('title', 'Add New Commodities')

@section('content')
    
    <div class="row">
        <div class="col-md-6 mx-auto">
            
            <form action="{{ route('commodities.store') }}" method="post" autocomplete="off">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Add New Commodities</h3>
                </div>
                <div class="card-body">
                    <div class="commodity-item-container">
                        <div class="commodity-item d-flex align-items-center">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="commodities[]" placeholder="Commodity name" required>
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-times remove-commodity-item"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-outline-secondary add-commodity-item"><i class="fas fa-fw fa-plus"></i> New</button>
                    <a href="{{ route('commodities') }}" class="btn btn-outline-danger"><i class="fas fa-fw fa-times"></i> Cancel</a>
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
            // Function to add a new commodity item
            $(".add-commodity-item").click(function () {
                var newItem = $(".commodity-item:first").clone();
                newItem.find('input').val('');
                $(".commodity-item-container").append(newItem);
            });

            // Function to remove the closest commodity item
            $(".commodity-item-container").on('click', '.remove-commodity-item', function () {
                if ($('.commodity-item').length > 1) {
                    $(this).closest('.commodity-item').remove();
                } else {
                    alert("At least one commodity item should be present.");
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