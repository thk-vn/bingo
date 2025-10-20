$(document).ready(function () {
    const $formLogin = $('#loginForm');
    const $toast = $('#toast');
    const defaultMarkedCells = Array.from({
        length: 5
    }, () => Array(5).fill(false));
    let card = [];
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    // Save default marked_cells
    if (!localStorage.getItem('marked_cells')) {
        localStorage.setItem('marked_cells', JSON.stringify(defaultMarkedCells));
    }

    checkUser();

    $formLogin.on('submit', async function (e) {
        e.preventDefault();

        const name  = $('#name').val().trim();
        const email = $('#email').val().trim();
        const phone_number = $('#phone_number').val().trim();

        if (!name || !email || !phone_number) {
            showToast(checkInfomation);
            return;
        }

        try {
            const res = await $.ajax({
                url: '/bingo/resgister/user',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ name, email, phone_number }),
                headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
            });

            if (res.status) {
                localStorage.setItem('bingo_user', JSON.stringify(res.data));
                showToast(resgisterSuccess);
                generateCard();
                saveBoardGame();
            } else {
                showToast(resgisterFail);
            }
        } catch (err) {
            console.error(err);
            if (err.status === 422 && err.responseJSON?.errors) {
                showToast(err.responseJSON.message);
            } else {
                showToast(resgisterErrorServer);
            }
        }

    });

    async function checkUser() {
        const userData = JSON.parse(localStorage.getItem('bingo_user'));
        if ( !userData?.name || !userData?.email || !userData?.phone_number) return;

        try {
            const res = await $.ajax({
                url: '/bingo/check-user',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ name: userData.name, email: userData.email, phone_number: userData.phone_number }),
                headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
            });

            if (res.status) {

                showToast(resgisterGoBack + userData.name + '!');
                setTimeout(() => window.location.href = '/bingo/number-plate', 1000);
            } else {
                localStorage.removeItem('bingo_user');
            }
        } catch (error) {
            console.error(error);
        }
    }

    function showToast(message) {
        $toast.text(message).addClass('show');
        setTimeout(() => $toast.removeClass('show'), 2500);
    }

    function rndSample(total, min, max) {
        const pool = Array.from({
            length: max - min + 1
        }, (_, i) => i + min);
        // Fisher-Yates shuffle
        for (let i = pool.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [pool[i], pool[j]] = [pool[j], pool[i]];
        }
        return pool.slice(0, total);
    }

    function generateCard() {
            // return random data
            let numbers = [];
            numbers = rndSample(25, 1, 50);
            card = [];
            for (let row = 0; row < 5; row++) {
                card[row] = [];
                for (let column = 0; column < 5; column++) {
                    const index = row * 5 + column;
                    card[row][column] = numbers[index];
                }
            }
            // Save bingo_board into local storage
            localStorage.setItem('bingo_board', JSON.stringify(card));
    }

    async function saveBoardGame() {
        try {
            const bingo_board = JSON.parse(localStorage.getItem('bingo_board'));
            const marked_cells = JSON.parse(localStorage.getItem('marked_cells'));
            const url = '/bingo/save-board-game';
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    bingo_board,
                    marked_cells
                }),
            });

            // Check HTTP status
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            setTimeout(() => window.location.href = '/bingo/number-plate', 1200);
        } catch (err) {
            console.error(err);
            // `resgisterErrorServer` is defined in the blade view; fallback to a generic message
            showToast(typeof resgisterErrorServer !== 'undefined' ? resgisterErrorServer : 'Server error, please try again.');
        }
    }
});
