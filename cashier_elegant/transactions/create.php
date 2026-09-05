<?php
require_once __DIR__ . '/../config/database.php';

$page_title = 'Transaksi Baru';
$customers = $pdo->query('SELECT * FROM customer ORDER BY name ASC')->fetchAll();
$items = $pdo->query('SELECT * FROM item WHERE stock > 0 ORDER BY name ASC')->fetchAll();

if (!$customers) {
    set_flash('warning', 'Tambahkan customer terlebih dahulu.');
    redirect('customers/create.php');
}

if (!$items) {
    set_flash('warning', 'Tidak ada item dengan stok tersedia.');
    redirect('items/create.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card card-pad">
    <form method="post" action="<?= url('transactions/store.php') ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Tanggal Transaksi</label>
                <input type="datetime-local" name="date" value="<?= e(date('Y-m-d\TH:i')) ?>" required>
            </div>
            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" required>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= e($customer['id']) ?>"><?= e($customer['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mt-18">
            <div class="toolbar">
                <div>
                    <h2>Item Transaksi</h2>
                    <p class="muted">Pilih item, masukkan qty, lalu sistem menghitung subtotal otomatis.</p>
                </div>
                <button class="btn btn-ghost" data-add-line>+ Tambah Baris</button>
            </div>

            <div data-transaction-lines>
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
            </div>
        </div>

        <div class="grid grid-2 mt-18">
            <div class="form-group">
                <label>Diskon</label>
                <input type="number" name="discount" data-discount min="0" step="100" value="0">
            </div>
            <div class="summary-box">
                <div class="summary-row"><span>Subtotal</span><strong data-subtotal>Rp 0</strong></div>
                <div class="summary-row"><span>Grand Total</span><strong data-grand-total>Rp 0</strong></div>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= url('transactions/index.php') ?>" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn">Simpan Transaksi</button>
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