<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class PimpinanController extends Controller
{
    public function __construct()
    {
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pimpinan') {
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $data['title']    = 'Executive Dashboard';
        $data['username'] = $_SESSION['username'];

        // --- AMBIL DATA (Logic sama dengan Export) ---
        $this->getDashboardData($data);

        $this->view('layouts/header', $data);
        $this->view('pimpinan/dashboard', $data);
        $this->view('layouts/footer');
    }

    // FUNGSI BARU: Export PDF Landscape
    public function export_pdf()
    {
        // Naikkan limit untuk render grafik
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die('Akses Ditolak');
        }

        // Tangkap gambar dari Canvas JS
        $data['chart_income'] = $_POST['income_chart'] ?? '';
        $data['chart_survey'] = $_POST['survey_chart'] ?? '';
        $data['title']        = 'Executive Report - ' . date('d F Y');

        // --- AMBIL DATA (Reuse Logic) ---
        $this->getDashboardData($data);

        // Render PDF
        ob_start();
        $this->view('pimpinan/pdf_template', $data);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isPhpEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Penting untuk gambar

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        // Set Landscape sesuai permintaan
        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();
        $dompdf->stream("Executive_Report_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
        exit();
    }

    // Helper Private: Agar codingan tidak duplikat
    private function getDashboardData(&$data)
    {
        // 1. OPERASIONAL
        $locationModel    = $this->model('ParkingLocation');
        $coordinatorModel = $this->model('FieldCoordinator');
        $surveyModel      = $this->model('ParkingDeposit');

        $data['total_locations']    = $locationModel->getTotalCount();
        $data['total_coordinators'] = count($coordinatorModel->getAll());
        $data['surveyed_locations'] = $surveyModel->getSurveyedLocationsCount();

        $potensi                 = $surveyModel->getTotalAllDeposits();
        $data['total_potential'] = ($potensi->total_daily ?? 0) + ($potensi->total_weekend ?? 0) + ($potensi->total_monthly ?? 0);

        // Data Chart Survey (Untuk view biasa, PDF pakai gambar)
        $data['survey_chart_data'] = [
            'surveyed'     => $data['surveyed_locations'],
            'not_surveyed' => $data['total_locations'] - $data['surveyed_locations'],
        ];

        // 2. FINANSIAL (Takeover)
        $realDepositModel = $this->model('TakeoverDeposit');
        $data['finance']  = $realDepositModel->getFinancialStats();

        // Data Chart Income
        $incomeChart           = $realDepositModel->getIncomeChartData();
        $data['income_labels'] = $incomeChart['labels'];
        $data['income_values'] = $incomeChart['data'];

        // Performa Tim
        $data['team_performance'] = $realDepositModel->getTeamPerformanceThisMonth();
    }
}
