<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>SSE | Shri Sai Electricals</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        {{-- Bootstrap 5  --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            /* Custom CSS */
            body {
                background-image: url('/assets/img/SSE_BACKGROUND.jpg');
                background-size: 100% 100%;
                background-repeat: no-repeat;
                height: 98vh;
            }
        </style>
    </head>
    <body class="bg-dark">
        <header class="mb-auto mt-3">
            <div class="container">
                <h3 class="float-md-start mb-0">
                    <img src="/assets/img/logo.png" width="100px" alt="">
                </h3>
                <nav class="nav nav-masthead justify-content-center float-md-end">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/home') }}" class="nav-link fw-bold py-1 px-0 text-white active">Home</a>
                        @else
                            <a href="{{ route('login') }}" class="nav-link fw-bold py-1 px-0 text-white">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="nav-link fw-bold py-1 px-0 text-white">Register</a>
                            @endif
                        @endauth
                    @endif
                </nav>
            </div>
        </header>
        <main>
            <div class="container">
                <div class="row">
                    <div class="col-8 mx-auto" style="margin-top: 40%;">
                        @if(session('error'))
                            <div class="alert alert-danger text-center">
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
        <footer class="mt-auto text-white-50 text-center w-100" style="position: fixed; bottom: 0;">
            <p class="text-center">Powered by <a href="#" class="text-white">Kiaan IT Solutions</a></p>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    </body>
</html>
