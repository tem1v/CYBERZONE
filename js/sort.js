document.getElementById("sortSelect").addEventListener("change", function () {
    const selected = this.value;
    const urlParams = new URLSearchParams(window.location.search);

    urlParams.set("sort", selected); // Устанавливаем параметр sort

    window.location.search = urlParams.toString(); // Обновляем URL
});
