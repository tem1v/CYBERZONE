document.querySelectorAll(".order-cart").forEach(cart => {
    const productId = cart.dataset.productId;
    const stars = cart.querySelectorAll(".star-btn");

    stars.forEach(star => {
        star.addEventListener("click", () => {
            const rating = star.dataset.rating;
            const comment = prompt("Оставьте комментарий к товару:");

            if (comment !== null) {
                fetch("server/addReview.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `product_id=${productId}&rating=${rating}&comment=${encodeURIComponent(comment)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Отзыв добавлен!");
						window.location.reload();
                    } else {
                        alert("Ошибка: " + data.message);
                    }
                });
            }
        });
    });
});
