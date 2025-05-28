const banners = [
	'img/banners/banner_1.png',
	'img/banners/banner_2.png',
	'img/banners/banner_3.png'
];

let currentIndex = 0;
const bannerImg = document.querySelector('.banner-img');
const leftBtn = document.querySelector('.banner-btn-left');
const rightBtn = document.querySelector('.banner-btn-right');

function showBanner(index) {
		bannerImg.src = banners[index];
}

function nextBanner() {
	currentIndex = (currentIndex + 1) % banners.length;
	showBanner(currentIndex);
}

function prevBanner() {
	currentIndex = (currentIndex - 1 + banners.length) % banners.length;
	showBanner(currentIndex);
}

// Автоматическая прокрутка
setInterval(nextBanner, 3000);

// Управление кнопками
rightBtn.addEventListener('click', nextBanner);
leftBtn.addEventListener('click', prevBanner);
