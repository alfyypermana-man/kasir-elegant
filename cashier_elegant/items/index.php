<?php
require_once __DIR__ . '/../config/database.php';

$page_title = 'Item';
$keyword = trim($_GET['q'] ?? '');

if ($keyword !== '') {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM item 
        WHERE code ILIKE :code_keyword 
           OR name ILIKE :name_keyword 
        ORDER BY id DESC
    ");

    $stmt->execute([
        'code_keyword' => '%' . $keyword . '%',
        'name_keyword' => '%' . $keyword . '%',
    ]);

    $items = $stmt->fetchAll();
} else {
    $items = $pdo->query("
        SELECT * 
        FROM item 
        ORDER BY id DESC
    ")->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card card-pad">
    <div class="toolbar">
        <form class="search-form" method="get">
            <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Cari kode atau nama item...">
            <button class="btn btn-ghost" type="submit">Cari</button>
        </form>

        <a href="<?= url('items/create.php') ?>" class="btn">+ Tambah Item</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Expired</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php if (!$items): ?>
                    <tr>
                        <td colspan="8" class="empty-state">Data item tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($items as $item): ?>
                    <?php
                        $expired = !empty($item['expired_date']) ? strtotime($item['expired_date']) : null;
                        $now = strtotime(date('Y-m-d'));
                    ?>

                    <tr>
                        <td><?= e($item['id']) ?></td>
                        <td><?= e($item['code']) ?></td>
                        <td><?= e($item['name']) ?></td>
                        <td><?= rupiah($item['price']) ?></td>

                        <td>
                            <span class="badge <?= (int)$item['stock'] <= 10 ? 'badge-rose' : 'badge-green' ?>">
                                <?= e($item['stock']) ?>
                            </span>
                        </td>

                        <td>
                            <?= $item['expired_date'] ? e(date('d M Y', $expired)) : '-' ?>
                        </td>

                        <td>
                            <?php if ($expired && $expired < $now): ?>
                                <span class="badge badge-rose">Expired</span>
                            <?php elseif ($expired && $expired <= strtotime('+45 days')): ?>
                                <span class="badge badge-gold">Segera expired</span>
                            <?php else: ?>
                                <span class="badge badge-green">Aman</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="table-actions">
                                <a class="btn btn-small btn-ghost" href="<?= url('items/edit.php?id=' . $item['id']) ?>">
                                    Edit
                                </a>

                                <a 
                                    class="btn btn-small btn-danger" 
                                    data-confirm="Hapus item ini?" 
                                    href="<?= url('items/delete.php?id=' . $item['id']) ?>"
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