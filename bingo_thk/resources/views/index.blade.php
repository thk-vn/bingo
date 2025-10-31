@extends('master')

@section('css')
    @vite(['resources/css/index.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endsection

@section('main')
    <div class="app" role="application" aria-label="Bingo game">
        <div class="logo-fixed">BINGO ✨</div>
        <div class="user-info">
            <a href="{{ route('bingo.detail', auth('bingo')->user()) }}" id="info">{{ $userBingoName }} 🖋️</a>
        </div>
        <div id="bingoWinOverlay">
            <div class="bingo-text show-bingo">🎉 BINGO!!! 🎉</div>
            <div class="canvas" id="confetti"></div>
        </div>
        <div class="card-wrap" aria-live="polite">
            <div class="card-bingo">
                <div id="bingo" class="bingo" role="grid" aria-label="Bingo card">
                </div>
            </div>
            <div class="controls">
                <button id="reset" class="small reset">Reset</button>
            </div>
        </div>
    </div>
    <footer class="small">
        © 2025 - THK Holdings Vietnam
    </footer>
@endsection

@section('script')
    @vite('resources/js/page/bingo/bingo-board.js')
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        // --- Create star for background ---
        for (let i = 0; i < 40; i++) {
            const star = document.createElement("div");
            star.className = "star";
            const size = Math.random() * 2;
            star.style.width = `${size}px`;
            star.style.height = `${size}px`;
            star.style.top = `${Math.random() * 100}vh`;
            star.style.left = `${Math.random() * 100}vw`;
            star.style.animationDelay = `${Math.random() * 3}s`;
            document.body.appendChild(star);
        }
    </script>
@endsection
