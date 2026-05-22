@extends('master')

@section('css')
    @vite(['resources/css/v2/index.css'])
    <link rel="stylesheet" href="{{ asset('css/all.min.css') }}">
@endsection

@section('main')
    <div class="game-container">

        <div class="main-board"></div>

        <div class="logo-fixed">
            <img class="logo" src="{{ Vite::asset('resources/images/logo-bingo-2026-removebg-preview.png') }}"
                alt="Bingo Logo">
            <div class="title">
                BINGO <span>Game</span>
            </div>
        </div>
        <main class="app" role="application" aria-label="Bingo game">
            <div class="user-info-fixed">
                <span class="label">Họ tên:</span>
                <a href="{{ route('bingo.detail', auth('bingo')->user()) }}" id="info">
                    {{ $userBingoName }} <span class="edit-icon">🖋️</span>
                </a>
            </div>
            <div id="bingoWinOverlay">
                {{-- <div class="bingo-text show-bingo">🎉 BINGO!!! 🎉</div> --}}
                <div class="bingo-text show-bingo">
                    <img src="{{ Vite::asset('resources/images/popup-bingo.gif') }}" alt="">
                </div>
                <div class="canvas" id="confetti"></div>
            </div>

            <div class="card-wrap" aria-live="polite">
                <div class="card-bingo">
                    <div id="bingo" class="bingo" role="grid" aria-label="Bingo card">
                    </div>
                </div>
            </div>

            <div class="controls-area">
            </div>

            <div class="button-reset">
                <button id="reset" class="small reset">Reset</button>
            </div>
        </main>
    </div>
    <footer class="system-footer small">
        © 2025 - THK Holdings Vietnam
    </footer>
@endsection

@section('script')
    @vite('resources/js/page/bingo/bingo-board.js')
    <script src="{{ asset('js/libs/confetti.browser.min.js') }}"></script>
    <script>
        // --- Tối ưu hóa hiệu năng tạo sao nền bằng DocumentFragment ---
        const fragment = document.createDocumentFragment();
        const starCount = window.innerWidth < 768 ? 20 : 40; // Mobile thì tạo ít sao lại cho đỡ lag máy

        for (let i = 0; i < starCount; i++) {
            const star = document.createElement("div");
            star.className = "star";
            const size = Math.random() * 2;
            star.style.width = `${size}px`;
            star.style.height = `${size}px`;
            star.style.top = `${Math.random() * 100}vh`;
            star.style.left = `${Math.random() * 100}vw`;
            star.style.animationDelay = `${Math.random() * 3}s`;
            fragment.appendChild(star);
        }
        document.body.appendChild(fragment);
    </script>
@endsection
