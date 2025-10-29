<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quay Số</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js', 'resources/css/dial.css'])
</head>
<body>
    <div id="container"></div>
    <div class="group-numbers">
        <div class="numbers-grid" id="numbersGrid"></div>
    </div>
    <div class="group-button">
        <button class="btn-reset">RESET</button>
    </div>

<script src="{{ asset('js/libs/three.min.js') }}"></script>
@vite(['resources/js/page/bingo/dial.js'])
</body>
</html>
