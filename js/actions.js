window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
document.addEventListener("DOMContentLoaded", function () {
    const cartButtons = document.querySelectorAll(".add-to-cart");
    const favButtons = document.querySelectorAll(".add-to-favorites");
    const addToCartBtn = document.querySelector(".add-to-cart-btn");

    if (addToCartBtn) {
        addToCartBtn.addEventListener("click", function () {
            const productId = this.dataset.id;
            const button = this;

            fetch("server/toggle_cart.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "product_id=" + encodeURIComponent(productId),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.in_cart) {
                        button.textContent = "Убрать из корзины";
                    } else {
                        button.textContent = "Добавить в корзину";
                    }
					const counter = document.querySelector(".cart-counter");
                    if (counter) {
                        let current = parseInt(counter.textContent);
                        counter.textContent = data.in_cart
                            ? current + 1
                            : current - 1;
                    }
                })
                .catch((error) =>
                    console.error("Ошибка при добавлении в корзину:", error)
                );
        });
    }

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

            fetch("../server/toggle_favorites.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "product_id=" + productId,
            })
                .then((response) => response.json())
                .then((data) => {
                    img.src = data.in_favorite
                        ? "img/icons/heart_red.png"
                        : "img/icons/heart_black.png";
                });
        });
    });
});
