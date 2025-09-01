<?php
class BackupController extends Controller
{

    public function __construct()
    {
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak.'];
            $this->redirect('admin');
        }
    }

    // Menampilkan halaman backup
    public function index()
    {
        $data['title']      = 'Backup Database';
        $data['csrf_token'] = $this->generateCsrf();
        $this->view('layouts/header', $data);
        $this->view('backup/index', $data);
        $this->view('layouts/footer');
    }

    // Membuat dan men-download file backup
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('CSRF token validation failed.');
            }

            // Panggil helper, bukan model
            require_once '../app/helpers/DatabaseHelper.php';
            $helper = new DatabaseHelper();
            $result = $helper->backup();

            if ($result['success']) {
                $filePath = $result['filePath'];
                $fileName = $result['fileName'];

                // Jika file berhasil dibuat, paksa browser untuk men-downloadnya
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                flush(); // Flush aystem output buffer
                readfile($filePath);

                // Hapus file sementara setelah di-download
                unlink($filePath);
                exit;
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => $result['message']];
                $this->redirect('backup');
            }
        }
    }
}
