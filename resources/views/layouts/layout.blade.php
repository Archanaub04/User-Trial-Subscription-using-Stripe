<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="{{ asset('css/template-style.css') }}">

    <title>Trial & Subscription App</title>
</head>

<body>

    @include('layouts.nav')

    @yield('content')

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>

    @stack('scripts')

    <script>
        $(document).ready(function() {
            $('.logout-user').click(function() {
                $.ajax({
                    type: "POST",
                    url: "{{ route('logout') }}",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            location
                        .reload(); // to reload the page after logout successful. Because, after reload due to out middleware redirect to login page if not login (guest, auth), back button not work.

                            // to prevent back button, need to make redirect to login page if user mot login using middle ware. can use guest middleware
                        } else {
                            alert("Something went wrong!");
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
