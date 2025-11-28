<?php
class BendaharaController extends Controller
{
    public function __construct()
    {
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'bendahara') {
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $data['title']    = 'Dashboard Bendahara';
        $data['username'] = $_SESSION['username'];

        $depositModel = $this->model('TakeoverDeposit'); // Model Setoran Real

        // 1. Ambil Statistik Utama
        $data['stats'] = $depositModel->getFinancialStats();

        // 2. Ambil Data Chart
        $chartData            = $depositModel->getIncomeChartData();
        $data['chart_labels'] = $chartData['labels'];
        $data['chart_data']   = $chartData['data'];

        // 3. Ambil Performa Tim
        $data['team_performance'] = $depositModel->getTeamPerformanceThisMonth();

        $this->view('layouts/header', $data);
        $this->view('bendahara/dashboard', $data);
        $this->view('layouts/footer');
    }
}
