@extends('master')

@section('css')
    @vite(['resources/css/login.css', 'resources/css/animation.css'])
@endsection

@section('main')
    <div class="card">
        <h1 class="neon-title">
            <span class="logo-dot"></span>
            {{ __("view.bingo_user.register_account") }}
        </h1>

        <form id="loginForm" action="" method="POST">
            @csrf
            <input class="input" id="name"
                name="name"
                type="text"
                placeholder="{{ __('view.placeholder.name') }}"
            />
            <input class="input" id="email"
                name="email"
                type="text"
                placeholder="{{ __('view.placeholder.email') }}"
            />
            <input class="input" id="phone_number"
                name="phone_number"
                type="text"
                placeholder="{{ __('view.placeholder.phone_number') }}"
            />
            <button id="loginBtn" class="btn" type="submit">
                {{ __("view.bingo_user.btn_register") }}
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
