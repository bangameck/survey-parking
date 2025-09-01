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
                                                                                       // PENTING: Sesuaikan path ini dengan lokasi mysqldump.exe di Laragon Anda!
                                                                                       // Cari di D:\laragon\bin\mysql\...\bin\mysqldump.exe
                                                                                       // ====================================================================
        $mysqldumpPath = 'D:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe'; // CONTOH PATH, HARAP DISESUAIKAN

        if (! file_exists($mysqldumpPath)) {
            // Jika mysqldump tidak ditemukan, kembalikan pesan error yang jelas
            return ['success' => false, 'message' => 'File mysqldump.exe tidak ditemukan di path yang ditentukan. Silakan periksa file DatabaseHelper.php dan sesuaikan path-nya. Path yang dicari: ' . $mysqldumpPath];
        }

        // Buat nama file backup yang unik dengan tanggal dan waktu
        $fileName = 'backup-' . $this->dbName . '-' . date('Y-m-d-H-i-s') . '.sql';
        $filePath = $this->backupPath . '/' . $fileName;

        // Bangun perintah command line untuk mysqldump
        // PENTING: tidak ada spasi antara -p dan password jika password ada
        $passwordArg = ! empty($this->dbPass) ? "-p\"{$this->dbPass}\"" : "";
        $command     = "\"{$mysqldumpPath}\" -h {$this->dbHost} -u {$this->dbUser} {$passwordArg} {$this->dbName} > \"{$filePath}\"";

        // Jalankan perintah untuk membuat file backup
        exec($command, $output, $returnVar);

        // Cek apakah perintah berhasil dan file telah dibuat
        if ($returnVar === 0 && file_exists($filePath)) {

            // FITUR KOMPATIBILITAS HOSTING:
            // Baca seluruh isi file backup yang baru dibuat
            $fileContents = file_get_contents($filePath);
            // Ganti collation modern (MySQL 8) dengan yang lebih kompatibel (MySQL 5.7 / MariaDB)
            $fileContents = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', $fileContents);
            // Tulis kembali perubahan ke file
            file_put_contents($filePath, $fileContents);

            return ['success' => true, 'filePath' => $filePath, 'fileName' => $fileName];
        } else {
            return ['success' => false, 'message' => 'Gagal membuat file backup. Cek konfigurasi, path mysqldump, atau izin folder.', 'output' => $output];
        }
    }
}
