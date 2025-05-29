document.addEventListener("DOMContentLoaded", function () {
    const descriptionBtn = document.querySelector(".description-btn");
    const reviewsBtn = document.querySelector(".reviews-btn");
    const descriptionContainer = document.querySelector(
        ".description-container"
    );
    const reviewsContainer = document.querySelector(".reviews-container");

    descriptionBtn.addEventListener("click", function () {
        descriptionBtn.classList.add("active");
        reviewsBtn.classList.remove("active");

        descriptionContainer.classList.add("visible");
        reviewsContainer.classList.remove("visible");
    });

    reviewsBtn.addEventListener("click", function () {
        reviewsBtn.classList.add("active");
        descriptionBtn.classList.remove("active");

        reviewsContainer.classList.add("visible");
        descriptionContainer.classList.remove("visible");
    });
});
