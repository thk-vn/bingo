$(document).ready(function () {
    const $formLogin = $('#loginForm');
    const $toast = $('#toast');

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
                setTimeout(() => window.location.href = '/bingo/number-plate', 1200);
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
});
