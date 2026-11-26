<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0369A1">
    <title>@yield('title', 'SIMTAG') - SIMTAG</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/simtag.css') }}">
    <script src="{{ asset('js/simtag.js') }}" defer></script>
</head>
<body class="@yield('bodyclass')">
    <a class="skip-link" href="#isi">Langsung ke isi halaman</a>
    @yield('body')
</body>
</html>
