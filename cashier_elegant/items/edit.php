<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM item WHERE id = :id');
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    set_flash('danger', 'Item tidak ditemukan.');
    redirect('items/index.php');
}

$page_title = 'Edit Item';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="card card-pad">
    <form method="post" action="<?= url('items/update.php') ?>">
        <input type="hidden" name="id" value="<?= e($item['id']) ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Kode Item</label>
                <input type="text" name="code" value="<?= e($item['code']) ?>" required>
            </div>
            <div class="form-group">
                <label>Nama Item</label>
                <input type="text" name="name" value="<?= e($item['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="price" min="0" step="100" value="<?= e($item['price']) ?>" required>
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" min="0" step="1" value="<?= e($item['stock']) ?>" required>
            </div>
            <div class="form-group">
                <label>Tanggal Expired</label>
                <input type="date" name="expired_date" value="<?= e($item['expired_date']) ?>">
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= url('items/index.php') ?>" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn">Update</button>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>