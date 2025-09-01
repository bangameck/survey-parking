<?php
class GuestController extends Controller
{
    public function __construct()
    {
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'guest') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak.'];
            $this->redirect('auth/login');
        }
    }

    // GANTI METHOD INDEX() YANG LAMA DENGAN INI
    public function index()
    {
        $data['title']    = 'Guest Dashboard';
        $data['username'] = $_SESSION['username'];

        // Model yang akan digunakan
        $locationModel    = $this->model('ParkingLocation');
        $coordinatorModel = $this->model('FieldCoordinator');
        $depositModel     = $this->model('ParkingDeposit');

        // Ambil data statistik yang sama seperti admin
        $total_locations        = $locationModel->getTotalCount();
        $surveyed_locations     = $depositModel->getSurveyedLocationsCount();
        $not_surveyed_locations = $total_locations - $surveyed_locations;

        $data['total_locations']          = $total_locations;
        $data['total_coordinators']       = count($coordinatorModel->getAll());
        $data['total_surveyed_locations'] = $surveyed_locations;

        // Siapkan data khusus untuk chart
        $data['chart_data'] = [
            'surveyed'     => $surveyed_locations,
            'not_surveyed' => $not_surveyed_locations,
        ];

        // Gunakan layout khusus Guest
        $this->view('layouts/guest_header', $data);
        $this->view('guest/dashboard', $data);
        $this->view('layouts/guest_footer');
    }
}
