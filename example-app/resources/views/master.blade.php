<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Bingo — Game đơn giản</title>
  @vite(['resources/css/index.css'])
  {{-- <link href="{{asset('resources/css/index.css')}}" rel="stylesheet" type="text/css" /> --}}
</head>
<body>
  @yield('main')

  @yield('script')
</body>
</html>
