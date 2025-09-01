<?php
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
    public function index()
    {
        $coordinatorModel     = $this->model('FieldCoordinator');
        $data['coordinators'] = $coordinatorModel->getAll();
        $data['title']        = 'Manajemen Koordinator';

        // Hasilkan token SATU KALI di sini
        $data['csrf_token'] = $this->generateCsrf();

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
}
