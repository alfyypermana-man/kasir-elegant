<?php
require_once __DIR__ . '/../config/database.php';
$page_title = 'Tambah Item';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="card card-pad">
    <form method="post" action="<?= url('items/store.php') ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Kode Item</label>
                <input type="text" name="code" placeholder="Contoh: ITM-006" required>
            </div>
            <div class="form-group">
                <label>Nama Item</label>
                <input type="text" name="name" placeholder="Nama item" required>
            </div>
            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="price" min="0" step="100" placeholder="0" required>
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" min="0" step="1" placeholder="0" required>
            </div>
            <div class="form-group">
                <label>Tanggal Expired</label>
                <input type="date" name="expired_date">
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= url('items/index.php') ?>" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn">Simpan</button>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>