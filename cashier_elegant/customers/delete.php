<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare('DELETE FROM customer WHERE id = :id');
    $stmt->execute(['id' => $id]);
    set_flash('success', 'Customer berhasil dihapus.');
} catch (PDOException $e) {
    set_flash('danger', 'Customer tidak bisa dihapus karena sudah dipakai di transaksi.');
}

redirect('customers/index.php');