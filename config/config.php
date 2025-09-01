<?php

/**
 * ===================================================================
 * FILE KONFIGURASI UTAMA APLIKASI
 * ===================================================================
 * File ini berisi semua pengaturan inti yang dibutuhkan oleh aplikasi,
 * mulai dari koneksi database, URL dasar, hingga pengaturan session
 * yang aman dan kuat untuk lingkungan produksi (HTTPS).
 */

// 1. PENGATURAN LINGKUNGAN (ENVIRONMENT)
// -------------------------------------------------------------------
// Atur menjadi 'development' untuk menampilkan semua error saat ngoding.
// Ganti menjadi 'production' saat aplikasi sudah live di server.
define('ENVIRONMENT', 'development');

// 2. PENGATURAN LAPORAN ERROR (ERROR REPORTING)
// -------------------------------------------------------------------
if (ENVIRONMENT == 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
    // Di lingkungan produksi, disarankan untuk mencatat error ke dalam file log.
    // ini_set('log_errors', 1);
    // ini_set('error_log', '/path/to/your/php-error.log');
}

// 3. PENGATURAN ZONA WAKTU (TIMEZONE)
// -------------------------------------------------------------------
// Mengatur zona waktu default untuk semua fungsi tanggal dan waktu di PHP.
date_default_timezone_set('Asia/Jakarta'); // Sesuai Waktu Indonesia Barat (WIB)

// 4. KONFIGURASI URL DASAR APLIKASI (BASE URL)
// -------------------------------------------------------------------
// URL utama aplikasi Anda. WAJIB menggunakan https:// untuk produksi.
// Pastikan tidak ada garis miring (/) di akhir.
define('BASE_URL', 'https://survey-parking.radevankaproject');

// 5. KONFIGURASI DATABASE
// -------------------------------------------------------------------
// Ganti dengan detail koneksi database Anda.
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ganti dengan username database Anda
define('DB_PASS', '');     // Ganti dengan password database Anda
define('DB_NAME', 'survey_parking');

// 6. KONFIGURASI SESSION YANG AMAN DAN KUAT (POWERFULL & SECURE)
// -------------------------------------------------------------------
// Blok ini memastikan session dan cookie ditangani dengan benar dan aman,
// terutama saat berjalan di koneksi HTTPS.

// Langkah 6.1: Deteksi koneksi aman di belakang Reverse Proxy (misal: Cloudflare, Nginx)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Langkah 6.2: Atur parameter cookie session sebelum session dimulai
// Ini hanya akan diterapkan jika koneksi menggunakan HTTPS.
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    session_set_cookie_params([
        'lifetime' => 0, // Cookie berlaku sampai browser ditutup
        'path'     => '/',
        'domain'   => 'survey-parking.radevankaproject', // Ganti dengan domain Anda
        'secure'   => true,                              // WAJIB: Hanya kirim cookie melalui HTTPS
        'httponly' => true,                              // WAJIB: Mencegah akses via JavaScript (XSS)
        'samesite' => 'Lax',                             // WAJIB: Perlindungan modern terhadap serangan CSRF
    ]);
}

// Langkah 6.3: Mulai session PHP
// Memeriksa apakah session belum dimulai sebelum menjalankannya.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
