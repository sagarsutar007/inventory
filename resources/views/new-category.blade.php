@extends('adminlte::page')

@section('title', 'Add New Categories')

@section('content')
    <div class="row">
        <div class="col-md-6 mx-auto">
            <form action="{{ route('categories.store') }}" method="post" autocomplete="off">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Add New Categories</h3>
                    <div class="card-tools">
                        <a href="{{ route('categories') }}" class="btn btn-primary btn-sm"><i class="fas fa-fw fa-th-large"></i> View Categories</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="category-item-container">
                        <div class="category-item d-flex align-items-center">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="categories[]" placeholder="Category name">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-times remove-category-item"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-outline-secondary add-category-item"><i class="fas fa-fw fa-plus"></i> New</button>
                    <a href="{{ route('categories') }}" class="btn btn-outline-danger"><i class="fas fa-fw fa-times"></i> Cancel</a>
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
            $(".add-category-item").click(function () {
                var newItem = $(".category-item:first").clone();
                newItem.find('input').val('');
                $(".category-item-container").append(newItem);
            });

            // Function to remove the closest category item
            $(".category-item-container").on('click', '.remove-category-item', function () {
                if ($('.category-item').length > 1) {
                    $(this).closest('.category-item').remove();
                } else {
                    alert("At least one category item should be present.");
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