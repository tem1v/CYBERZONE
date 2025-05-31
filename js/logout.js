document
    .querySelector(".delete-account-btn")
    .addEventListener("click", function () {
        if (confirm("Вы действительно хотите выйти из аккаунта?")) {
            window.location.href = "../server/logout.php";
        }
    });
