<?php

class BackupController extends Controller
{
    public function __construct()
    {
        // Middleware: Hanya Admin
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            // Jika request AJAX, kirim JSON error
            if (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['error' => 'Akses ditolak']);
                exit;
            }
            // Jika akses biasa, redirect
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $data['title']      = 'Backup Database';
        $data['csrf_token'] = $this->generateCsrf();

        $this->view('layouts/header', $data);
        $this->view('backup/index', $data);
        $this->view('layouts/footer');
    }

    public function create()
    {
        // Hanya menerima POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('backup');
        }

        // Verifikasi CSRF
        if (! $this->verifyCsrf($_POST['csrf_token'] ?? '')) {
            http_response_code(403); // Forbidden
            die('CSRF Token Invalid');
        }

        // Set waktu eksekusi dan memori lebih besar untuk database besar
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 menit

        // Panggil Helper Database
        require_once '../app/helpers/DatabaseHelper.php';
        $dbHelper = new DatabaseHelper();
        $result   = $dbHelper->backup();

        if ($result['success']) {
            $file = $result['filePath'];

            if (file_exists($file)) {
                // --- STREAM FILE KE BROWSER ---
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));

                // Bersihkan output buffer agar file tidak korup
                ob_clean();
                flush();

                readfile($file);
                exit; // Stop eksekusi script di sini
            } else {
                http_response_code(500);
                echo "File backup berhasil dibuat tapi tidak ditemukan di server.";
            }
        } else {
            // Kirim Error ke AJAX
            http_response_code(500);
            echo "Gagal membuat backup: " . $result['message'];
        }
    }
}
