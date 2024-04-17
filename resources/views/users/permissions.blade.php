@extends('adminlte::page')

@section('title', 'Edit Permissions')

@section('content')
    <div class="row">
        <div class="col-8 mx-auto">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">{{ $user->employee_id . " - " . $user->name }}</h3> <br>
                    <p class="text-sm text-secondary m-0">Current Role: {{$user->role->role->name}}</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.setPermission', $user->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            @foreach ($permissions as $index => $item)
                                @if ($index % 4 == 0)
                                    <div class="col-md-3">
                                        <div class="card">
                                            <div class="card-body">
                                @endif

                                <div class="form-check py-2">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" id="{{ $item->id }}" value="{{ $item->id }}"
                                    @if ($userPermissions->contains('permission_id', $item->id)) checked @endif>
                                    <label class="form-check-label" for="{{ $item->id }}">{{ $item->name }}</label>
                                </div>

                                @if (($index + 1) % 4 == 0 || $loop->last)
                                    </div> </div> </div>
                                @endif
                            @endforeach
                        </div>
                        <div class="d-flex align-items-end justify-content-end">
                            <a href="{{ route('users') }}" class="btn btn-outline-secondary mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
@stop



@section('js')
    <script>
        $(function () {

            $(".toggle-password").click(function() {
                $(this).toggleClass("active");
                var input = $("#password");
                if (input.attr("type") === "password") {
                    input.attr("type", "text");
                } else {
                    input.attr("type", "password");
                }
            });

            // Show Error Messages
            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif

            // Show Success Message
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
        });
    </script>
@stop