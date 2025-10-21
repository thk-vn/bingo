<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bingo — Game đơn giản</title>
    @yield('css')
</head>

<body>
    @yield('main')

    @vite(['resources/js/app.js'])

    @yield('script')

    @stack('section-scripts')
</body>

</html>
