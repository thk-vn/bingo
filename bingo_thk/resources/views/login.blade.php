@extends('master')

@section('css')
    @vite(['resources/css/login.css', 'resources/css/animation.css'])
@endsection

@section('main')
    <div class="card">
        <h1 class="neon-title"><span class="logo-dot"></span>Login</h1>

        <form id="loginForm" action="{{ route('bingo.login') }}" method="POST">
            @csrf
            <input class="input" id="email" name="email" type="text" placeholder="Email or user name" required />
            <input class="input" id="password" name="password" type="password" placeholder="Password" required />
            <button id="loginBtn" class="btn" type="button">Login</button>
            <div class="footer">💫 Bấm đi — chờ chi 💫</div>
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div id="toast" class="toast show">{{ $error }}</div>
                @endforeach
            @elseif (session('error'))
                <div id="toast" class="toast show">{{ session('error') }}</div>
            @else
                <div id="toast" class="toast">Bấm đi — chờ chi!</div>
            @endif

            <div id="toast" class="toast">Bấm đi — chờ chi!</div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        const toast = document.getElementById('toast');
        const card = document.querySelector('.card');
        const form = document.querySelector('#loginForm');
        const btn = document.querySelector('#loginBtn');

        function makeConfetti() {
            const count = 16;
            for (let i = 0; i < count; i++) {
                const c = document.createElement('div');
                const colors = ['#ff4ec6', '#14f6ff', '#b48cff', '#fff65e', '#7dff9a'];
                c.style.position = 'absolute';
                c.style.width = '6px';
                c.style.height = '6px';
                c.style.background = colors[Math.floor(Math.random() * colors.length)];
                c.style.left = '50%';
                c.style.top = '50%';
                c.style.borderRadius = '2px';
                c.style.transform = `translate(${(Math.random()-0.5)*160}px,${(Math.random()-0.5)*160}px)`;
                c.style.opacity = 0;
                c.style.animation = `pop 1s ease-out forwards`;
                c.style.animationDelay = `${Math.random()*0.2}s`;
                card.appendChild(c);
                setTimeout(() => c.remove(), 1100);
            }
        }

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            let message = '';
            if (!email && !password) {
                message = 'Please fill email or password!';
                showErrorMessages(message);
            } else if (!email) {
                message = 'Please fill email!';
                showErrorMessages(message);
            } else if (!password) {
                message = 'Please fill password!';
                showErrorMessages(message);
            } else {
                form.submit();
            }
        });

        function showErrorMessages(message) {
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 10000);
        }
    </script>
@endsection
