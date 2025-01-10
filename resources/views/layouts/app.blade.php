<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" />


    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        .article-container {
            display: flex;
            gap: 15px;
            height: 100%;
        }

        .article-image {
            flex: 1;
            max-width: 33%;
        }

        .article-content {
            flex: 2;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .article-title {
            font-weight: bold;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .article-description {
            font-size: 1rem;
            color: #666;
        }

        .carousel-control-prev,
        .carousel-control-next {
            top: 80%;
            transform: translateY(-50%);
            background-color: rgba(209, 15, 15, 0.5);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: invert(1);
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .card-img-top {
            height: 350px;
            object-fit: cover;
        }

        .card-body-height {
            height: 200px;
        }
    </style>
</head>

<body class="antialiased">
    <nav class="navbar navbar-dark bg-dark navbar-expand-lg ">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">News APIS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('newsApi') }}">News API</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('newYorkApi') }}">New York Times API</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('guardianNewsApi') }}">Guardian News API</a>
                    </li>

                </ul>

            </div>
        </div>
    </nav>

    <div class="container-fluid">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @yield('scripts')


</body>

</html>
