@extends('master')

@section('css')
    @vite(['resources/css/v2/login.css', 'resources/css/animation.css'])
@endsection

@section('main')
    <div class="main-board"></div>
    <div class="page-wrapper">
        <div class="logo-fixed">
            <img class="logo" src="{{ Vite::asset('resources/images/2026 — Code The Wave.png') }}"
                 alt="Bingo Logo">
            <div class="title">
                BINGO <span>Game</span>
            </div>
        </div>

        <div class="main-container">
            @if ($errors->any())
                <div class="toast show">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @elseif (session('error'))
                <div class="toast show">
                    {{ session('error') }}
                </div>
            @endif
            <div class="card">
                <h1 class="neon-title">
                    {{ __('view.bingo_user.enter_the_game') }}
                </h1>

                <form id="loginForm" action="" method="POST">
                    @csrf
                    <input class="input" id="name" name="name" type="text"
                           placeholder="{{ __('view.placeholder.name') }}" />
                    <input class="input" id="email" name="email" type="text"
                           placeholder="{{ __('view.placeholder.email') }}" />
                    <button id="loginBtn" class="btn" type="submit">
                        {{ __('view.bingo_user.btn_play_now') }}
                    </button>

                </form>
            </div>
        </div>
    </div>

    @push('section-scripts')
        <script>
            const checkInformation = "{{ __('view.bingo_user.check_info') }}";
            const buttonPending = "{{ __('view.button.pending') }}";
            const registerFail = "{{ __('view.register.fail') }}";
            const registerSuccess = "{{ __('view.register.success') }}";
            const registerErrorServer = "{{ __('view.register.error_server') }}";
            const registerGoBack = "{{ __('view.register.go_back') }}";
        </script>
        @vite('resources/js/page/bingo/register.js')
    @endpush
@endsection
