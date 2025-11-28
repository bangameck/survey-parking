<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class TeamController extends Controller
{
    public function __construct()
    {
        // UPDATE: Izinkan user login apapun kecuali guest (karena Admin/Pimpinan boleh lihat)
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'guest') {
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        // PROTEKSI: Dashboard Team hanya untuk Role Team
        if ($_SESSION['user_role'] !== 'team') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak. Halaman ini khusus Tim Lapangan.'];

            // Redirect ke dashboard yang sesuai
            if ($_SESSION['user_role'] === 'admin') {
                $this->redirect('admin');
            } elseif ($_SESSION['user_role'] === 'pimpinan') {
                $this->redirect('pimpinan');
            } elseif ($_SESSION['user_role'] === 'bendahara') {
                $this->redirect('bendahara');
            } else {
                $this->redirect('auth/login');
            }

            return;
        }

        $data['title']     = 'Dashboard Tim UPT';
        $data['team_name'] = $_SESSION['user_team'] ?? 'Tim UPT';

        // Ambil data Real dari Database untuk Dashboard
        $takeoverModel = $this->model('PksTakeover');
        $stats         = $takeoverModel->getTeamDashboardStats($data['team_name']);

        // Gabungkan data stats ke variabel $data utama
        $data = array_merge($data, $stats);

        $this->view('layouts/header', $data);
        $this->view('team/dashboard', $data);
        $this->view('layouts/footer');
    }

    // Halaman Laporan & Riwayat
    public function reports()
    {
        $data['title'] = 'Laporan Tim';

        // Tentukan Tim mana yang akan ditampilkan
        $teamToQuery = null;
        if ($_SESSION['user_role'] === 'team') {
            $teamToQuery       = $_SESSION['user_team'];
            $data['team_name'] = $teamToQuery;
        } else {
            // Admin/Pimpinan melihat semua
            $data['team_name'] = 'Semua Tim (Monitoring)';
        }

        $takeoverModel = $this->model('PksTakeover');

        // Filter dari URL
        $reportType = $_GET['type'] ?? 'harian';
        $coordId    = $_GET['coord_id'] ?? '';

        // 1. AMBIL DATA DROPDOWN KOORDINATOR
        if ($teamToQuery) {
            $data['assigned_coordinators'] = $takeoverModel->getByTeam($teamToQuery);
        } else {
            $data['assigned_coordinators'] = $takeoverModel->getAllWithDetails();
        }

        $data['expenses'] = [];

        // 2. AMBIL DATA LAPORAN
        if ($reportType === 'harian') {
            $date                = $_GET['date'] ?? date('Y-m-d');
            $data['report_data'] = $takeoverModel->getDailyReport($teamToQuery, $date, $coordId);

            // Ambil Pengeluaran Harian (Filter Koordinator juga jika ada)
            $data['expenses'] = $takeoverModel->getExpensesDaily($teamToQuery, $date, $coordId);

            $data['date'] = $date;
        } else {
            $month = $_GET['month'] ?? date('m');
            $year  = $_GET['year'] ?? date('Y');

            // Untuk Web View, gunakan report summary (totalan)
            $data['report_data'] = $takeoverModel->getMonthlyReport($teamToQuery, $month, $year, $coordId);

            // Ambil Pengeluaran Bulanan (Filter Koordinator juga jika ada)
            $data['expenses'] = $takeoverModel->getExpensesMonthly($teamToQuery, $month, $year, $coordId);

            $data['month'] = $month;
            $data['year']  = $year;
        }

        $data['report_type']    = $reportType;
        $data['selected_coord'] = $coordId;

        $this->view('layouts/header', $data);
        $this->view('team/reports', $data);
        $this->view('layouts/footer');
    }

    // Method Export PDF
    public function export_report()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $teamName = null;
        if ($_SESSION['user_role'] === 'team') {
            $teamName = $_SESSION['user_team'];
        }

        $takeoverModel = $this->model('PksTakeover');

        $reportType = $_GET['type'] ?? 'harian';
        $coordId    = $_GET['coord_id'] ?? '';

        $data['expenses'] = [];
        $daysInMonth      = 0;

        if ($reportType === 'harian') {
            $date = $_GET['date'] ?? date('Y-m-d');

            $data['report_data'] = $takeoverModel->getDailyReport($teamName, $date, $coordId);
            $data['expenses']    = $takeoverModel->getExpensesDaily($teamName, $date, $coordId);

            $title = "Laporan Harian - " . date('d F Y', strtotime($date));
        } else {
            $month = $_GET['month'] ?? date('m');
            $year  = $_GET['year'] ?? date('Y');

            // PENTING: Gunakan fungsi RAW untuk PDF Bulanan (Breakdown tanggal 1-31)
            $data['report_data'] = $takeoverModel->getMonthlyDepositsRaw($teamName, $month, $year, $coordId);
            $data['expenses']    = $takeoverModel->getExpensesMonthly($teamName, $month, $year, $coordId);

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $monthName   = date("F", mktime(0, 0, 0, $month, 10));
            $title       = "Laporan Bulanan - $monthName $year";
        }

        // Data untuk View PDF
        $viewData = [
            'title'       => $title,
            'team_name'   => $teamName ?? 'Semua Tim',
            'report_type' => $reportType,
            'data'        => $data['report_data'],
            'expenses'    => $data['expenses'],
            'daysInMonth' => $daysInMonth ?? 0,
            'month'       => $month ?? 0,
            'year'        => $year ?? 0,
            'app_url'     => BASE_URL,
            'date'        => date('d-m-Y H:i:s'),
        ];

        ob_start();
        $this->view('team/pdf_report', $viewData);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isPhpEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        // Set ukuran kertas
        $paperSize = ($reportType == 'bulanan') ? 'Legal' : 'A4';
        $dompdf->setPaper($paperSize, 'landscape');

        $dompdf->render();
        $dompdf->stream("Laporan_Tim_" . date('YmdHis') . ".pdf", ["Attachment" => false]);
        exit;
    }
}
