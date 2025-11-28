<?php
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ParkingdepositsController extends Controller
{

    public function __construct()
    {
        // UPDATE: Izinkan Admin, Pimpinan, dan Bendahara
        $allowedRoles = ['admin', 'pimpinan', 'bendahara'];

        if (! isset($_SESSION['user_id']) || ! in_array($_SESSION['user_role'], $allowedRoles)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak.'];
            $this->redirect('auth/login');
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
        // PROTEKSI: Hanya Admin yang boleh Input/Simpan
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya Admin yang boleh menginput data setoran.'];
            $this->redirect('parkingdeposits?coordinator_id=' . ($_POST['coordinator_id'] ?? ''));
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('CSRF token validation failed.');
            }

            $documentPath = null;
            if (isset($_FILES['document_survey']) && $_FILES['document_survey']['error'] == 0) {
                $targetDir = "uploads/surveys/";
                if (! is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $fileName   = uniqid() . '-' . basename($_FILES["document_survey"]["name"]);
                $targetFile = $targetDir . $fileName;

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
        $dompdf->set_option('author', 'Aplikasi Survey UPT Perparkiran');
        $dompdf->add_info('Creator', 'https://survey.uptperparkiranpku.com');
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

    public function export_excel()
    {
        // Pastikan koordinator dipilih dan user adalah admin
        if (! isset($_GET['coordinator_id']) || $_SESSION['user_role'] !== 'admin') {
            die('Akses ditolak atau Koordinator belum dipilih.');
        }

        $coordinator_id = $_GET['coordinator_id'];

        // Ambil semua data yang relevan
        $coordinatorModel = $this->model('FieldCoordinator');
        $locationModel    = $this->model('ParkingLocation');
        $coordinator      = $coordinatorModel->getById($coordinator_id);
        $locations        = $locationModel->getDetailsByCoordinatorId($coordinator_id);

        if (! $coordinator) {
            die('Koordinator tidak ditemukan.');
        }

        // ====================================================================
        // PROSES PEMBUATAN EXCEL DENGAN STYLING
        // ====================================================================

        // 1. Buat Spreadsheet Baru
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // --- 2. Konfigurasi Dasar & Judul ---
        $sheet->setTitle('Laporan Setoran');

        // Judul Utama Laporan
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'LAPORAN SURVEY POTENSI PENDAPATAN PARKIR');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Informasi Koordinator dan Tanggal
        $sheet->setCellValue('A3', 'NAMA KOORDINATOR:');
        $sheet->setCellValue('B3', $coordinator->name);
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->setCellValue('F3', 'TANGGAL CETAK:');
        $sheet->setCellValue('G3', date('d F Y'));
        $sheet->getStyle('F3')->getFont()->setBold(true);

        // --- 3. Menulis Header Tabel ---
        $headers = ['NO', 'NAMA LOKASI', 'ALAMAT', 'HARIAN', 'SABTU/MINGGU', 'BULANAN', 'KETERANGAN'];
        $sheet->fromArray($headers, null, 'A5');
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E2F3']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A5:G5')->applyFromArray($headerStyle);

                           // --- 4. Menulis Data ke Setiap Baris ---
        $row          = 6; // Mulai dari baris 6
        $nomor        = 1;
        $totalDaily   = 0;
        $totalWeekend = 0;
        $totalMonthly = 0;

        foreach ($locations as $loc) {
            $sheet->setCellValue('A' . $row, $nomor++);
            $sheet->setCellValue('B' . $row, $loc->parking_location);
            $sheet->setCellValue('C' . $row, $loc->address);
            $sheet->setCellValue('D' . $row, $loc->daily_deposits ?? 0);
            $sheet->setCellValue('E' . $row, $loc->weekend_deposits ?? 0);
            $sheet->setCellValue('F' . $row, $loc->monthly_deposits ?? 0);
            $sheet->setCellValue('G' . $row, $loc->information ?? '');

            $totalDaily += $loc->daily_deposits ?? 0;
            $totalWeekend += $loc->weekend_deposits ?? 0;
            $totalMonthly += $loc->monthly_deposits ?? 0;

            $row++;
        }

        // --- 5. Menulis Baris Total ---
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('D' . $row, $totalDaily);
        $sheet->setCellValue('E' . $row, $totalWeekend);
        $sheet->setCellValue('F' . $row, $totalMonthly);

        $totalStyle = [
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFABF8F']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
        ];
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($totalStyle);
        // Khusus untuk total currency agar rata kanan
        $sheet->getStyle('D' . $row . ':F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // --- 6. Menerapkan Styling Global ---
        $lastRow = $row;
        // Format Angka/Mata Uang
        $currencyFormat = '#,##0';
        $sheet->getStyle('D6:F' . $lastRow)->getNumberFormat()->setFormatCode($currencyFormat);

        // Alignment
        $sheet->getStyle('A6:A' . $lastRow)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('D6:F' . $lastRow)->getAlignment()->setHorizontal('right');

        // Mengatur Lebar Kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(50);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(40);

        // Menambahkan Border ke seluruh tabel
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A5:G' . $lastRow)->applyFromArray($borderStyle);

        // --- 7. Mengirim File ke Browser ---
        $fileName = "laporan-setoran-" . str_replace(' ', '-', $coordinator->name) . "-" . date('Y-m-d') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}
