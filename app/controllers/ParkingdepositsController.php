<?php
use Dompdf\Dompdf;

class ParkingdepositsController extends Controller
{

    public function __construct()
    {
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak.'];
            $this->redirect('admin');
        }
    }

    // Menampilkan halaman utama (pemilihan koordinator & form input)
    public function index()
    {
        $coordinatorModel = $this->model('FieldCoordinator');
        $locationModel    = $this->model('ParkingLocation');
        $depositModel     = $this->model('ParkingDeposit');

        $data['coordinators']            = $coordinatorModel->getAll();
        $data['selected_coordinator_id'] = isset($_GET['coordinator_id']) ? $_GET['coordinator_id'] : null;
        $data['locations']               = [];
        $data['deposits']                = [];
        $data['existing_document']       = null; // Tambahkan variabel ini

        if ($data['selected_coordinator_id']) {
            $data['locations'] = $locationModel->getPaginated(1000, 0, $data['selected_coordinator_id']);
            $data['deposits']  = $depositModel->getDepositsByCoordinator($data['selected_coordinator_id']);

            // Cek apakah ada dokumen yang sudah ada di salah satu data
            if (! empty($data['deposits'])) {
                foreach ($data['deposits'] as $dep) {
                    if (! empty($dep->document_survey)) {
                        $data['existing_document'] = $dep->document_survey;
                        break; // Cukup temukan satu, karena semuanya harusnya sama
                    }
                }
            }
        }

        $data['title']      = 'Input Setoran Parkir';
        $data['csrf_token'] = $this->generateCsrf();

        $this->view('layouts/header', $data);
        $this->view('parking_deposits/index', $data);
        $this->view('layouts/footer');
    }

    // Menyimpan semua data dari form
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('CSRF token validation failed.');
            }

            $documentPath = null;
            // Proses file upload
            if (isset($_FILES['document_survey']) && $_FILES['document_survey']['error'] == 0) {
                $targetDir = "uploads/surveys/";
                if (! is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $fileName   = uniqid() . '-' . basename($_FILES["document_survey"]["name"]);
                $targetFile = $targetDir . $fileName;

                // Hanya izinkan PDF
                $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                if ($fileType != "pdf") {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya file PDF yang diizinkan.'];
                    $this->redirect('parkingdeposits?coordinator_id=' . $_POST['coordinator_id']);
                    return;
                }

                if (move_uploaded_file($_FILES["document_survey"]["tmp_name"], $targetFile)) {
                    $documentPath = $targetFile;
                } else {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal mengupload file dokumen.'];
                    $this->redirect('parkingdeposits?coordinator_id=' . $_POST['coordinator_id']);
                    return;
                }
            }

            $depositModel = $this->model('ParkingDeposit');
            if ($depositModel->upsertBatch($_POST['deposits'], $_POST['surveyor_1'], $_POST['surveyor_2'], $documentPath)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data setoran berhasil disimpan.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Terjadi kesalahan saat menyimpan data.'];
            }

            $this->redirect('parkingdeposits?coordinator_id=' . $_POST['coordinator_id']);
        }
    }

    public function export_pdf()
    {
        // Pastikan koordinator dipilih
        if (! isset($_GET['coordinator_id'])) {
            die('Pilih koordinator terlebih dahulu.');
        }

        $coordinator_id = $_GET['coordinator_id'];

        // Ambil semua data yang relevan (bukan paginasi)
        $coordinatorModel = $this->model('FieldCoordinator');
        $locationModel    = $this->model('ParkingLocation');
        $depositModel     = $this->model('ParkingDeposit');

        $data['coordinator'] = $coordinatorModel->getById($coordinator_id);
        $data['locations']   = $locationModel->getPaginated(1000, 0, $coordinator_id);
        $data['deposits']    = $depositModel->getDepositsByCoordinator($coordinator_id);

        if (! $data['coordinator']) {
            die('Koordinator tidak ditemukan.');
        }

        $data['coordinator_name'] = $data['coordinator']->name;

        $data['surveyor_1'] = 'N/A'; // Nilai default jika tidak ada data
        $data['surveyor_2'] = 'N/A'; // Nilai default jika tidak ada data

        if (! empty($data['deposits'])) {
            $first_deposit      = reset($data['deposits']); // Mengambil elemen pertama dari array
            $data['surveyor_1'] = ! empty($first_deposit->surveyor_1) ? $first_deposit->surveyor_1 : 'N/A';
            $data['surveyor_2'] = ! empty($first_deposit->surveyor_2) ? $first_deposit->surveyor_2 : 'N/A';
        }

        // Render view PDF ke dalam sebuah variabel string
        ob_start();
        $this->view('parking_deposits/pdf_template', $data);
        $html = ob_get_clean();

        // Inisialisasi Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // Definisikan ukuran F4 dalam points [kiri, atas, lebar, tinggi]
        $customPaper = [0, 0, 595.28, 935.43];

        // Terapkan ukuran F4 dengan orientasi portrait
        $dompdf->setPaper($customPaper, 'portrait');

        // Render HTML sebagai PDF
        $dompdf->render();

        // Output PDF yang dihasilkan ke browser
        // "Attachment" => false akan menampilkan PDF di browser, true akan langsung men-download
        $dompdf->stream("laporan-hasil-survey-harian-" . $data['coordinator_name'] . ".pdf", ["Attachment" => false]);
        exit();
    }
}
