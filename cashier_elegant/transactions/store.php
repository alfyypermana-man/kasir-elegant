<?php
require_once __DIR__ . '/../config/database.php';

$dateInput = $_POST['date'] ?? date('Y-m-d\TH:i');
$date = date('Y-m-d H:i:s', strtotime($dateInput));

$customerId = (int)($_POST['customer_id'] ?? 0);
$discount = max(0, (float)($_POST['discount'] ?? 0));

$itemIds = $_POST['item_id'] ?? [];
$itemCounts = $_POST['item_count'] ?? [];

if ($customerId <= 0) {
    set_flash('danger', 'Customer belum dipilih.');
    redirect('transactions/create.php');
}

if (empty($itemIds) || empty($itemCounts)) {
    set_flash('danger', 'Item transaksi belum dipilih.');
    redirect('transactions/create.php');
}

try {
    $pdo->beginTransaction();

    // Cek customer
    $customerStmt = $pdo->prepare("
        SELECT id 
        FROM customer 
        WHERE id = :id
    ");
    $customerStmt->execute([
        'id' => $customerId
    ]);

    if (!$customerStmt->fetch()) {
        throw new Exception('Customer tidak ditemukan.');
    }

    // Gabungkan item yang sama
    $cart = [];

    foreach ($itemIds as $index => $itemId) {
        $itemId = (int)$itemId;
        $qty = (int)($itemCounts[$index] ?? 0);

        if ($itemId <= 0 || $qty <= 0) {
            continue;
        }

        if (!isset($cart[$itemId])) {
            $cart[$itemId] = 0;
        }

        $cart[$itemId] += $qty;
    }

    if (empty($cart)) {
        throw new Exception('Minimal pilih satu item transaksi.');
    }

    $details = [];
    $subtotal = 0;

    foreach ($cart as $itemId => $qty) {
        $itemStmt = $pdo->prepare("
            SELECT *
            FROM item
            WHERE id = :id
            FOR UPDATE
        ");

        $itemStmt->execute([
            'id' => $itemId
        ]);

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

    // Simpan transaksi utama
    $transactionStmt = $pdo->prepare("
        INSERT INTO \"transaction\"
        (date, customer_id, price, discount, total_price)
        VALUES
        (:date, :customer_id, :price, :discount, :total_price)
        RETURNING id
    ");

    $transactionStmt->execute([
        'date' => $date,
        'customer_id' => $customerId,
        'price' => $subtotal,
        'discount' => $discount,
        'total_price' => $totalPrice,
    ]);

    $transactionId = (int)$transactionStmt->fetchColumn();

    // Simpan detail transaksi
    $detailStmt = $pdo->prepare("
        INSERT INTO transaction_detail
        (transaction_id, item_id, item_count, price)
        VALUES
        (:transaction_id, :item_id, :item_count, :price)
    ");

    // Kurangi stok item
    $stockStmt = $pdo->prepare("
        UPDATE item
        SET stock = stock - :qty
        WHERE id = :id
    ");

    foreach ($details as $detail) {
        $detailStmt->execute([
            'transaction_id' => $transactionId,
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

    set_flash('success', 'Transaksi berhasil disimpan.');
    redirect('transactions/show.php?id=' . $transactionId);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    set_flash('danger', 'Gagal menyimpan transaksi: ' . $e->getMessage());
    redirect('transactions/create.php');
}