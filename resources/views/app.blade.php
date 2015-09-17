<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>RAIDMIRROR</title>

    <!-- CSS -->
    <link href="{{ asset('/css/vendor/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/vendor/morris.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    @yield('styles')

    <!-- Font -->
    <link href="{{ asset('/css/vendor/font-awesome.css') }}" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="{{ asset('/js/vendor/html5shiv.js') }}"></script>
    <script src="{{ asset('/js/vendor/respond.js') }}"></script>
    <![endif]-->
</head>
<body>
@yield('navigation')

@yield('content')

<!-- Script -->
<script src="{{ asset('/js/vendor/jquery.js') }}"></script>
<script src="{{ asset('/js/vendor/bootstrap.js') }}"></script>
<script src="{{ asset('/js/vendor/raphael.js') }}"></script>
<script src="{{ asset('/js/vendor/morris.js') }}"></script>
<script src="{{ asset('/js/vendor/jquery.flot.js') }}"></script>
<script src="{{ asset('/js/vendor/jquery.flot.time.js') }}"></script>

@yield('scripts')
</body>
</html>
