document
    .querySelectorAll("#filterForm select, #filterForm input")
    .forEach((el) => {
        el.addEventListener("change", () => {
            document.getElementById("filterForm").submit();
        });
});


document.getElementById("resetFilters").addEventListener("click", () => {
	const form = document.getElementById("filterForm");

	const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get("sort");
    const currentCategory = urlParams.get("category");

	// Сброс всех select и input
	form.querySelectorAll("select, input").forEach((el) => {
		if (el.name === "category" || el.name === "sort") return;

		if (el.type === "checkbox" || el.type === "radio") {
			el.checked = false;
		} else {
			el.value = "";
		}
	});
	
	if (currentSort) {
        const sortSelect = form.querySelector("select[name='sort']");
        if (sortSelect) {
            sortSelect.value = currentSort;
        }
    }

    // Устанавливаем сохранённую категорию в форму (если она вдруг очищается)
    if (currentCategory) {
        const categoryInput = form.querySelector("select[name='category']");
        if (categoryInput) {
            categoryInput.value = currentCategory;
        }
    }

	form.submit();
});

const sortSelect = document.getElementById("sortSelect");
const sortHidden = document.getElementById("sortHiddenInput");

sortSelect.addEventListener("change", function () {
    sortHidden.value = this.value; 
    document.getElementById("filterForm").submit();
});
