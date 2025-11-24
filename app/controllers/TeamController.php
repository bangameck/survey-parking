<?php

use Dompdf\Dompdf;

class TeamController extends Controller
{

    public function __construct()
    {
        // Pastikan hanya role 'team' yang bisa masuk
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'team') {
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $data['title']     = 'Dashboard Tim UPT';
        $data['team_name'] = $_SESSION['user_team'] ?? 'Tim UPT';

        // Ambil data Real dari Database
        $takeoverModel = $this->model('PksTakeover');
        $stats         = $takeoverModel->getTeamDashboardStats($data['team_name']);

        // Gabungkan data stats ke variabel $data utama
        $data = array_merge($data, $stats);

        $this->view('layouts/header', $data);
        $this->view('team/dashboard', $data);
        $this->view('layouts/footer');
    }

    public function reports()
    {
        $data['title']     = 'Laporan Tim';
        $data['team_name'] = $_SESSION['user_team'];

        $takeoverModel = $this->model('PksTakeover');

        // Filter Default
        $reportType = $_GET['type'] ?? 'harian';
        $coordId    = $_GET['coord_id'] ?? '';

        // Data untuk Dropdown Koordinator (Hanya milik tim ini)
        $data['assigned_coordinators'] = $takeoverModel->getByTeam($data['team_name']);

        if ($reportType === 'harian') {
            $date                = $_GET['date'] ?? date('Y-m-d');
            $data['report_data'] = $takeoverModel->getDailyReport($data['team_name'], $date, $coordId);
            $data['date']        = $date;
        } else {
            $month               = $_GET['month'] ?? date('m');
            $year                = $_GET['year'] ?? date('Y');
            $data['report_data'] = $takeoverModel->getMonthlyReport($data['team_name'], $month, $year, $coordId);
            $data['month']       = $month;
            $data['year']        = $year;
        }

        $data['report_type']    = $reportType;
        $data['selected_coord'] = $coordId;

        $this->view('layouts/header', $data);
        $this->view('team/reports', $data);
        $this->view('layouts/footer');
    }

    // BARU: Method Export PDF
    public function export_report()
    {
        $teamName      = $_SESSION['user_team'];
        $takeoverModel = $this->model('PksTakeover');

        $reportType = $_GET['type'] ?? 'harian';
        $coordId    = $_GET['coord_id'] ?? '';

        if ($reportType === 'harian') {
            $date  = $_GET['date'] ?? date('Y-m-d');
            $data  = $takeoverModel->getDailyReport($teamName, $date, $coordId);
            $title = "Laporan Harian - " . date('d F Y', strtotime($date));
        } else {
            $month     = $_GET['month'] ?? date('m');
            $year      = $_GET['year'] ?? date('Y');
            $data      = $takeoverModel->getMonthlyReport($teamName, $month, $year, $coordId);
            $monthName = date("F", mktime(0, 0, 0, $month, 10));
            $title     = "Laporan Bulanan - $monthName $year";
        }

        // Data untuk View PDF
        $viewData = [
            'title'       => $title,
            'team_name'   => $teamName,
            'report_type' => $reportType,
            'data'        => $data,
        ];

        ob_start();
        $this->view('team/pdf_report', $viewData); // Kita buat file ini nanti
        $html = ob_get_clean();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait'); // Portrait karena kolomnya sedikit
        $dompdf->render();
        $dompdf->stream("Laporan_Tim_" . date('YmdHis') . ".pdf", ["Attachment" => false]);
    }
}
