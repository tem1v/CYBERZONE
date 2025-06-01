// document.getElementById("sortSelect").addEventListener("change", function () {
//     const selected = this.value;
//     const urlParams = new URLSearchParams(window.location.search);

//     urlParams.set("sort", selected);

//     window.location.search = urlParams.toString();
// });

document.getElementById("sortSelect").addEventListener("change", function () {
    const sortValue = this.value;
    const form = document.getElementById("filterForm");

    // Добавляем/обновляем hidden input с именем sort
    let sortInput = form.querySelector("input[name='sort']");
    if (!sortInput) {
        sortInput = document.createElement("input");
        sortInput.type = "hidden";
        sortInput.name = "sort";
        form.appendChild(sortInput);
    }
    sortInput.value = sortValue;

    form.submit(); // Отправляем форму с новым значением sort
});
