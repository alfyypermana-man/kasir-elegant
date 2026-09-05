<?php
require_once __DIR__ . '/../config/database.php';
$page_title = 'Tambah Customer';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="card card-pad">
    <form method="post" action="<?= url('customers/store.php') ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Nama Customer</label>
                <input type="text" name="name" placeholder="Nama lengkap" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" placeholder="08xxxxxxxxxx">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="nama@email.com">
            </div>
            <div class="form-group full">
                <label>Alamat</label>
                <textarea name="address" placeholder="Alamat customer"></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= url('customers/index.php') ?>" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn">Simpan</button>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>