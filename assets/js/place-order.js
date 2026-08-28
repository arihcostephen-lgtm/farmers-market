document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('placeOrderForm');
    const quantityInput = document.getElementById('quantity');
    const price = Number(form?.dataset.price || 0);
    const taxRate = Number(form?.dataset.taxRate || 0);
    const maxQuantity = Number(quantityInput?.max || 0);
    const subtotal = document.getElementById('orderSubtotal');
    const tax = document.getElementById('orderTax');
    const total = document.getElementById('orderTotal');
    const formatMoney = value => value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function updateTotals() {
        if (!quantityInput || !subtotal || !tax || !total) return;
        const quantity = Math.max(1, Math.min(Number(quantityInput.value) || 1, maxQuantity || Number.MAX_SAFE_INTEGER));
        quantityInput.value = quantity;
        const subtotalValue = quantity * price;
        const taxValue = subtotalValue * taxRate / 100;
        subtotal.textContent = formatMoney(subtotalValue);
        tax.textContent = formatMoney(taxValue);
        total.textContent = formatMoney(subtotalValue + taxValue);
    }

    document.querySelector('[data-quantity-change="decrease"]')?.addEventListener('click', function () {
        quantityInput.value = Math.max(1, Number(quantityInput.value || 1) - 1);
        updateTotals();
    });
    document.querySelector('[data-quantity-change="increase"]')?.addEventListener('click', function () {
        quantityInput.value = Math.min(maxQuantity || Number.MAX_SAFE_INTEGER, Number(quantityInput.value || 1) + 1);
        updateTotals();
    });
    quantityInput?.addEventListener('input', updateTotals);
    form?.addEventListener('submit', function (event) {
        if (!quantityInput || !document.getElementById('delivery_location')?.value.trim()) {
            event.preventDefault();
            document.getElementById('delivery_location')?.focus();
        }
    });
    updateTotals();

    document.querySelectorAll('.checkout-reveal').forEach(function (element, index) {
        element.style.animationDelay = Math.min(index * 80, 240) + 'ms';
    });
});
