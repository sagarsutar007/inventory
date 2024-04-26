@extends('adminlte::page')

@section('title', 'Edit User')

@section('content')
    <div class="row">
        <div class="col-6 mx-auto">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Edit User</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="empid">Employee ID</label>
                                    <input type="text" name="empid" id="empid" class="form-control" value="{{ $user->employee_id }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control" value="{{ $user->phone }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label for="gender">Gender</label>
                                    <select name="gender" id="gender" class="form-control" required>
                                        <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ $user->gender === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label for="role">Department</label>
                                    <select name="role" id="role" class="form-control" required>
                                        <option hidden>Department</option>
                                        @if ($roles)
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" {{ $user->role->role_id === $role->id ? 'selected' : '' }} >{{ $role->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="password">Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control">
                                        <div class="input-group-append">
                                            <span class="input-group-text toggle-password">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="status">Status</label> <br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="active" value="1" {{ $user->status === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">Active</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="inactive" value="0" {{ $user->status === '0' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="inactive">In-active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-end">
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