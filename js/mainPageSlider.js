const banners = [
    {
        src: "img/banners/banner_1.png",
        link: "catalog.php?category=3&discount=1",
    },
    {
        src: "img/banners/banner_2.png",
        link: "catalog.php?category=1&brand=Razer",
    },
    {
        src: "img/banners/banner_3.png",
        link: "catalog.php?category=4",
    },
];

let currentIndex = 0;
const bannerImg = document.querySelector(".banner-img");
const bannerLink = document.getElementById("bannerLink");
const leftBtn = document.querySelector(".banner-btn-left");
const rightBtn = document.querySelector(".banner-btn-right");

function showBanner(index) {
    bannerImg.src = banners[index].src;
    bannerLink.href = banners[index].link;
}

function nextBanner() {
    currentIndex = (currentIndex + 1) % banners.length;
    showBanner(currentIndex);
}

function prevBanner() {
    currentIndex = (currentIndex - 1 + banners.length) % banners.length;
    showBanner(currentIndex);
}

// Автопрокрутка
setInterval(nextBanner, 3000);

// Кнопки
rightBtn.addEventListener("click", nextBanner);
leftBtn.addEventListener("click", prevBanner);

// Инициализация первого баннера
showBanner(currentIndex);
