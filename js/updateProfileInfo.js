document.querySelector(".change-info-btn").addEventListener("click", function () {
    const isEdit = this.textContent === "Сохранить";
    const fields = document.querySelectorAll(".value");
    const cancelBtn = document.querySelector(".delete-account-btn");

    if (!isEdit) {
        // Переключаемся в режим редактирования
        fields.forEach(span => {
            const fieldName = span.dataset.field;
            const currentValue = span.textContent.trim();
            let inputType = "text";

            if (fieldName === "birth_date") inputType = "date";
            if (fieldName === "email") inputType = "email";
            if (fieldName === "phone") inputType = "tel";

            const input = document.createElement("input");
            input.type = inputType;
            input.name = fieldName;
            input.value = fieldName === "birth_date" ? formatDateForInput(currentValue) : currentValue;
            input.classList.add("edit-input");

            span.replaceWith(input);
        });

        this.textContent = "Сохранить";
        cancelBtn.textContent = "Отменить";
    } else {
        // Здесь можно отправить данные на сервер или просто обновить отображение
        const inputs = document.querySelectorAll(".edit-input");
        inputs.forEach(input => {
            const span = document.createElement("span");
            span.classList.add("value");
            span.dataset.field = input.name;

            if (input.name === "birth_date") {
                const formattedDate = new Date(input.value).toLocaleDateString('ru-RU');
                span.textContent = formattedDate;
            } else {
                span.textContent = input.value;
            }

            input.replaceWith(span);
        });

        this.textContent = "Изменить";
        cancelBtn.textContent = "Выйти";
    }
});

document.querySelector(".delete-account-btn").addEventListener("click", function () {
    if (this.textContent === "Отменить") {
        // Отмена редактирования — восстановим оригинальные значения
        location.reload(); // Самый простой способ — перезагрузить страницу
    } else {
        if (confirm("Вы уверены, что хотите выйти?")) {
            window.location.href = "logout.php"; // ваш обработчик выхода
        }
    }
});

function formatDateForInput(dateStr) {
    const parts = dateStr.split(".");
    if (parts.length !== 3) return "";
    const [day, month, year] = parts;
    return `${year}-${month}-${day}`;
}
