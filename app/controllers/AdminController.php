<?php

use Dompdf\Dompdf;

class AdminController extends Controller
{
    public function __construct()
    {
        // Middleware: Pastikan user adalah admin yang sudah login
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak. Silakan login sebagai Admin.'];
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $data['title']    = 'Admin Dashboard';
        $data['username'] = $_SESSION['username'];

        // Model yang akan digunakan
        $locationModel    = $this->model('ParkingLocation');
        $coordinatorModel = $this->model('FieldCoordinator');
        $depositModel     = $this->model('ParkingDeposit');

        // Ambil data statistik dasar
        $total_locations        = $locationModel->getTotalCount();
        $surveyed_locations     = $depositModel->getSurveyedLocationsCount();
        $not_surveyed_locations = $total_locations - $surveyed_locations;

        // Ambil data total semua setoran
        $all_deposits = $depositModel->getTotalAllDeposits();
        // Hitung total keseluruhan
        $grand_total = ($all_deposits->total_daily ?? 0) + ($all_deposits->total_weekend ?? 0) + ($all_deposits->total_monthly ?? 0);

        // Siapkan semua data untuk dikirim ke view
        $data['total_locations']          = $total_locations;
        $data['total_coordinators']       = count($coordinatorModel->getAll());
        $data['total_surveyed_locations'] = $surveyed_locations;
        $data['grand_total_deposits']     = $grand_total; // Data BARU untuk kartu statistik

        // Data untuk chart
        $data['chart_data'] = [
            'surveyed'     => $surveyed_locations,
            'not_surveyed' => $not_surveyed_locations,
        ];

        $this->view('layouts/header', $data);
        $this->view('admin/dashboard', $data);
        $this->view('layouts/footer');
    }

    // FUNGSI BARU UNTUK EXPORT PDF
    public function export_pdf()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['chart_image'])) {
            die('Akses tidak sah atau data chart tidak ada.');
        }
        $data['chart_image_base64'] = $_POST['chart_image'];

        $locationModel    = $this->model('ParkingLocation');
        $coordinatorModel = $this->model('FieldCoordinator');
        $depositModel     = $this->model('ParkingDeposit');

        $total_locations        = $locationModel->getTotalCount();
        $surveyed_locations     = $depositModel->getSurveyedLocationsCount();
        $not_surveyed_locations = $total_locations - $surveyed_locations;

        // Ambil data rincian deposit
        $all_deposits = $depositModel->getTotalAllDeposits();

        $data['title']                    = 'Laporan Dashboard Admin';
        $data['total_locations']          = $total_locations;
        $data['total_coordinators']       = count($coordinatorModel->getAll());
        $data['total_surveyed_locations'] = $surveyed_locations;

        // Kirim rincian deposit ke View PDF
        $data['deposits'] = [
            'daily'   => $all_deposits->total_daily ?? 0,
            'weekend' => $all_deposits->total_weekend ?? 0,
            'monthly' => $all_deposits->total_monthly ?? 0,
        ];

        $data['chart_data'] = [
            'surveyed'     => $surveyed_locations,
            'not_surveyed' => $not_surveyed_locations,
        ];

        ob_start();
        $this->view('admin/pdf_template', $data);
        $html = ob_get_clean();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = "laporan-dashboard-" . date('Y-m-d') . ".pdf";
        $dompdf->stream($fileName, ["Attachment" => false]);
        exit();
    }
}
