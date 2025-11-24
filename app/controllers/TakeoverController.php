<?php
class TakeoverController extends Controller
{

    public function __construct()
    {
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $coordModel    = $this->model('FieldCoordinator');
        $takeoverModel = $this->model('PksTakeover');
        $userModel     = $this->model('User'); // Load model User

        $data['title'] = 'Pengambilalihan PKS';

        // Ambil data dropdown
        $data['coordinators']       = $coordModel->getAvailableForTakeover();
        $data['existing_teams']     = $userModel->getAllTeams(); // Data Tim yang sudah ada
        $data['existing_takeovers'] = $takeoverModel->getAllWithDetails();

        $data['csrf_token'] = $this->generateCsrf();

        $this->view('layouts/header', $data);
        $this->view('takeover/admin_assign', $data);
        $this->view('layouts/footer');
    }

    // API BARU: Ambil anggota tim (JSON)
    public function getTeamMembersJson()
    {
        header('Content-Type: application/json');
        $teamName = $_GET['team'] ?? '';

        if (empty($teamName)) {
            echo json_encode([]);
            exit;
        }

        $userModel  = $this->model('User');
        $membersRaw = $userModel->getMembersByTeam($teamName);

        // Format ulang username menjadi nama panggilan (opsional)
        // Misal: tim1_budi -> Budi
        $members = [];
        foreach ($membersRaw as $username) {
            $parts = explode('_', $username);
            // Ambil bagian setelah underscore, atau username utuh jika tidak ada underscore
            $name      = isset($parts[1]) ? ucfirst($parts[1]) : ucfirst($username);
            $members[] = $name;
        }

        echo json_encode($members);
        exit;
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1. Simpan Data Takeover (PKS) - Ini Selalu Dijalankan
            $takeoverModel = $this->model('PksTakeover');
            $takeoverData  = [
                'field_coordinator_id' => $_POST['coordinator_id'],
                'team_name'            => $_POST['team_name'],
                'start_date'           => $_POST['start_date'],
            ];
            $takeoverModel->create($takeoverData);

            // 2. Cek apakah Tim ini SUDAH ADA?
            $userModel       = $this->model('User');
            $existingMembers = $userModel->getMembersByTeam($_POST['team_name']);

            // JIKA TIM BARU (Belum ada member), maka buatkan usernya
            if (empty($existingMembers)) {
                $members = $_POST['members']; // Array nama anggota dari form
                foreach ($members as $name) {
                    if (! empty($name)) {
                        $cleanName = strtolower(str_replace(' ', '', $name));
                        $username  = $cleanName;

                        if (! $userModel->findByUsername($username)) {
                            $this->createUser($username, 'password', 'team', $_POST['team_name']);
                        }
                    }
                }
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'PKS berhasil diambil alih & User Tim Baru dibuat!'];
            } else {
                // JIKA TIM LAMA, kita hanya link-kan saja (tidak buat user baru)
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'PKS berhasil ditambahkan ke Tim ' . htmlspecialchars($_POST['team_name']) . '!'];
            }

            $this->redirect('takeover');
        }
    }

    private function createUser($username, $password, $role, $team)
    {
        $db   = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password, role, team_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $hash, $role, $team]);
    }
}
