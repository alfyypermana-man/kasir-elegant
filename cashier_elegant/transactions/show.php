<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT t.*, c.name AS customer_name
    FROM \"transaction\" t
    JOIN customer c ON c.id = t.customer_id
    WHERE t.id = :id
");

$stmt->execute(['id' => $id]);

$transaction = $stmt->fetch();

if (!$transaction) {
    die('Transaksi tidak ditemukan');
}

$detailStmt = $pdo->prepare("
    SELECT td.*, i.name
    FROM transaction_detail td
    JOIN item i ON i.id = td.item_id
    WHERE td.transaction_id = :transaction_id
");

$detailStmt->execute([
    'transaction_id' => $id
]);

$details = $detailStmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Print Struk</title>

<style>
body{
    font-family: monospace;
    background:#f5f5f5;
    padding:20px;
}

.receipt{
    width:320px;
    background:white;
    margin:auto;
    padding:20px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.center{
    text-align:center;
}

.line{
    border-top:1px dashed #000;
    margin:10px 0;
}

.item{
    margin-bottom:10px;
}

.row{
    display:flex;
    justify-content:space-between;
}

.total{
    font-weight:bold;
    font-size:16px;
}

.small{
    font-size:12px;
}

@media print{
    body{
        background:white;
        padding:0;
    }

    .receipt{
        box-shadow:none;
        width:100%;
    }

    .no-print{
        display:none;
    }
}
</style>
</head>

<body>

<div class="receipt">

    <div class="center">
        <h2>SUPERHOLIC</h2>
        <div class="small">
            Cashier System
        </div>
    </div>

    <div class="line"></div>

    <div class="small">
        ID : #<?= $transaction['id'] ?><br>
        Tanggal : <?= date('d/m/Y H:i', strtotime($transaction['date'])) ?><br>
        Customer : <?= htmlspecialchars($transaction['customer_name']) ?>
    </div>

    <div class="line"></div>

    <?php foreach($details as $detail): ?>

        <div class="item">
            <strong><?= htmlspecialchars($detail['name']) ?></strong>

            <div class="row small">
                <span>
                    <?= $detail['item_count'] ?> x
                    <?= rupiah($detail['price'] / $detail['item_count']) ?>
                </span>

                <span>
                    <?= rupiah($detail['price']) ?>
                </span>
            </div>
        </div>

    <?php endforeach; ?>

    <div class="line"></div>

    <div class="row small">
        <span>Subtotal</span>
        <span><?= rupiah($transaction['price']) ?></span>
    </div>

    <div class="row small">
        <span>Diskon</span>
        <span><?= rupiah($transaction['discount']) ?></span>
    </div>

    <div class="line"></div>

    <div class="row total">
        <span>TOTAL</span>
        <span><?= rupiah($transaction['total_price']) ?></span>
    </div>

    <div class="line"></div>

    <div class="center small">
        Terima kasih<br>
        Sudah berbelanja
    </div>

</div>

<div class="center no-print" style="margin-top:20px;">
    <button onclick="window.print()">
        Print Struk
    </button>
</div>

<script>
window.onload = function() {
    window.print();
}
</script>

</body>
</html>