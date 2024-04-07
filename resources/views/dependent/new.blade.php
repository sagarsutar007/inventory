@extends('adminlte::page')

@section('title', 'Add New Dependent Materials')

@section('content')
    
    <div class="row">
        <div class="col-md-6 mx-auto">
            
            <form action="{{ route('dm.store') }}" method="post" autocomplete="off">
            @csrf
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Add New Dependent Materials</h3>
                </div>
                <div class="card-body">
                    <div class="dependent-item-container">
                        <div class="dependent-item">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" name="description[]" placeholder="Description" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group mb-3">
                                        <select name="frequency[]" class="form-control">
                                            <option value="">-- Select Frequency --</option>
                                            <option value="Daily">Daily</option>
                                            <option value="Weekly">Weekly</option>
                                            <option value="Bi-weekly">Bi-weekly</option>
                                            <option value="Monthly">Monthly</option>
                                            <option value="Bi-monthly">Bi-monthly</option>
                                            <option value="Quarterly">Quarterly</option>
                                            <option value="Half-yearly">Half-yearly</option>
                                            <option value="Yearly">Yearly</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-times remove-dependent-item"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-outline-secondary add-dependent-item"><i class="fas fa-fw fa-plus"></i> New</button>
                    <a href="{{ route('dm.index') }}" class="btn btn-outline-danger"><i class="fas fa-fw fa-times"></i> Cancel</a>
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
            // Function to add a new dependent item
            $(".add-dependent-item").click(function () {
                var newItem = $(".dependent-item:first").clone();
                newItem.find('input').val('');
                $(".dependent-item-container").append(newItem);
            });

            // Function to remove the closest dependent item
            $(".dependent-item-container").on('click', '.remove-dependent-item', function () {
                if ($('.dependent-item').length > 1) {
                    $(this).closest('.dependent-item').remove();
                } else {
                    alert("At least one dependent item should be present.");
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