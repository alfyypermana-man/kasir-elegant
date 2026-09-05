<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare('DELETE FROM item WHERE id = :id');
    $stmt->execute(['id' => $id]);
    set_flash('success', 'Item berhasil dihapus.');
} catch (PDOException $e) {
    set_flash('danger', 'Item tidak bisa dihapus karena sudah dipakai di transaksi.');
}

redirect('items/index.php');