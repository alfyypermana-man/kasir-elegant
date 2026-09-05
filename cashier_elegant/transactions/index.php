<?php
require_once __DIR__ . '/../config/database.php';

$page_title = 'Transaction';
$keyword = trim($_GET['q'] ?? '');

$sql = "
    SELECT 
        t.*, 
        c.name AS customer_name, 
        COALESCE(SUM(td.item_count), 0) AS total_items
    FROM \"transaction\" t
    JOIN customer c ON c.id = t.customer_id
    LEFT JOIN transaction_detail td ON td.transaction_id = t.id
";

$params = [];

if ($keyword !== '') {
    $sql .= "
        WHERE c.name ILIKE :customer_keyword 
           OR CAST(t.id AS TEXT) ILIKE :id_keyword
    ";

    $params = [
        'customer_keyword' => '%' . $keyword . '%',
        'id_keyword' => '%' . $keyword . '%',
    ];
}

$sql .= "
    GROUP BY t.id 
    ORDER BY t.date DESC, t.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card card-pad">
    <div class="toolbar">
        <form class="search-form" method="get">
            <input 
                type="search" 
                name="q" 
                value="<?= e($keyword) ?>" 
                placeholder="Cari ID transaksi atau customer..."
            >

            <button class="btn btn-ghost" type="submit">Cari</button>
        </form>

        <a href="<?= url('transactions/create.php') ?>" class="btn">+ Transaksi Baru</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Qty Item</th>
                    <th>Subtotal</th>
                    <th>Diskon</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php if (!$transactions): ?>
                    <tr>
                        <td colspan="8" class="empty-state">Data transaksi tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td>#<?= e($transaction['id']) ?></td>

                        <td>
                            <?= e(date('d M Y H:i', strtotime($transaction['date']))) ?>
                        </td>

                        <td>
                            <?= e($transaction['customer_name']) ?>
                        </td>

                        <td>
                            <span class="badge">
                                <?= e($transaction['total_items']) ?>
                            </span>
                        </td>

                        <td>
                            <?= rupiah($transaction['price']) ?>
                        </td>

                        <td>
                            <?= rupiah($transaction['discount']) ?>
                        </td>

                        <td>
                            <strong><?= rupiah($transaction['total_price']) ?></strong>
                        </td>

                        <td>
                            <div class="table-actions">
                                <a 
                                    class="btn btn-small btn-ghost" 
                                    href="<?= url('transactions/show.php?id=' . $transaction['id']) ?>"
                                >
                                    Detail
                                </a>

                                <a 
                                    class="btn btn-small btn-ghost" 
                                    href="<?= url('transactions/edit.php?id=' . $transaction['id']) ?>"
                                >
                                    Edit
                                </a>

                                <a 
                                    class="btn btn-small btn-danger" 
                                    data-confirm="Hapus transaksi ini? Stok item akan dikembalikan." 
                                    href="<?= url('transactions/delete.php?id=' . $transaction['id']) ?>"
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