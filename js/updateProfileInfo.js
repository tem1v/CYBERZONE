document
    .querySelector(".change-info-btn")
    .addEventListener("click", function () {
        const isEdit = this.textContent === "Сохранить";
        const fields = document.querySelectorAll(".value, .edit-input");
        const cancelBtn = document.querySelector(".delete-account-btn");

        if (!isEdit) {
            fields.forEach((span) => {
                if (!span.classList.contains("value")) return;

                const fieldName = span.dataset.field;
                const currentValue = span.textContent.trim();
                let inputType = "text";

                if (fieldName === "email") inputType = "email";
                if (fieldName === "phone") inputType = "tel";

                const input = document.createElement("input");
                input.type = inputType;
                input.name = fieldName;
                input.value = currentValue;
                input.classList.add("edit-input");

                span.replaceWith(input);
            });

            this.textContent = "Сохранить";
            cancelBtn.textContent = "Отменить";
        } else {
			const inputs = document.querySelectorAll(".edit-input");
            const formData = new FormData();

            inputs.forEach((input) => {
                formData.append(input.name, input.value);
            });

            fetch("../server/updateProfile.php", {
                method: "POST",
                body: formData,
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        inputs.forEach((input) => {
                            const span = document.createElement("span");
                            span.classList.add("value");
                            span.dataset.field = input.name;
                            span.textContent = input.value;
                            input.replaceWith(span);
							location.reload();
                        });

                        this.textContent = "Изменить";
                        cancelBtn.textContent = "Выйти";
                    } else {
                        alert(
                            "Ошибка: " +
                                (data.message || "Не удалось обновить данные")
                        );
                    }
                })
                .catch(() => {
                    alert("Произошла ошибка при обновлении профиля");
                });

        }
    });

document
    .querySelector(".delete-account-btn")
    .addEventListener("click", function () {
        if (this.textContent === "Отменить") {
            location.reload();
        } else {
            if (confirm("Вы уверены, что хотите выйти?")) {
                window.location.href = "../server/logout.php";
            }
        }
});
