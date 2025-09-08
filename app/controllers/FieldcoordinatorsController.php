<?php
use Dompdf\Dompdf;

class FieldcoordinatorsController extends Controller
{

    // Constructor untuk memastikan hanya admin yang bisa akses
    public function __construct()
    {
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak. Anda bukan Admin.'];
            $this->redirect('parkinglocations'); // Redirect ke dashboard
        }
    }

    // Menampilkan daftar semua koordinator
    // Ganti fungsi index() yang lama dengan ini
    public function index()
    {
        $coordinatorModel = $this->model('FieldCoordinator');

        // --- LOGIKA PAGINATION & PENCARIAN ---
        $limit      = 15;
        $page       = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $offset     = ($page - 1) * $limit;
        $searchTerm = isset($_GET['q']) && ! empty($_GET['q']) ? $_GET['q'] : null;

        // Ambil total data untuk menghitung total halaman
        $total_results = $coordinatorModel->getTotalCount($searchTerm);
        $total_pages   = ceil($total_results / $limit);

        // Ambil data yang sudah dipaginasi dan dicari
        $coordinators = $coordinatorModel->getPaginated($limit, $offset, $searchTerm);

        // Siapkan data untuk dikirim ke view
        $data['coordinators'] = $coordinators;
        $data['title']        = 'Manajemen Koordinator';
        $data['csrf_token']   = $this->generateCsrf();

        // Data untuk pagination dan pencarian
        $data['page']        = $page;
        $data['total_pages'] = $total_pages;
        $data['searchTerm']  = $searchTerm;

        $this->view('layouts/header', $data);
        $this->view('field_coordinators/index', $data);
        $this->view('layouts/footer');
    }

    // Menampilkan form untuk membuat koordinator baru
    public function create()
    {
        $data['title']      = 'Tambah Koordinator Baru';
        $data['csrf_token'] = $this->generateCsrf();

        $this->view('layouts/header', $data);
        $this->view('field_coordinators/create', $data);
        $this->view('layouts/footer');
    }

    // Menyimpan data dari form 'create' ke database
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('CSRF token validation failed.');
            }

            $coordinatorModel = $this->model('FieldCoordinator');
            if ($coordinatorModel->create($_POST)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Koordinator baru berhasil ditambahkan!'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambahkan koordinator.'];
            }
            $this->redirect('fieldcoordinators');
        }
    }

    public function getCoordinatorJson($id)
    {
        // Atur header agar browser tahu ini adalah JSON
        header('Content-Type: application/json');

        $coordinatorModel = $this->model('FieldCoordinator');
        $coordinator      = $coordinatorModel->getById($id);

        // Cek jika data ditemukan
        if ($coordinator) {
            echo json_encode($coordinator);
        } else {
            // Kirim response error jika tidak ditemukan
            http_response_code(404);
            echo json_encode(['error' => 'Koordinator tidak ditemukan']);
        }
        exit(); // Hentikan eksekusi setelah mengirim JSON
    }

    // Menampilkan form untuk mengedit koordinator
    public function edit($id)
    {
        $coordinatorModel    = $this->model('FieldCoordinator');
        $data['coordinator'] = $coordinatorModel->getById($id);
        $data['title']       = 'Edit Koordinator';
        $data['csrf_token']  = $this->generateCsrf();

        $this->view('layouts/header', $data);
        $this->view('field_coordinators/edit', $data);
        $this->view('layouts/footer');
    }

    // Mengupdate data dari form 'edit' ke database
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('CSRF token validation failed.');
            }

            $coordinatorModel = $this->model('FieldCoordinator');
            if ($coordinatorModel->update($id, $_POST)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data koordinator berhasil diupdate!'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal mengupdate data.'];
            }
            $this->redirect('fieldcoordinators');
        }
    }

    // Menghapus data koordinator
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Kita bisa menambahkan verifikasi CSRF di sini juga jika tombol delete ada di dalam form
            $coordinatorModel = $this->model('FieldCoordinator');
            if ($coordinatorModel->delete($id)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Koordinator berhasil dihapus!'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus koordinator.'];
            }
            $this->redirect('fieldcoordinators');
        }
    }

    // METHOD BARU UNTUK EXPORT PDF
    public function export_pdf()
    {
        // Pastikan hanya admin yang bisa akses
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            die('Akses ditolak.');
        }

        // Ambil kata kunci pencarian dari URL jika ada
        $searchTerm = isset($_GET['q']) ? $_GET['q'] : null;

        $coordinatorModel = $this->model('FieldCoordinator');

        // Ambil SEMUA data yang cocok (bukan paginasi) dengan filter pencarian
        $coordinators = $coordinatorModel->getPaginated(9999, 0, $searchTerm);

        $data['coordinators'] = $coordinators;
        $data['title']        = 'Laporan Koordinator Lapangan';

        // Render view PDF ke dalam sebuah variabel string menggunakan output buffering
        ob_start();
        $this->view('field_coordinators/pdf_template', $data);
        $html = ob_get_clean();

        // Inisialisasi Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // Atur ukuran kertas dan orientasi
        $dompdf->setPaper('A4', 'portrait');

        // Render HTML sebagai PDF
        $dompdf->render();

        // Output PDF yang dihasilkan ke browser untuk di-download atau ditampilkan
        $fileName = "daftar-koordinator-" . date('Y-m-d') . ".pdf";
        $dompdf->stream($fileName, ["Attachment" => false]); // false = tampilkan di browser, true = langsung download
        exit();
    }

    // Ganti fungsi getPaginated() Anda dengan versi lengkap dan benar ini

}
