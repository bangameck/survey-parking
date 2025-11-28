<?php
use Dompdf\Dompdf;
use Dompdf\Options;

class FieldcoordinatorsController extends Controller
{

    public function __construct()
    {
        // UPDATE: Izinkan Admin, Pimpinan, dan Bendahara mengakses halaman ini
        $allowedRoles = ['admin', 'pimpinan', 'bendahara'];

        if (! isset($_SESSION['user_id']) || ! in_array($_SESSION['user_role'], $allowedRoles)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak.'];
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $coordinatorModel = $this->model('FieldCoordinator');

        $limit  = 15;
        $page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Tangkap Filter
        $searchTerm = isset($_GET['q']) ? $_GET['q'] : null;
        $zone       = isset($_GET['zone']) && ! empty($_GET['zone']) ? $_GET['zone'] : null;

        // Pass filter ke model
        $total_results = $coordinatorModel->getTotalCount($searchTerm, $zone);
        $total_pages   = ceil($total_results / $limit);
        $coordinators  = $coordinatorModel->getPaginated($limit, $offset, $searchTerm, $zone);

        $data = [
            'coordinators' => $coordinators,
            'title'        => 'Manajemen Koordinator',
            'csrf_token'   => $this->generateCsrf(),
            'page'         => $page,
            'total_pages'  => $total_pages,
            'searchTerm'   => $searchTerm,
            'selectedZone' => $zone, // Kirim zona terpilih ke view
        ];

        $this->view('layouts/header', $data);
        $this->view('field_coordinators/index', $data);
        $this->view('layouts/footer');
    }

    public function store()
    {
        // UPDATE: Hanya Admin yang boleh Create
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya Admin yang boleh menambah data.'];
            $this->redirect('fieldcoordinators');
            return;
        }

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
        // API ini aman dibaca oleh semua role yang diizinkan di construct
        header('Content-Type: application/json');
        echo json_encode($this->model('FieldCoordinator')->getById($id));
        exit;
    }

    public function getDetailJson($id)
    {
        // API ini aman dibaca oleh semua role yang diizinkan di construct
        header('Content-Type: application/json');
        echo json_encode($this->model('FieldCoordinator')->getDetailWithLocations($id));
        exit;
    }

    public function update($id)
    {
        // UPDATE: Hanya Admin yang boleh Edit
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya Admin yang boleh mengubah data.'];
            $this->redirect('fieldcoordinators');
            return;
        }

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

    public function destroy($id)
    {
        // UPDATE: Hanya Admin yang boleh Hapus
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya Admin yang boleh menghapus data.'];
            $this->redirect('fieldcoordinators');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $coordinatorModel = $this->model('FieldCoordinator');
            if ($coordinatorModel->delete($id)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data berhasil dihapus!'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus data.'];
            }
            $this->redirect('fieldcoordinators');
        }
    }

    // EXPORT PDF
    public function export_pdf()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $zone = isset($_GET['zone']) && ! empty($_GET['zone']) ? $_GET['zone'] : null;

        $coordinatorModel = $this->model('FieldCoordinator');

        // Ambil data sesuai Zona
        $rawData = $coordinatorModel->getAllWithLocations($zone);

        $groupedData = [];
        foreach ($rawData as $row) {
            if (! isset($groupedData[$row->id])) {
                $groupedData[$row->id] = [
                    'name'         => $row->name,
                    'nik'          => $row->nik ?? '-',          // Masukkan NIK
                    'phone_number' => $row->phone_number ?? '-', // Masukkan HP
                    'pks_expired'  => $row->pks_expired,
                    'locations'    => [],
                ];
            }

            if ($row->parking_location) {
                $groupedData[$row->id]['locations'][] = [
                    'location' => $row->parking_location,
                    'address'  => $row->address,
                    'zone'     => $row->zone,
                ];
            }
        }

        // Sesuaikan Judul
        $title = 'Laporan Koordinator & Lokasi';
        if ($zone) {
            $title .= ' - ' . htmlspecialchars($zone);
        } else {
            $title .= ' - Semua Zona';
        }

        $data = [
            'groupedData' => $groupedData,
            'title'       => $title,
            'app_url'     => BASE_URL . '/fieldcoordinators',
            'date'        => date('d-M-Y H:i:s'),
        ];

        ob_start();
        $this->view('field_coordinators/pdf_template', $data);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isPhpEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('F4', 'portrait');
        $dompdf->render();

        $filename = "Data_Koordinator_" . ($zone ? str_replace(' ', '_', $zone) : 'All') . "_" . date('Y-m-d') . ".pdf";
        $dompdf->stream($filename, ["Attachment" => false]);
        exit();
    }
}
