<?php
require_once __DIR__ . '/../config/database.php';

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');

if ($name === '') {
    set_flash('danger', 'Nama customer wajib diisi.');
    redirect('customers/create.php');
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO customer 
        (name, phone, email, address) 
        VALUES 
        (:name, :phone, :email, :address)
    ");

    $stmt->execute([
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'address' => $address,
    ]);

    set_flash('success', 'Customer berhasil ditambahkan.');
    redirect('customers/index.php');
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menyimpan customer: ' . $e->getMessage());
    redirect('customers/create.php');
}