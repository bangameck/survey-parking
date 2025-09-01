<?php

    /**
     * ===================================================================
     * SCRIPT DIAGNOSA MASALAH SESSION & CSRF
     * ===================================================================
     * Tujuan: Untuk mengisolasi dan memverifikasi apakah session PHP
     * dapat bertahan (persistent) antara request GET dan POST di server ini.
     * Ini mengabaikan semua kerumitan dari struktur MVC.
     */

    // Menampilkan semua error untuk kepentingan debug
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // --- KONFIGURASI SESSION AMAN (diambil dari config.php kita) ---
    // Pastikan domain di bawah ini SUDAH BENAR
    $sessionDomain = 'survey-parking.radevankaproject';

    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
    }

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => $sessionDomain,
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    // Mulai session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    // --- AKHIR KONFIGURASI SESSION ---

    // Fungsi untuk menghasilkan token CSRF
    function generateCsrfToken()
    {
        return bin2hex(random_bytes(32));
    }

    // Logika utama script
    $sessionId = session_id();

    // Cek jika ini adalah request POST (form disubmit)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h1>Hasil Setelah Form Disubmit (POST Request)</h1>";

        $sessionTokenAfterPost = $_SESSION['csrf_token'] ?? 'TIDAK DITEMUKAN DI SESSION';
        $postToken             = $_POST['csrf_token'] ?? 'TIDAK DITEMUKAN DI POST';

        echo "<h3>ID Session Saat Ini: <pre>{$sessionId}</pre></h3>";
        echo "<h3>Token di SESSION: <pre>{$sessionTokenAfterPost}</pre></h3>";
        echo "<h3>Token dari FORM (POST): <pre>{$postToken}</pre></h3>";

        echo "<hr>";

        if ($sessionTokenAfterPost === $postToken && $sessionTokenAfterPost !== 'TIDAK DITEMUKAN DI SESSION') {
            echo '<h2 style="color: green;">HASIL: SUKSES! Session bertahan dan token cocok!</h2>';
        } else {
            echo '<h2 style="color: red;">HASIL: GAGAL! Session di-reset atau token tidak cocok!</h2>';
            echo "<p>Jika ID Session di atas berbeda dengan ID Session saat halaman pertama kali dimuat, artinya session Anda benar-benar di-reset pada setiap request. Ini kemungkinan besar masalah izin (permission) pada folder penyimpanan session di server.</p>";
        }
        echo '<br><a href="session_test.php">Coba Lagi</a>';
        exit();
    }

    // Jika ini adalah request GET (halaman pertama kali dibuka)
    // Buat token CSRF baru HANYA jika belum ada di session
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateCsrfToken();
    }

    $sessionToken = $_SESSION['csrf_token'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Diagnosa Session Test</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        pre { background-color: #f4f4f4; padding: 10px; border: 1px solid #ddd; word-wrap: break-word; }
        input[type=submit] { font-size: 1.2em; padding: 10px 20px; }
    </style>
</head>
<body>
    <h1>Halaman Awal (GET Request)</h1>
    <p>Script ini akan memeriksa apakah session Anda tetap sama setelah Anda menekan tombol submit.</p>

    <h3>ID Session Saat Ini: <pre><?php echo htmlspecialchars($sessionId); ?></pre></h3>
    <h3>Token CSRF di SESSION: <pre><?php echo htmlspecialchars($sessionToken); ?></pre></h3>

    <hr>

    <form action="session_test.php" method="POST">
        <p>Token di bawah ini akan dikirim melalui form. Nilainya harus cocok dengan token di session di atas setelah disubmit.</p>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($sessionToken); ?>">
        <input type="submit" value="TEST SUBMIT">
    </form>
</body>
</html>