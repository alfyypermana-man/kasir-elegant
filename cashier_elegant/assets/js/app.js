document.addEventListener('click', function (event) {
    const deleteButton = event.target.closest('[data-confirm]');
    if (deleteButton) {
        const message = deleteButton.getAttribute('data-confirm') || 'Yakin ingin menghapus data ini?';
        if (!confirm(message)) {
            event.preventDefault();
        }
    }

    const addLineButton = event.target.closest('[data-add-line]');
    if (addLineButton) {
        event.preventDefault();
        addTransactionLine();
    }

    const removeLineButton = event.target.closest('[data-remove-line]');
    if (removeLineButton) {
        event.preventDefault();
        const line = removeLineButton.closest('.transaction-item');
        if (document.querySelectorAll('.transaction-item').length > 1) {
            line.remove();
            updateTransactionTotals();
        }
    }
});

document.addEventListener('input', function (event) {
    if (event.target.matches('[data-item-select], [data-qty], [data-discount]')) {
        updateTransactionTotals();
    }
});

document.addEventListener('change', function (event) {
    if (event.target.matches('[data-item-select]')) {
        const line = event.target.closest('.transaction-item');
        const selected = event.target.selectedOptions[0];
        const price = selected ? Number(selected.dataset.price || 0) : 0;
        const stock = selected ? Number(selected.dataset.stock || 0) : 0;
        line.querySelector('[data-price-preview]').textContent = rupiah(price);
        line.querySelector('[data-stock-preview]').textContent = stock;
        updateTransactionTotals();
    }
});

function addTransactionLine() {
    const wrapper = document.querySelector('[data-transaction-lines]');
    const template = document.querySelector('[data-line-template]');
    if (!wrapper || !template) return;

    const clone = template.content.firstElementChild.cloneNode(true);
    wrapper.appendChild(clone);
    updateTransactionTotals();
}

function updateTransactionTotals() {
    let subtotal = 0;

    document.querySelectorAll('.transaction-item').forEach(function (line) {
        const select = line.querySelector('[data-item-select]');
        const qtyInput = line.querySelector('[data-qty]');
        const selected = select ? select.selectedOptions[0] : null;
        const price = selected ? Number(selected.dataset.price || 0) : 0;
        const qty = qtyInput ? Number(qtyInput.value || 0) : 0;
        const lineTotal = price * qty;

        subtotal += lineTotal;

        const lineTotalEl = line.querySelector('[data-line-total]');
        if (lineTotalEl) {
            lineTotalEl.textContent = rupiah(lineTotal);
        }
    });

    const discountInput = document.querySelector('[data-discount]');
    const discount = discountInput ? Number(discountInput.value || 0) : 0;
    const total = Math.max(subtotal - discount, 0);

    const subtotalEl = document.querySelector('[data-subtotal]');
    const totalEl = document.querySelector('[data-grand-total]');

    if (subtotalEl) subtotalEl.textContent = rupiah(subtotal);
    if (totalEl) totalEl.textContent = rupiah(total);
}

function rupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number || 0);
}

updateTransactionTotals();