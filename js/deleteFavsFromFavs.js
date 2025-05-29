window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const cartButtons = document.querySelectorAll(".add-to-cart");
    const favButtons = document.querySelectorAll(".add-to-favorites");

    cartButtons.forEach((btn) => {
        btn.addEventListener("click", function () {
            const productId = this.dataset.id;
            const img = this.querySelector("img");

            fetch("../server/toggle_cart.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "product_id=" + productId,
            })
                .then((response) => response.json())
                .then((data) => {
                    img.src = data.in_cart
                        ? "img/icons/shopping-cart_green.png"
                        : "img/icons/shopping-cart_black.png";

                    const counter = document.querySelector(".cart-counter");
                    if (counter) {
                        let current = parseInt(counter.textContent);
                        counter.textContent = data.in_cart
                            ? current + 1
                            : current - 1;
                    }
                });
        });
    });

    favButtons.forEach((btn) => {
        btn.addEventListener("click", function () {
            const productId = this.dataset.id;
            const img = this.querySelector("img");
            const card = this.closest(".card");

            fetch("../server/toggle_favorites.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "product_id=" + productId,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.in_favorite) {
                        img.src = "img/icons/heart_red.png";
                    } else {
                        img.src = "img/icons/heart_black.png";
                        if (card) {
                            card.remove();
                        }
                    }
                });
        });
    });
});
