<?php
require_once __DIR__ . '/../config/database.php';

$code = strtoupper(trim($_POST['code'] ?? ''));
$name = trim($_POST['name'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$expiredDate = !empty($_POST['expired_date']) ? $_POST['expired_date'] : null;

if ($code === '' || $name === '' || $price < 0 || $stock < 0) {
    set_flash('danger', 'Input item tidak valid.');
    redirect('items/create.php');
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO item 
        (code, name, price, stock, expired_date) 
        VALUES 
        (:code, :name, :price, :stock, :expired_date)
    ");

    $stmt->execute([
        'code' => $code,
        'name' => $name,
        'price' => $price,
        'stock' => $stock,
        'expired_date' => $expiredDate,
    ]);

    set_flash('success', 'Item berhasil ditambahkan.');
    redirect('items/index.php');
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menyimpan item: ' . $e->getMessage());
    redirect('items/create.php');
}