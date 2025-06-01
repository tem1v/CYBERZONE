const input = document.getElementById("searchInput");
const resultsContainer = document.getElementById("searchResults");

input.addEventListener("input", () => {
    const query = input.value.trim();

    if (query.length < 2) {
        resultsContainer.style.display = "none";
        return;
    }

    fetch(`../server/searchDropdown.php?q=${encodeURIComponent(query)}`)
        .then((res) => res.json())
        .then((data) => {
            resultsContainer.innerHTML = "";

            if (data.length === 0) {
                resultsContainer.style.display = "none";
                return;
            }

            data.forEach((item) => {
                const div = document.createElement("div");
                div.textContent = item.name;

                div.addEventListener("click", () => {
                    if (item.type === "product") {
                        window.location.href = `goodPage.php?id=${item.id}`;
                    } else if (item.type === "category") {
                        window.location.href = `catalog.php?category=${item.id}`;
                    }
                });

                resultsContainer.appendChild(div);
            });

            resultsContainer.style.display = "block";
        })
        .catch(() => {
            resultsContainer.style.display = "none";
        });
});

document.addEventListener("click", (e) => {
    if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
        resultsContainer.style.display = "none";
    }
});
