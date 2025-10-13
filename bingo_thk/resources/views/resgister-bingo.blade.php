@extends('master')

@section('css')
    @vite(['resources/css/login.css', 'resources/css/animation.css'])
@endsection

@section('main')
    <div class="card">
        <h1 class="neon-title">
            <span class="logo-dot"></span>
            {{ __("view.bingo_user.resgister_account") }}
        </h1>

        <form id="loginForm" action="" method="POST">
            @csrf
            <input class="input" id="name"
                name="name" type="text"
                placeholder="{{ __('view.placeholder.name') }}"
            />
            <input class="input" id="department"
                name="department"
                type="text"
                placeholder="{{ __('view.placeholder.department') }}"
            />
            <input class="input" id="phone_number"
                name="phone_number"
                type="text"
                placeholder="{{ __('view.placeholder.phone_number') }}"
            />
            <button id="loginBtn" class="btn" type="submit">
                {{ __("view.bingo_user.btn_resgister") }}
            </button>

            <div class="footer">
                {{ __("view.bingo_user.start") }}
            </div>

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div id="toast" class="toast show">{{ $error }}</div>
                @endforeach
            @elseif (session('error'))
                <div id="toast" class="toast show">
                    {{ session('error') }}
                </div>
            @else
                <div id="toast" class="toast">
                    {{ __("view.bingo_user.start") }}
                </div>
            @endif

            <div id="toast" class="toast">
                {{ __("view.bingo_user.start") }}
            </div>
        </form>
    </div>

    @push('section-scripts')
        <script>
            const checkInfomation = "{{ __('view.bingo_user.check_info') }}";
            const buttonPending = "{{ __('view.button.pending') }}";
            const resgisterFail = "{{ __('view.resgister.fail') }}";
            const resgisterSuccess = "{{ __('view.resgister.success') }}";
            const resgisterErrorServer = "{{ __('view.resgister.error_server') }}";
            const resgisterGoBack = "{{ __('view.resgister.go_back') }}";
        </script>
        @vite('resources/js/page/bingo-user.js')
    @endpush

@endsection
