<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');

if ($id <= 0 || $name === '') {
    set_flash('danger', 'Input customer tidak valid.');
    redirect('customers/index.php');
}

$stmt = $pdo->prepare('UPDATE customer SET name = :name, phone = :phone, email = :email, address = :address WHERE id = :id');
$stmt->execute([
    'id' => $id,
    'name' => $name,
    'phone' => $phone,
    'email' => $email,
    'address' => $address,
]);

set_flash('success', 'Customer berhasil diupdate.');
redirect('customers/index.php');