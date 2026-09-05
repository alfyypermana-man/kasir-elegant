# Nocturne Cashier — PHP + Supabase

Project ini tetap **PHP murni**. Yang diubah adalah koneksi database dari MySQL lokal menjadi PostgreSQL Supabase dan query yang perlu disesuaikan.

## Setup Supabase
1. Buka Supabase → SQL Editor.
2. Jalankan `database/supabase.sql`.
3. Ambil connection string PostgreSQL dari Supabase → Project Settings → Database.
4. Simpan password database hanya sebagai environment variable.

## Environment
Bisa memakai:
- `DATABASE_URL` = connection string PostgreSQL Supabase lengkap, atau
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_SSLMODE`.

PHP membutuhkan extension `pdo_pgsql`.

## Catatan
- Supabase **anon key tidak digunakan** oleh project ini karena koneksi dilakukan server-side dengan PDO PostgreSQL.
- Jangan commit `.env` atau connection string yang berisi password ke GitHub.
- `BASE_URL` sudah dibuat kosong agar aplikasi tidak menganggap project berada di `/cashier_elegant`.
