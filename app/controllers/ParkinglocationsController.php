<?php

// Gunakan namespace dari PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

class ParkinglocationsController extends Controller
{
    public function __construct()
    {
        // Middleware: Cek apakah user sudah login
        if (! isset($_SESSION['user_id'])) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Anda harus login terlebih dahulu.'];
            $this->redirect('auth/login');
        }
    }

    // Menampilkan halaman utama (daftar lokasi)
    public function index()
    {
        $locationModel    = $this->model('ParkingLocation');
        $coordinatorModel = $this->model('FieldCoordinator');

                      // --- LOGIKA PAGINATION & FILTER ---
        $limit  = 15; // Jumlah data per halaman
        $page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Ambil filter koordinator dari URL jika ada
        $selected_coordinator = isset($_GET['coordinator_id']) && ! empty($_GET['coordinator_id']) ? $_GET['coordinator_id'] : null;

        // Ambil total data untuk menghitung total halaman
        $total_results = $locationModel->getTotalCount($selected_coordinator);
        $total_pages   = ceil($total_results / $limit);

        // Ambil data yang sudah dipaginasi dan difilter
        $locations = $locationModel->getPaginated($limit, $offset, $selected_coordinator);

        // Siapkan data untuk dikirim ke view
        $data['locations']    = $locations;
        $data['coordinators'] = $coordinatorModel->getAll();
        $data['title']        = 'Manajemen Lokasi Parkir';
        $data['csrf_token']   = $this->generateCsrf();

        // Data untuk pagination
        $data['page']                 = $page;
        $data['total_pages']          = $total_pages;
        $data['selected_coordinator'] = $selected_coordinator;

        $this->view('layouts/header', $data);
        $this->view('parking_locations/index', $data);
        $this->view('layouts/footer');
    }
    // Menyimpan satu lokasi baru dari modal
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['user_role'] === 'admin') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('CSRF token validation failed.');
            }

            $locationModel = $this->model('ParkingLocation');
            if ($locationModel->create($_POST)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lokasi baru berhasil ditambahkan.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambahkan lokasi.'];
            }
        }
        $this->redirect('parkinglocations');
    }

    // FUNGSI BARU: Mengambil data satu lokasi sebagai JSON untuk modal edit
    public function getParkingLocationJson($id)
    {
        header('Content-Type: application/json');
        if ($_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Akses ditolak']);
            exit();
        }
        $locationModel = $this->model('ParkingLocation');
        $location      = $locationModel->getById($id);
        if ($location) {
            echo json_encode($location);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Lokasi tidak ditemukan']);
        }
        exit();
    }

    // Mengupdate data dari modal edit
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['user_role'] === 'admin') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('CSRF token validation failed.');
            }

            $locationModel = $this->model('ParkingLocation');
            if ($locationModel->update($id, $_POST)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data lokasi berhasil diupdate.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal mengupdate data lokasi.'];
            }
        }
        $this->redirect('parkinglocations');
    }

    public function destroy($id)
    {
        // Pastikan hanya admin yang bisa menghapus dan requestnya adalah POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {

            // Verifikasi CSRF token
            if (! isset($_POST['csrf_token']) || ! $this->verifyCsrf($_POST['csrf_token'])) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'CSRF token tidak valid.'];
                $this->redirect('parkinglocations');
                return;
            }

            $locationModel = $this->model('ParkingLocation');
            if ($locationModel->delete($id)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lokasi parkir berhasil dihapus.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus lokasi parkir.'];
            }
            $this->redirect('parkinglocations');

        } else {
            // Jika bukan admin atau bukan POST, tolak akses
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses tidak sah.'];
            $this->redirect('parkinglocations');
        }
    }

    // FUNGSI BARU: Memproses file import
    public function import()
    {
        ini_set("auto_detect_line_endings", true);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['user_role'] === 'admin') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('CSRF token validation failed.');
            }

            if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
                $file           = $_FILES['import_file']['tmp_name'];
                $coordinator_id = $_POST['field_coordinator_id'];

                try {
                    // --- BLOK KODE YANG DIPERBARUI ---
                    $inputFileType = IOFactory::identify($file);
                    $reader        = IOFactory::createReader($inputFileType);

                    if ($inputFileType == 'Csv') {
                        $reader->setDelimiter(';');
                        $reader->setInputEncoding('UTF-8');
                    }
                    $spreadsheet = $reader->load($file);
                    // --- AKHIR BLOK KODE YANG DIPERBARUI ---

                    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                    $locationsToInsert = [];
                    for ($row = 2; $row <= count($sheetData); $row++) {
                        $locationName = trim($sheetData[$row]['A']);
                        $address      = trim($sheetData[$row]['B']);

                        if (! empty($locationName) && ! empty($address)) {
                            $locationsToInsert[] = [
                                'field_coordinator_id' => $coordinator_id,
                                'parking_location'     => $locationName,
                                'address'              => $address,
                            ];
                        }
                    }

                    if (! empty($locationsToInsert)) {
                        $locationModel = $this->model('ParkingLocation');
                        if ($locationModel->createBatch($locationsToInsert)) {
                            $_SESSION['flash'] = ['type' => 'success', 'message' => count($locationsToInsert) . ' lokasi berhasil diimpor.'];
                        } else {
                            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menyimpan data ke database.'];
                        }
                    } else {
                        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Tidak ada data valid untuk diimpor. Pastikan file tidak kosong dan formatnya benar.'];
                    }

                } catch (Exception $e) {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal membaca file. Error: ' . $e->getMessage()];
                }

            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'File tidak valid atau gagal diupload.'];
            }
            $this->redirect('parkinglocations');
        }
    }

    public function searchJson()
    {
        header('Content-Type: application/json');
        $term = $_GET['q'] ?? '';

        $locationModel = $this->model('ParkingLocation');
        // Kita perlu method baru di model untuk ini
        $results = $locationModel->searchByName($term);

        echo json_encode($results);
        exit();
    }

// METHOD BARU: Untuk mengambil detail lengkap satu lokasi
    public function getLocationDetailsJson($id)
    {
        header('Content-Type: application/json');

        $locationModel = $this->model('ParkingLocation');
        $depositModel  = $this->model('ParkingDeposit');

        $details  = [];
        $location = $locationModel->getById($id);

        if ($location) {
            $details['location'] = $location;
            // Ambil juga data deposit yang berelasi
            // Kita perlu method baru di model untuk ini
            $deposits = $depositModel->getByLocationId($id);

            // Filter informasi berdasarkan role user
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                // Admin dapat melihat semua data
                $details['deposits'] = $deposits;
            } else {
                // Guest tidak melihat data sensitif
                $details['deposits'] = null;
            }
            echo json_encode($details);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Lokasi tidak ditemukan']);
        }
        exit();
    }

    // FUNGSI BARU: Endpoint JSON untuk pencarian alamat (jalan)
    public function searchAddressJson()
    {
        header('Content-Type: application/json');
        $term = $_GET['q'] ?? '';

        if (strlen($term) < 3) {
            echo json_encode([]); // Jangan cari jika input terlalu pendek
            exit();
        }

        $locationModel = $this->model('ParkingLocation');
        $results       = $locationModel->searchAddress($term);

        echo json_encode($results);
        exit();
    }

// FUNGSI BARU: Endpoint JSON untuk mengambil lokasi berdasarkan alamat (jalan)
    public function getLocationsByAddressJson()
    {
        header('Content-Type: application/json');
        $address = $_GET['address'] ?? '';

        if (empty($address)) {
            echo json_encode(['error' => 'Alamat tidak boleh kosong']);
            exit();
        }

        $locationModel = $this->model('ParkingLocation');
        $results       = $locationModel->getByAddress($address);

        echo json_encode($results);
        exit();
    }

    public function searchCoordinatorsJson()
    {
        header('Content-Type: application/json');
        $term = $_GET['q'] ?? '';

        if (strlen($term) < 2) {
            echo json_encode([]);
            exit();
        }

        // Kita panggil model FieldCoordinator di sini
        $coordinatorModel = $this->model('FieldCoordinator');
        $results          = $coordinatorModel->searchByName($term);

        echo json_encode($results);
        exit();
    }

// FUNGSI BARU: Endpoint JSON untuk mengambil semua lokasi milik satu koordinator
    public function getLocationsByCoordinatorJson($coordinator_id)
    {
        header('Content-Type: application/json');

        if (empty($coordinator_id)) {
            echo json_encode(['error' => 'ID Koordinator tidak boleh kosong']);
            exit();
        }

        $locationModel = $this->model('ParkingLocation');
        // Kita gunakan lagi fungsi getPaginated dengan limit yang sangat besar
        $results = $locationModel->getPaginated(1000, 0, $coordinator_id);

        echo json_encode($results);
        exit();
    }
}
