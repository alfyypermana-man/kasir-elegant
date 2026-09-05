<?php
require_once __DIR__ . '/../config/database.php';

$page_title = 'Customer';
$keyword = trim($_GET['q'] ?? '');

if ($keyword !== '') {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM customer 
        WHERE name ILIKE :name_keyword 
           OR phone ILIKE :phone_keyword 
           OR email ILIKE :email_keyword 
        ORDER BY id DESC
    ");

    $stmt->execute([
        'name_keyword' => '%' . $keyword . '%',
        'phone_keyword' => '%' . $keyword . '%',
        'email_keyword' => '%' . $keyword . '%',
    ]);

    $customers = $stmt->fetchAll();
} else {
    $customers = $pdo->query("
        SELECT * 
        FROM customer 
        ORDER BY id DESC
    ")->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card card-pad">
    <div class="toolbar">
        <form class="search-form" method="get">
            <input 
                type="search" 
                name="q" 
                value="<?= e($keyword) ?>" 
                placeholder="Cari nama, phone, atau email..."
            >

            <button class="btn btn-ghost" type="submit">Cari</button>
        </form>

        <a href="<?= url('customers/create.php') ?>" class="btn">+ Tambah Customer</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Alamat</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php if (!$customers): ?>
                    <tr>
                        <td colspan="6" class="empty-state">Data customer tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= e($customer['id']) ?></td>
                        <td><?= e($customer['name']) ?></td>
                        <td><?= e($customer['phone'] ?: '-') ?></td>
                        <td><?= e($customer['email'] ?: '-') ?></td>
                        <td><?= e(mb_strimwidth($customer['address'] ?: '-', 0, 44, '...')) ?></td>

                        <td>
                            <div class="table-actions">
                                <a 
                                    class="btn btn-small btn-ghost" 
                                    href="<?= url('customers/edit.php?id=' . $customer['id']) ?>"
                                >
                                    Edit
                                </a>

                                <a 
                                    class="btn btn-small btn-danger" 
                                    data-confirm="Hapus customer ini?" 
                                    href="<?= url('customers/delete.php?id=' . $customer['id']) ?>"
                                >
                                    Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>