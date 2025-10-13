$(document).ready(function () {
    const $form = $('#loginForm');
    const $btn = $('#loginBtn');
    const $toast = $('#toast');

    checkUser();

    $form.on('submit', async function (e) {
        e.preventDefault();

        const name = $('#name').val().trim();
        const department = $('#department').val().trim();
        const phone_number = $('#phone_number').val().trim();

        if (!name || !department || !phone_number) {
            showToast(checkInfomation);
            return;
        }

        $btn.prop('disabled', true).text(buttonPending);

        try {
            const res = await $.ajax({
                url: '/bingo/resgister/user',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ name, department, phone_number }),
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
            showToast(resgisterErrorServer);
        }
    });

    async function checkUser() {
        const userData = JSON.parse(localStorage.getItem('bingo_user'));
        if (!userData?.name || !userData?.phone_number) return;

        try {
            const res = await $.ajax({
                url: '/bingo/check-user',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ name: userData.name, phone_number: userData.phone_number, session_token: userData.session_token }),
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
