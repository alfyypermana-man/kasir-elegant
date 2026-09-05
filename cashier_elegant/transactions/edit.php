<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_POST['id'] ?? 0);
$dateInput = $_POST['date'] ?? '';
$date = $dateInput !== '' ? date('Y-m-d H:i:s', strtotime($dateInput)) : date('Y-m-d H:i:s');
$customerId = (int)($_POST['customer_id'] ?? 0);
$discount = max(0, (float)($_POST['discount'] ?? 0));
$itemIds = $_POST['item_id'] ?? [];
$itemCounts = $_POST['item_count'] ?? [];

if ($id <= 0 || $customerId <= 0) {
    set_flash('danger', 'Data transaksi tidak valid.');
    redirect('transactions/index.php');
}

try {
    $pdo->beginTransaction();

    $transactionStmt = $pdo->prepare('SELECT * FROM "transaction" WHERE id = :id FOR UPDATE');
    $transactionStmt->execute(['id' => $id]);
    $oldTransaction = $transactionStmt->fetch();
    if (!$oldTransaction) {
        throw new Exception('Transaksi tidak ditemukan.');
    }

    $customerStmt = $pdo->prepare('SELECT id FROM customer WHERE id = :id');
    $customerStmt->execute(['id' => $customerId]);
    if (!$customerStmt->fetch()) {
        throw new Exception('Customer tidak ditemukan.');
    }

    // Kembalikan stok transaksi lama terlebih dahulu.
    $oldDetailsStmt = $pdo->prepare('SELECT item_id, item_count FROM transaction_detail WHERE transaction_id = :id');
    $oldDetailsStmt->execute(['id' => $id]);
    $restoreStmt = $pdo->prepare('UPDATE item SET stock = stock + :qty WHERE id = :item_id');
    foreach ($oldDetailsStmt->fetchAll() as $oldDetail) {
        $restoreStmt->execute([
            'qty' => (int)$oldDetail['item_count'],
            'item_id' => (int)$oldDetail['item_id'],
        ]);
    }

    $cart = [];
    foreach ($itemIds as $index => $itemId) {
        $itemId = (int)$itemId;
        $qty = (int)($itemCounts[$index] ?? 0);
        if ($itemId > 0 && $qty > 0) {
            $cart[$itemId] = ($cart[$itemId] ?? 0) + $qty;
        }
    }

    if (!$cart) {
        throw new Exception('Minimal pilih satu item transaksi.');
    }

    $details = [];
    $subtotal = 0;

    foreach ($cart as $itemId => $qty) {
        $itemStmt = $pdo->prepare('SELECT * FROM item WHERE id = :id FOR UPDATE');
        $itemStmt->execute(['id' => $itemId]);
        $item = $itemStmt->fetch();

        if (!$item) {
            throw new Exception('Item tidak ditemukan.');
        }

        if ((int)$item['stock'] < $qty) {
            throw new Exception('Stok item "' . $item['name'] . '" tidak cukup. Stok tersedia: ' . $item['stock']);
        }

        $lineTotal = (float)$item['price'] * $qty;
        $subtotal += $lineTotal;
        $details[] = [
            'item_id' => $itemId,
            'qty' => $qty,
            'price' => $lineTotal,
        ];
    }

    $discount = min($discount, $subtotal);
    $totalPrice = $subtotal - $discount;

    $updateStmt = $pdo->prepare('UPDATE "transaction" SET date = :date, customer_id = :customer_id, price = :price, discount = :discount, total_price = :total_price WHERE id = :id');
    $updateStmt->execute([
        'id' => $id,
        'date' => $date,
        'customer_id' => $customerId,
        'price' => $subtotal,
        'discount' => $discount,
        'total_price' => $totalPrice,
    ]);

    $pdo->prepare('DELETE FROM transaction_detail WHERE transaction_id = :id')
        ->execute(['id' => $id]);

    $detailStmt = $pdo->prepare('INSERT INTO transaction_detail (transaction_id, item_id, item_count, price) VALUES (:transaction_id, :item_id, :item_count, :price)');
    $stockStmt = $pdo->prepare('UPDATE item SET stock = stock - :qty WHERE id = :id');

    foreach ($details as $detail) {
        $detailStmt->execute([
            'transaction_id' => $id,
            'item_id' => $detail['item_id'],
            'item_count' => $detail['qty'],
            'price' => $detail['price'],
        ]);
        $stockStmt->execute([
            'qty' => $detail['qty'],
            'id' => $detail['item_id'],
        ]);
    }

    $pdo->commit();
    set_flash('success', 'Transaksi berhasil diupdate.');
    redirect('transactions/show.php?id=' . $id);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('danger', 'Gagal mengupdate transaksi: ' . $e->getMessage());
    redirect('transactions/update.php?id=' . $id);
}
