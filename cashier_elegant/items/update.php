<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_POST['id'] ?? 0);
$code = strtoupper(trim($_POST['code'] ?? ''));
$name = trim($_POST['name'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$expiredDate = $_POST['expired_date'] !== '' ? $_POST['expired_date'] : null;

if ($id <= 0 || $code === '' || $name === '' || $price < 0 || $stock < 0) {
    set_flash('danger', 'Input item tidak valid.');
    redirect('items/index.php');
}

try {
    $stmt = $pdo->prepare('UPDATE item SET code = :code, name = :name, price = :price, stock = :stock, expired_date = :expired_date WHERE id = :id');
    $stmt->execute([
        'id' => $id,
        'code' => $code,
        'name' => $name,
        'price' => $price,
        'stock' => $stock,
        'expired_date' => $expiredDate,
    ]);
    set_flash('success', 'Item berhasil diupdate.');
} catch (PDOException $e) {
    set_flash('danger', 'Gagal mengupdate item. Kode item mungkin sudah dipakai.');
}

redirect('items/index.php');