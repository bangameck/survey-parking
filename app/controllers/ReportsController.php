<?php

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportsController extends Controller
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

    // Menampilkan Halaman Menu Laporan
    public function index()
    {
        $coordinatorModel = $this->model('FieldCoordinator');

        $data['title']        = 'Pusat Laporan & Export';
        $data['coordinators'] = $coordinatorModel->getAll();

        $this->view('layouts/header', $data);
        $this->view('reports/index', $data);
        $this->view('layouts/footer');
    }

    // Proses Utama Export
    public function process()
    {
                                          // PENTING: Naikkan batas memori dan waktu eksekusi untuk data besar
        ini_set('memory_limit', '1024M'); // 1GB
        set_time_limit(300);              // 5 Menit

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type           = $_POST['export_type']; // 'pdf' atau 'excel'
            $coordinator_id = $_POST['coordinator_id'] ?? null;

            $locationModel = $this->model('ParkingLocation');
            $rawData       = $locationModel->getExportData($coordinator_id);

            // --- 1. Hitung Statistik Header ---
            $totalLocations = count($rawData);

            // Hitung koordinator unik yang ada di data hasil filter
            $uniqueCoordinators = [];
            foreach ($rawData as $row) {
                $uniqueCoordinators[$row->coordinator_name] = true;
            }
            $totalCoordinators = count($uniqueCoordinators);

            // --- 2. Siapkan Struktur Data (Grouping & Grand Total) ---
            $grandTotal = [
                'daily'   => 0,
                'weekend' => 0,
                'monthly' => 0,
            ];

            $groupedData = [];
            foreach ($rawData as $row) {
                // Grouping berdasarkan nama koordinator
                $groupedData[$row->coordinator_name][] = $row;

                // Akumulasi Grand Total
                $grandTotal['daily'] += $row->daily_deposits;
                $grandTotal['weekend'] += $row->weekend_deposits;
                $grandTotal['monthly'] += $row->monthly_deposits;
            }

            // Data statistik untuk dikirim ke view/excel
            $stats = [
                'is_filtered'        => ! empty($coordinator_id),
                'total_locations'    => $totalLocations,
                'total_coordinators' => $totalCoordinators,
            ];

            // --- 3. Eksekusi sesuai tipe ---
            if ($type === 'excel') {
                $this->generateExcel($groupedData, $grandTotal, $stats);
            } elseif ($type === 'pdf') {
                $this->generatePdf($groupedData, $coordinator_id, $grandTotal, $stats);
            }
        }

        // Jika bukan POST, kembalikan ke halaman laporan
        $this->redirect('reports');
    }

    // --- FUNGSI PRIVATE: GENERATE EXCEL ---
    private function generateExcel($groupedData, $grandTotal, $stats)
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // --- HEADER LAPORAN ---
        $currentRow = 1;

        // Judul Utama
        $sheet->setCellValue('A' . $currentRow, 'DATA HASIL SURVEY POTENSI PARKIR PEKANBARU');
        $sheet->mergeCells("A{$currentRow}:J{$currentRow}");
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $currentRow++;

        // Info Statistik (Baris 2)
        if (! $stats['is_filtered']) {
            $infoText = "Total Koordinator: {$stats['total_coordinators']}  |  Total Lokasi: {$stats['total_locations']}";
        } else {
            $infoText = "Total Lokasi Parkir: {$stats['total_locations']}";
        }
        $sheet->setCellValue('A' . $currentRow, $infoText);
        $sheet->mergeCells("A{$currentRow}:J{$currentRow}");
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $currentRow++;

        // Tanggal Cetak (Baris 3)
        $sheet->setCellValue('A' . $currentRow, 'Tanggal Cetak: ' . date('d F Y, H:i') . ' WIB');
        $sheet->mergeCells("A{$currentRow}:J{$currentRow}");
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                          // --- HEADER TABEL ---
        $currentRow += 2; // Beri jarak (Mulai Baris 5)
        $headers = ['No', 'Koordinator', 'Nama Lokasi', 'Alamat', 'Setoran Harian', 'Setoran Weekend', 'Setoran Bulanan', 'Surveyor 1', 'Surveyor 2', 'Keterangan'];
        $sheet->fromArray($headers, null, "A{$currentRow}");

        // Styling Header Tabel
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']], // Warna Indigo
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle("A{$currentRow}:J{$currentRow}")->applyFromArray($headerStyle);

        // --- ISI DATA ---
        $startDataRow = $currentRow + 1;
        $no           = 1;

        foreach ($groupedData as $coordinatorName => $locations) {
            $startRow   = $startDataRow;
            $subDaily   = 0;
            $subWeekend = 0;
            $subMonthly = 0;

            foreach ($locations as $item) {
                $sheet->setCellValue('A' . $startDataRow, $no++);
                $sheet->setCellValue('B' . $startDataRow, strtoupper($coordinatorName)); // Uppercase
                $sheet->setCellValue('C' . $startDataRow, $item->parking_location);
                $sheet->setCellValue('D' . $startDataRow, $item->address);
                $sheet->setCellValue('E' . $startDataRow, $item->daily_deposits);
                $sheet->setCellValue('F' . $startDataRow, $item->weekend_deposits);
                $sheet->setCellValue('G' . $startDataRow, $item->monthly_deposits);
                $sheet->setCellValue('H' . $startDataRow, $item->surveyor_1);
                $sheet->setCellValue('I' . $startDataRow, $item->surveyor_2);
                $sheet->setCellValue('J' . $startDataRow, $item->information);

                // Hitung Subtotal
                $subDaily += $item->daily_deposits;
                $subWeekend += $item->weekend_deposits;
                $subMonthly += $item->monthly_deposits;

                $startDataRow++;
            }

            $endRow = $startDataRow - 1;

            // Merge Cell Nama Koordinator
            if ($endRow > $startRow) {
                $sheet->mergeCells("B{$startRow}:B{$endRow}");
            }
            $sheet->getStyle("B{$startRow}:B{$endRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("B{$startRow}:B{$endRow}")->getFont()->setBold(true);

            // Baris Subtotal Per Koordinator
            $sheet->setCellValue('A' . $startDataRow, 'Total ' . $coordinatorName);
            $sheet->mergeCells("A{$startDataRow}:D{$startDataRow}");
            $sheet->setCellValue('E' . $startDataRow, $subDaily);
            $sheet->setCellValue('F' . $startDataRow, $subWeekend);
            $sheet->setCellValue('G' . $startDataRow, $subMonthly);

            $subTotalStyle = [
                'font'    => ['bold' => true],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E7FF']], // Biru Muda
                'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE]],
            ];
            $sheet->getStyle("A{$startDataRow}:J{$startDataRow}")->applyFromArray($subTotalStyle);
            $sheet->getStyle("A{$startDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $startDataRow++;
        }

                         // --- BARIS GRAND TOTAL ---
        $startDataRow++; // Beri jarak 1 baris
        $sheet->setCellValue('A' . $startDataRow, 'TOTAL KESELURUHAN');
        $sheet->mergeCells("A{$startDataRow}:D{$startDataRow}");
        $sheet->setCellValue('E' . $startDataRow, $grandTotal['daily']);
        $sheet->setCellValue('F' . $startDataRow, $grandTotal['weekend']);
        $sheet->setCellValue('G' . $startDataRow, $grandTotal['monthly']);

        $grandTotalStyle = [
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']], // Hijau Emerald
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];
        $sheet->getStyle("A{$startDataRow}:J{$startDataRow}")->applyFromArray($grandTotalStyle);
        $sheet->getStyle("A{$startDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($startDataRow)->setRowHeight(30);

        // --- FORMATTING AKHIR ---
        $lastRow = $startDataRow;

        // Format Currency (Rupiah)
        $sheet->getStyle("E5:G{$lastRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0_-');

        // Border untuk semua data (kecuali grand total yang sudah punya style sendiri)
        $borderEndRow = $lastRow - 2;
        $sheet->getStyle("A4:J{$borderEndRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto Size Kolom
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download File
        $filename = 'Survey_Parkir_Lengkap_' . date('Y-m-d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // --- FUNGSI PRIVATE: GENERATE PDF ---
    private function generatePdf($groupedData, $coordinator_id, $grandTotal, $stats)
    {
        // Data untuk Header PDF
        $title = 'Data Hasil Survey Potensi Parkir Pekanbaru';

        // Logic Subtitle berdasarkan Filter
        if ($stats['is_filtered']) {
            $coordName = array_key_first($groupedData);
            $subtitle  = 'Koordinator: ' . strtoupper($coordName);
        } else {
            $subtitle = 'Laporan Seluruh Koordinator';
        }

        // URL Aplikasi untuk Footer
        $app_url = BASE_URL . '/reports';

        // Siapkan data untuk View
        $data = [
            'title'       => $title,
            'subtitle'    => $subtitle,
            'date'        => date('d-m-Y H:i:s'),
            'groupedData' => $groupedData,
            'grandTotal'  => $grandTotal,
            'stats'       => $stats, // Kirim statistik header ke view
            'app_url'     => $app_url,
        ];

        // Render View ke HTML
        ob_start();
        $this->view('reports/pdf_view', $data);
        $html = ob_get_clean();

        // Setup Dompdf
        $options = new Options();
        $options->set('isPhpEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Ijinkan load gambar/font eksternal jika perlu

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('F4', 'landscape'); // Kertas F4 Landscape
        $dompdf->render();

        // Stream PDF (Preview di browser)
        $dompdf->stream("Laporan_Survey_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
        exit;
    }
}
