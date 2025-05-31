document.querySelector('.make-order-btn').addEventListener('click', () => {
    fetch('../server/create_order.php', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Заказ успешно оформлен!');
            location.reload();
        } else {
            alert('Ошибка оформления заказа: ' + data.message);
        }
    })
    .catch(() => alert('Ошибка сети'));
});