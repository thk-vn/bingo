$(document).ready(function () {

    const $toast = $('#toast');

    $("#detailBingoUserForm").on("submit", async function (e) {
        e.preventDefault();

        const userData = JSON.parse(localStorage.getItem('bingo_user'));
        if (!userData) {
            showToast(errroNullInfo);
            return;
        }

        const name = $("#name").val().trim();
        const email = $("#email").val().trim();
        const phone_number = $("#phone_number").val().trim();

        if (!name || !email || !phone_number) {
            showToast(checkInformation);
            return;
        }

        try {
            const res = await $.ajax({
                url: "/bingo/update-user",
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify({ name, email, phone_number }),
                headers: {
                    "X-CSRF-TOKEN": $('input[name="_token"]').val(),
                },
            });

            if (res.status) {
                localStorage.setItem('bingo_user', JSON.stringify(res.data));
                showToast(updateSuccess);
                setTimeout(() => window.location.href = '/bingo/number-plate', 1200);
            } else {
                showToast(updateFail);
            }
        } catch (err) {
            console.error(err);
            if (err.status === 422 && err.responseJSON?.errors) {
                showToast(err.responseJSON.message);
            } else {
                showToast(registerErrorServer);
            }
        }
    });

    function showToast(message) {
        $toast.text(message).addClass('show');
        setTimeout(() => $toast.removeClass('show'), 2500);
    }
});
