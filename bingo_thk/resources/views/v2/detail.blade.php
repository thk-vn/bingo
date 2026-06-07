@extends('master')

@section('css')
    @vite(['resources/css/v2/login.css', 'resources/css/animation.css'])
@endsection

@section('main')
    <div class="main-board"></div>
    <div class="page-wrapper">
        <div class="logo-card">
            <img class="logo" src="{{ Vite::asset('resources/images/2026 — Code The Wave.png') }}"
                 alt="Bingo Logo">
            <div class="title">
                BINGO <span>Game</span>
            </div>
        </div>
        <div class="main-card">
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div id="toast" class="toast show">{{ $error }}</div>
                @endforeach
            @elseif (session('error'))
                <div id="toast" class="toast show">
                    {{ session('error') }}
                </div>
            @else
                <div id="toast" class="toast"></div>
            @endif
            <div class="card">
                <h1 class="neon-title">
                    <span class="logo-dot"></span>
                    {{ __('view.bingo_user.update') }}
                </h1>

                <form id="detailBingoUserForm" action="" method="POST">
                    @csrf
                    <input class="input" id="name" name="name" type="text"
                           value="{{ old('name', $bingoUser->name) }}" placeholder="{{ __('view.placeholder.name') }}" />
                    <input class="input" id="email" name="email" type="text"
                           value="{{ old('email', $bingoUser->email) }}" placeholder="{{ __('view.placeholder.email') }}" />
                    <button id="btnUpdateInfoBingoUser" class="btn" type="submit">
                        {{ __('view.bingo_user.btn_confirm') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('section-scripts')
        <script>
            const checkInformation = "{{ __('view.bingo_user.check_info') }}";
            const errroNullInfo = "{{ __('view.update.null_info') }}";
            const updateSuccess = "{{ __('view.update.success') }}";
            const updateFail = "{{ __('view.update.fail') }}";
            const updateErrorServer = "{{ __('view.register.error_server') }}";
        </script>
        @vite('resources/js/page/bingo/detail.js')
    @endpush

@endsection
