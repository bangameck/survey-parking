<?php
class DatabaseHelper
{

    private $dbHost = DB_HOST;
    private $dbUser = DB_USER;
    private $dbPass = DB_PASS;
    private $dbName = DB_NAME;
    private $backupPath;

    public function __construct()
    {
        // Tentukan path target secara langsung ke root proyek, lalu tambahkan folder /backups
        $targetPath = realpath(__DIR__ . '/../..') . '/backups';

        // Cek apakah folder sudah ada, jika belum, buat folder tersebut
        if (! is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        // Tetapkan path setelah kita pastikan folder tersebut ada
        $this->backupPath = $targetPath;
    }

    public function backup()
    {
        // ====================================================================
        // DETEKSI SISTEM OPERASI (WINDOWS VS LINUX/HOSTING)
        // ====================================================================

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // --- SETTINGAN LOKAL (WINDOWS / LARAGON) ---
            // Sesuaikan path ini jika versi MySQL Laragon Anda berbeda
            $mysqldumpPath = 'D:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe';

            // Cek validitas path windows
            if (! file_exists($mysqldumpPath)) {
                return ['success' => false, 'message' => 'File mysqldump.exe tidak ditemukan di path Windows. Cek DatabaseHelper.php.'];
            }

        } else {
            // --- SETTINGAN HOSTING (LINUX) ---
            // Di hosting, biasanya 'mysqldump' sudah terdaftar di global path.
            // Jika gagal, coba ganti menjadi '/usr/bin/mysqldump'
            $mysqldumpPath = 'mysqldump';
        }

        // Buat nama file backup yang unik dengan tanggal dan waktu
        $fileName = 'backup-' . $this->dbName . '-' . date('Y-m-d-H-i-s') . '.sql';
        $filePath = $this->backupPath . '/' . $fileName;

        // Bangun perintah command line untuk mysqldump
        // Catatan: Pada Linux hosting, terkadang perlu menambahkan path absolut untuk password jika gagal
        $passwordArg = ! empty($this->dbPass) ? "-p\"{$this->dbPass}\"" : "";

        // Perintah dasar
        $command = "\"{$mysqldumpPath}\" -h {$this->dbHost} -u {$this->dbUser} {$passwordArg} {$this->dbName} > \"{$filePath}\"";

        // Khusus Linux Hosting: Tambahkan 2>&1 untuk menangkap error output jika debug diperlukan
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            // Opsional: Tambahkan flag --no-tablespaces jika user hosting tidak punya akses process
            // $command = "mysqldump --no-tablespaces -h {$this->dbHost} -u {$this->dbUser} {$passwordArg} {$this->dbName} > \"{$filePath}\"";
        }

        // Jalankan perintah
        exec($command, $output, $returnVar);

        // Cek apakah perintah berhasil dan file telah dibuat (Ukuran file > 0)
        if ($returnVar === 0 && file_exists($filePath) && filesize($filePath) > 0) {

            // FITUR KOMPATIBILITAS:
            $fileContents = file_get_contents($filePath);
            // Ganti collation modern (MySQL 8) dengan yang lebih kompatibel
            $fileContents = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', $fileContents);
            file_put_contents($filePath, $fileContents);

            return ['success' => true, 'filePath' => $filePath, 'fileName' => $fileName];
        } else {
            // Jika gagal di hosting, seringkali karena fungsi exec() diblokir
            $errorMsg = 'Gagal membuat backup.';

            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $disabled = explode(',', ini_get('disable_functions'));
                if (in_array('exec', $disabled)) {
                    $errorMsg .= ' Fungsi exec() dinonaktifkan oleh penyedia hosting Anda.';
                } else {
                    $errorMsg .= ' Pastikan user database memiliki izin LOCK TABLES atau gunakan --no-tablespaces.';
                }
            }

            return ['success' => false, 'message' => $errorMsg, 'output' => $output];
        }
    }
}
