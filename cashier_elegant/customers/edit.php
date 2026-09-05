<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM customer WHERE id = :id');
$stmt->execute(['id' => $id]);
$customer = $stmt->fetch();

if (!$customer) {
    set_flash('danger', 'Customer tidak ditemukan.');
    redirect('customers/index.php');
}

$page_title = 'Edit Customer';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="card card-pad">
    <form method="post" action="<?= url('customers/update.php') ?>">
        <input type="hidden" name="id" value="<?= e($customer['id']) ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Nama Customer</label>
                <input type="text" name="name" value="<?= e($customer['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= e($customer['phone']) ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($customer['email']) ?>">
            </div>
            <div class="form-group full">
                <label>Alamat</label>
                <textarea name="address"><?= e($customer['address']) ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= url('customers/index.php') ?>" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn">Update</button>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>