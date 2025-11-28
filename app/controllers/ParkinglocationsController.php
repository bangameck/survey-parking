<?php

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ParkinglocationsController extends Controller
{
    public function __construct()
    {
        // UPDATE: Izinkan Admin, Pimpinan, dan Bendahara masuk
        $allowedRoles = ['admin', 'pimpinan', 'bendahara'];

        if (! isset($_SESSION['user_id']) || ! in_array($_SESSION['user_role'], $allowedRoles)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak.'];
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $locationModel    = $this->model('ParkingLocation');
        $coordinatorModel = $this->model('FieldCoordinator');

        $limit  = 15;
        $page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $selected_coordinator = isset($_GET['coordinator_id']) && ! empty($_GET['coordinator_id']) ? $_GET['coordinator_id'] : null;
        $searchTerm           = isset($_GET['q']) && ! empty($_GET['q']) ? $_GET['q'] : null;

        $total_results = $locationModel->getTotalCount($selected_coordinator, $searchTerm);
        $total_pages   = ceil($total_results / $limit);

        $locations = $locationModel->getPaginated($limit, $offset, $selected_coordinator, $searchTerm);

        $data['locations']    = $locations;
        $data['coordinators'] = $coordinatorModel->getAll();
        $data['title']        = 'Manajemen Lokasi Parkir';
        $data['csrf_token']   = $this->generateCsrf();

        $data['page']                 = $page;
        $data['total_pages']          = $total_pages;
        $data['selected_coordinator'] = $selected_coordinator;
        $data['searchTerm']           = $searchTerm;

        $this->view('layouts/header', $data);
        $this->view('parking_locations/index', $data);
        $this->view('layouts/footer');
    }

    public function store()
    {
        // PROTEKSI: Hanya Admin
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya Admin yang boleh menambah data.'];
            $this->redirect('parkinglocations');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {die('CSRF token validation failed.');}

            $locationModel = $this->model('ParkingLocation');
            if ($locationModel->create($_POST)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lokasi baru berhasil ditambahkan.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambahkan lokasi.'];
            }
        }
        $this->redirect('parkinglocations');
    }

    public function update($id)
    {
        // PROTEKSI: Hanya Admin
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya Admin yang boleh mengubah data.'];
            $this->redirect('parkinglocations');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {die('CSRF token validation failed.');}

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
        // PROTEKSI: Hanya Admin
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya Admin yang boleh menghapus data.'];
            $this->redirect('parkinglocations');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! isset($_POST['csrf_token']) || ! $this->verifyCsrf($_POST['csrf_token'])) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'CSRF token tidak valid.'];
                $this->redirect('parkinglocations');return;
            }

            $locationModel = $this->model('ParkingLocation');
            if ($locationModel->delete($id)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lokasi parkir berhasil dihapus.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus lokasi parkir.'];
            }
            $this->redirect('parkinglocations');
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses tidak sah.'];
            $this->redirect('parkinglocations');
        }
    }

    // =========================================================================
    // API JSON (DIAKSES OLEH ADMIN & PIMPINAN)
    // =========================================================================

    public function getParkingLocationJson($id)
    {
        header('Content-Type: application/json');
        // Khusus get data edit, biasanya admin. Tapi Pimpinan bisa saja perlu detail.
        // Kita buka saja untuk read.

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

    public function getLocationDetailsJson($id)
    {
        header('Content-Type: application/json');
        $locationModel = $this->model('ParkingLocation');
        $depositModel  = $this->model('ParkingDeposit');

        $details  = [];
        $location = $locationModel->getById($id);

        if ($location) {
            $details['location'] = $location;
            $deposits            = $depositModel->getByLocationId($id);

            // PERBAIKAN: Izinkan Admin, Pimpinan, dan Bendahara melihat detail uang
            if (in_array($_SESSION['user_role'], ['admin', 'pimpinan', 'bendahara'])) {
                $details['deposits'] = $deposits;
            } else {
                $details['deposits'] = null;
            }
            echo json_encode($details);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Lokasi tidak ditemukan']);
        }
        exit();
    }

    public function getLocationsByCoordinatorJson($coordinator_id)
    {
        header('Content-Type: application/json');

        if (empty($coordinator_id)) {
            echo json_encode(['error' => 'ID Koordinator tidak boleh kosong']);
            exit();
        }

        $locationModel = $this->model('ParkingLocation');
        $results       = [];

        // Pimpinan & Bendahara juga boleh lihat detail lengkap (untuk kalkulasi total di dashboard)
        if (in_array($_SESSION['user_role'], ['admin', 'pimpinan', 'bendahara'])) {
            $results = $locationModel->getDetailsByCoordinatorId($coordinator_id);
        } else {
            $results = $locationModel->getPaginated(1000, 0, $coordinator_id);
        }

        echo json_encode($results);
        exit();
    }

    public function searchJson()
    {
        header('Content-Type: application/json');
        $term          = $_GET['q'] ?? '';
        $locationModel = $this->model('ParkingLocation');
        $results       = $locationModel->searchByName($term);
        echo json_encode($results);
        exit();
    }

    public function searchAddressJson()
    {
        header('Content-Type: application/json');
        $term = $_GET['q'] ?? '';
        if (strlen($term) < 3) {echo json_encode([]);exit();}
        $locationModel = $this->model('ParkingLocation');
        $results       = $locationModel->searchAddress($term);
        echo json_encode($results);
        exit();
    }

    public function getLocationsByAddressJson()
    {
        header('Content-Type: application/json');
        $address = $_GET['address'] ?? '';
        if (empty($address)) {echo json_encode(['error' => 'Alamat tidak boleh kosong']);exit();}
        $locationModel = $this->model('ParkingLocation');
        $results       = $locationModel->getByAddress($address);
        echo json_encode($results);
        exit();
    }

    public function searchCoordinatorsJson()
    {
        header('Content-Type: application/json');
        $term = $_GET['q'] ?? '';
        if (strlen($term) < 2) {echo json_encode([]);exit();}
        $coordinatorModel = $this->model('FieldCoordinator');
        $results          = $coordinatorModel->searchByName($term);
        echo json_encode($results);
        exit();
    }

    // =========================================================================
    // FITUR LAIN (IMPORT & BATCH ACTION)
    // =========================================================================

    public function import()
    {
        // PROTEKSI: Hanya Admin
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya Admin yang boleh melakukan import.'];
            $this->redirect('parkinglocations');
            return;
        }

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {die('CSRF token validation failed.');}

            if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
                $file           = $_FILES['import_file']['tmp_name'];
                $coordinator_id = $_POST['field_coordinator_id'];
                $default_zone   = ! empty($_POST['default_zone']) ? $_POST['default_zone'] : null;

                try {
                    $spreadsheet = IOFactory::load($file);
                    $sheetData   = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                    $locationsToInsert = [];
                    foreach ($sheetData as $index => $row) {
                        if ($index == 1) {
                            continue;
                        }

                        $locationName = trim($row['A']);
                        $address      = trim($row['B']);
                        $excelZone    = isset($row['C']) ? trim($row['C']) : null;
                        $finalZone    = ! empty($excelZone) ? $excelZone : $default_zone;

                        if (! empty($locationName)) {
                            $locationsToInsert[] = [
                                'field_coordinator_id' => $coordinator_id,
                                'parking_location'     => $locationName,
                                'address'              => $address,
                                'zone'                 => $finalZone,
                            ];
                        }
                    }

                    if (! empty($locationsToInsert)) {
                        $locationModel = $this->model('ParkingLocation');
                        if ($locationModel->createBatch($locationsToInsert)) {
                            $_SESSION['flash'] = ['type' => 'success', 'message' => count($locationsToInsert) . ' lokasi berhasil diimpor!'];
                        } else {
                            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menyimpan data ke database.'];
                        }
                    } else {
                        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'File kosong atau format salah.'];
                    }

                } catch (Exception $e) {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
                }
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'File wajib diupload.'];
            }
        }
        $this->redirect('parkinglocations');
    }

    public function updateBatch()
    {
        // PROTEKSI: Hanya Admin
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya Admin yang boleh melakukan aksi massal.'];
            $this->redirect('parkinglocations');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {die('Token Invalid');}

            $location_ids = $_POST['location_ids'] ?? [];
            $bulk_zone    = $_POST['bulk_zone'] ?? null;

            if (empty($location_ids) || empty($bulk_zone)) {
                $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Pilih lokasi dan zona tujuan terlebih dahulu.'];
                $this->redirect('parkinglocations');return;
            }

            $locationModel = $this->model('ParkingLocation');
            if ($locationModel->updateBatch($location_ids, $bulk_zone)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => count($location_ids) . ' lokasi berhasil diubah.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal update massal.'];
            }

            $q        = $_POST['q_hidden'] ?? '';
            $coord_id = $_POST['coordinator_id_hidden'] ?? '';
            $params   = http_build_query(['q' => $q, 'coordinator_id' => $coord_id]);
            $this->redirect('parkinglocations?' . $params);

        } else {
            $this->redirect('parkinglocations');
        }
    }

    public function destroyBatch()
    {
        // PROTEKSI: Hanya Admin
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya Admin yang boleh menghapus data.'];
            $this->redirect('parkinglocations');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {die('Token Invalid');}

            $location_ids = $_POST['location_ids'] ?? [];
            if (empty($location_ids)) {
                $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Tidak ada lokasi yang dipilih.'];
                $this->redirect('parkinglocations');return;
            }
            $locationModel = $this->model('ParkingLocation');
            if ($locationModel->deleteBatch($location_ids)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => count($location_ids) . ' lokasi berhasil dihapus.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus massal.'];
            }

            $q        = $_POST['q_hidden'] ?? '';
            $coord_id = $_POST['coordinator_id_hidden'] ?? '';
            $params   = http_build_query(['q' => $q, 'coordinator_id' => $coord_id]);
            $this->redirect('parkinglocations?' . $params);
        } else {
            $this->redirect('parkinglocations');
        }
    }

    public function export_pdf()
    {
        // Akses: Admin, Pimpinan, Bendahara
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $coordinator_id = isset($_GET['coordinator_id']) ? $_GET['coordinator_id'] : null;
        $searchTerm     = isset($_GET['q']) ? $_GET['q'] : null;

        $locationModel = $this->model('ParkingLocation');
        $locations     = $locationModel->getPaginated(9999, 0, $coordinator_id, $searchTerm);

        $data['locations']  = $locations;
        $data['title']      = 'Laporan Lokasi Parkir';
        $data['searchTerm'] = $searchTerm;

        $data['coordinator_name'] = null;
        if ($coordinator_id) {
            $coordinatorModel         = $this->model('FieldCoordinator');
            $coordinator              = $coordinatorModel->getById($coordinator_id);
            $data['coordinator_name'] = $coordinator->name;
        }

        ob_start();
        $this->view('parking_locations/pdf_template', $data);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isPhpEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('F4', 'landscape');
        $dompdf->render();
        $dompdf->stream("Laporan-Lokasi-" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
        exit();
    }
}
