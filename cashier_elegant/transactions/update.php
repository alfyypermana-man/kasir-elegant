<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM "transaction" WHERE id = :id');
$stmt->execute(['id' => $id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    set_flash('danger', 'Transaksi tidak ditemukan.');
    redirect('transactions/index.php');
}

$customers = $pdo->query('SELECT * FROM customer ORDER BY name ASC')->fetchAll();

$detailStmt = $pdo->prepare('SELECT td.*, i.stock AS current_stock FROM transaction_detail td JOIN item i ON i.id = td.item_id WHERE td.transaction_id = :transaction_id ORDER BY td.id ASC');
$detailStmt->execute(['transaction_id' => $id]);
$details = $detailStmt->fetchAll();

$items = $pdo->query('SELECT * FROM item ORDER BY name ASC')->fetchAll();
$oldQtyByItem = [];

foreach ($details as $detail) {
    $oldQtyByItem[(int)$detail['item_id']] = ($oldQtyByItem[(int)$detail['item_id']] ?? 0) + (int)$detail['item_count'];
}

foreach ($items as &$item) {
    $item['stock'] = (int)$item['stock'] + ($oldQtyByItem[(int)$item['id']] ?? 0);
}
unset($item);

$page_title = 'Edit Transaksi #' . $transaction['id'];
require_once __DIR__ . '/../includes/header.php';
?>

<section class="card card-pad">
    <form method="post" action="<?= url('transactions/update.php') ?>">
        <input type="hidden" name="id" value="<?= e($transaction['id']) ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Tanggal Transaksi</label>
                <input type="datetime-local" name="date" value="<?= e(date('Y-m-d\TH:i', strtotime($transaction['date']))) ?>" required>
            </div>
            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" required>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= e($customer['id']) ?>" <?= (int)$customer['id'] === (int)$transaction['customer_id'] ? 'selected' : '' ?>>
                            <?= e($customer['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mt-18">
            <div class="toolbar">
                <div>
                    <h2>Item Transaksi</h2>
                    <p class="muted">Ubah item atau qty. Sistem akan menghitung ulang stok dengan aman.</p>
                </div>
                <button class="btn btn-ghost" data-add-line>+ Tambah Baris</button>
            </div>

            <div data-transaction-lines>
                <?php foreach ($details as $detail): ?>
                    <?php
                        $selectedItemId = (int)$detail['item_id'];
                        $selectedAvailableStock = 0;

                        foreach ($items as $candidateItem) {
                            if ((int)$candidateItem['id'] === $selectedItemId) {
                                $selectedAvailableStock = (int)$candidateItem['stock'];
                                break;
                            }
                        }
                    ?>
                    <div class="transaction-item">
                        <div class="form-group">
                            <label>Item</label>
                            <select name="item_id[]" data-item-select required>
                                <option value="" data-price="0" data-stock="0">Pilih item</option>
                                <?php foreach ($items as $item): ?>
                                    <option value="<?= e($item['id']) ?>" data-price="<?= e($item['price']) ?>" data-stock="<?= e($item['stock']) ?>" <?= (int)$selectedItemId === (int)$item['id'] ? 'selected' : '' ?>>
                                        <?= e($item['code']) ?> — <?= e($item['name']) ?> / <?= rupiah($item['price']) ?> / stok <?= e($item['stock']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="muted">Harga: <span data-price-preview><?= rupiah($detail['price'] / max((int)$detail['item_count'], 1)) ?></span> · Stok: <span data-stock-preview><?= e($selectedAvailableStock) ?></span></small>
                        </div>

                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" name="item_count[]" data-qty min="1" step="1" value="<?= e($detail['item_count']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Subtotal Baris</label>
                            <strong data-line-total><?= rupiah($detail['price']) ?></strong>
                        </div>

                        <button class="btn btn-danger btn-small" data-remove-line>Hapus</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid grid-2 mt-18">
            <div class="form-group">
                <label>Diskon</label>
                <input type="number" name="discount" data-discount min="0" step="100" value="<?= e($transaction['discount']) ?>">
            </div>
            <div class="summary-box">
                <div class="summary-row"><span>Subtotal</span><strong data-subtotal><?= rupiah($transaction['price']) ?></strong></div>
                <div class="summary-row"><span>Grand Total</span><strong data-grand-total><?= rupiah($transaction['total_price']) ?></strong></div>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= url('transactions/index.php') ?>" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn">Update Transaksi</button>
        </div>
    </form>
</section>

<template data-line-template>
    <div class="transaction-item">
        <div class="form-group">
            <label>Item</label>
            <select name="item_id[]" data-item-select required>
                <option value="" data-price="0" data-stock="0">Pilih item</option>
                <?php foreach ($items as $item): ?>
                    <option value="<?= e($item['id']) ?>" data-price="<?= e($item['price']) ?>" data-stock="<?= e($item['stock']) ?>">
                        <?= e($item['code']) ?> — <?= e($item['name']) ?> / <?= rupiah($item['price']) ?> / stok <?= e($item['stock']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="muted">Harga: <span data-price-preview>Rp 0</span> · Stok: <span data-stock-preview>0</span></small>
        </div>

        <div class="form-group">
            <label>Qty</label>
            <input type="number" name="item_count[]" data-qty min="1" step="1" value="1" required>
        </div>

        <div class="form-group">
            <label>Subtotal Baris</label>
            <strong data-line-total>Rp 0</strong>
        </div>

        <button class="btn btn-danger btn-small" data-remove-line>Hapus</button>
    </div>
</template>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>