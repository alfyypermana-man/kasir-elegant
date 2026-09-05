<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'ID transaksi tidak valid.');
    redirect('transactions/index.php');
}

try {
    $pdo->beginTransaction();

    $check = $pdo->prepare('SELECT id FROM "transaction" WHERE id = :id FOR UPDATE');
    $check->execute(['id' => $id]);
    if (!$check->fetch()) {
        throw new Exception('Transaksi tidak ditemukan.');
    }

    $detailsStmt = $pdo->prepare('SELECT item_id, item_count FROM transaction_detail WHERE transaction_id = :id');
    $detailsStmt->execute(['id' => $id]);

    $restoreStmt = $pdo->prepare('UPDATE item SET stock = stock + :qty WHERE id = :item_id');
    foreach ($detailsStmt->fetchAll() as $detail) {
        $restoreStmt->execute([
            'qty' => (int)$detail['item_count'],
            'item_id' => (int)$detail['item_id'],
        ]);
    }

    $pdo->prepare('DELETE FROM transaction_detail WHERE transaction_id = :id')->execute(['id' => $id]);
    $pdo->prepare('DELETE FROM "transaction" WHERE id = :id')->execute(['id' => $id]);

    $pdo->commit();
    set_flash('success', 'Transaksi berhasil dihapus dan stok dikembalikan.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('danger', 'Gagal menghapus transaksi: ' . $e->getMessage());
}

redirect('transactions/index.php');
