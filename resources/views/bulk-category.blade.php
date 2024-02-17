@extends('adminlte::page')

@section('title', 'Bulk Upload Categories')

@section('content')
    
    <div class="row">
        <div class="col-md-6 mx-auto">
            
            <form action="{{ route('categories.bulkStore') }}" method="post" autocomplete="off" enctype="multipart/form-data">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Bulk Upload Categories</h3>
                </div>
                <div class="card-body">
                    <div class="category-item-container">
                        <div class="category-item d-flex align-items-center">
                            <div class="form-group w-100">
                                <label for="excel-file">Upload CSV/Excel</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="file" id="excel-file" accept=".csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                                        <label class="custom-file-label" for="excel-file">Choose file</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ asset('assets/formats/bulk-categories.xlsx') }}" type="button" class="btn btn-outline-success"><i class="fas fa-file-export"></i> Download Format</a>
                    <a href="{{ route('categories') }}" class="btn btn-outline-danger"><i class="fas fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-check"></i> Submit</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    
@stop


@section('js')
    <script>
        $(function () {
            // Function to show uploaded file name 
            $('input[type="file"]').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                if (fileName === "") {
                    $(this).siblings('.custom-file-label').text("Choose file");
                } else {
                    $(this).siblings('.custom-file-label').text(fileName);
                }
            });

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