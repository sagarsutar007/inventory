@extends('adminlte::page')

@section('title', 'Settings')

@section('content')
    
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Settings</h3>
                </div>
                <div class="card-body">
                    
                </div>
            </div>
        </div>
    </div>
    
@stop


@section('js')
    <script>
        $(function () {

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