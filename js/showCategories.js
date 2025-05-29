function showCategories() {
    const dropdown = document.getElementById("dropdown");
	console.log(dropdown)
    if (dropdown.style.visibility === "visible") {
        dropdown.style.visibility = "hidden";
        dropdown.style.opacity = "0";
    } else {
        dropdown.style.visibility = "visible";
        dropdown.style.opacity = "1";
    }
}


// Глобальный клик по документу
document.addEventListener("click", function (event) {
    const dropdown = document.getElementById("dropdown");
    const trigger = document.getElementById("categoryDropdownTrigger");

    // Если клик был вне области дропдауна и триггера — скрываем
    if (!trigger.contains(event.target)) {
        dropdown.style.visibility = "hidden";
        dropdown.style.opacity = "0";
    }
});

function openLoginModal(e) {
    e.preventDefault();
    const modal = document.getElementById("login-modal");
    if (modal) {
        modal.style.display = "flex";
        document.body.style.overflow = "hidden"; // запретить прокрутку
    }
}
