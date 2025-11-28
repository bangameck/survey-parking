<?php
class TeaminputController extends Controller
{

    public function __construct()
    {
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'team') {
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $data['title'] = 'Input Setoran Tim';
        $takeoverModel = $this->model('PksTakeover');

        $userTeam                      = $_SESSION['user_team'] ?? '';
        $data['assigned_coordinators'] = $takeoverModel->getByTeam($userTeam);
        $data['csrf_token']            = $this->generateCsrf();

        $this->view('layouts/header', $data);
        $this->view('takeover/team_input', $data);
        $this->view('layouts/footer');
    }

    // API JSON SUPER LENGKAP
    public function getLocationsJson()
    {
        header('Content-Type: application/json');

        $coordId    = $_GET['coord_id'] ?? null;
        $date       = $_GET['date'] ?? date('Y-m-d');
        $takeoverId = $_GET['takeover_id'] ?? null;

        if (! $coordId) {
            echo json_encode(['locations' => [], 'expenses' => []]);
            exit;
        }

        $takeoverModel = $this->model('PksTakeover');
        $depositModel  = $this->model('TakeoverDeposit');
        $expenseModel  = $this->model('TakeoverExpense'); // Load Model Baru
        $db            = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

        // 1. QUERY LOKASI (Sama seperti sebelumnya)
        $query = "SELECT pl.*,
                         pd.daily_deposits, pd.weekend_deposits, pd.monthly_deposits
                  FROM parking_locations pl
                  LEFT JOIN parking_deposits pd ON pl.id = pd.parking_location_id
                  WHERE pl.field_coordinator_id = :cid
                  ORDER BY pl.parking_location ASC";

        $stmt = $db->prepare($query);
        $stmt->execute(['cid' => $coordId]);
        $rawLocations = $stmt->fetchAll(PDO::FETCH_OBJ);

        $locations = [];
        $dayOfWeek = date('N', strtotime($date));
        $month     = date('m', strtotime($date));
        $year      = date('Y', strtotime($date));

        foreach ($rawLocations as $loc) {
            // ... (Logika Warna & Status persis seperti sebelumnya) ...
            $todayData       = $takeoverModel->getExistingDeposit($loc->id, $date);
            $hasSurvey       = false;
            $surveyAmount    = 0;
            $surveyStatusRaw = 'harian';
            $surveyLabel     = '';

            if ($loc->daily_deposits > 0) {$hasSurvey = true;
                $lockedStatus                         = 'harian';
                $surveyAmount                         = $loc->daily_deposits;
                $surveyLabel                          = 'Target Harian';} elseif ($loc->weekend_deposits > 0) {$hasSurvey = true;
                $lockedStatus                         = 'weekend';
                $surveyAmount                         = $loc->weekend_deposits;
                $surveyLabel                          = 'Target Weekend';} elseif ($loc->monthly_deposits > 0) {$hasSurvey = true;
                $lockedStatus                         = 'bulanan';
                $surveyAmount                         = $loc->monthly_deposits;
                $surveyLabel                          = 'Target Bulanan';}

            if (! $hasSurvey) {
                $history = $depositModel->getHistoryStatus($takeoverId, $loc->id);
                if ($history) {$lockedStatus = $history->status;
                    $surveyLabel                             = 'Status Tersimpan';}
            }

            $rowColor    = 'gray';
            $finalStatus = $lockedStatus;
            if ($finalStatus) {
                if ($finalStatus === 'bulanan') {
                    $rowColor = 'orange';
                } elseif ($finalStatus === 'weekend') {
                    $rowColor = 'yellow';
                } else {
                    $rowColor = 'green';
                }

            } else {
                $finalStatus = ($dayOfWeek >= 6) ? 'weekend' : 'harian';
            }

            $monthlyPaid = $takeoverModel->checkMonthlyPayment($loc->id, $month, $year);
            $isDisabled  = false;
            if ($monthlyPaid) {
                if (! $todayData || $todayData->id != $monthlyPaid->id) {$isDisabled = true;
                    $rowColor                              = 'orange-paid';}
            }
            if ($todayData) {
                $finalStatus = $todayData->status;
            }

            $locations[] = [
                'id'                => $loc->id,
                'name'              => $loc->parking_location,
                'address'           => $loc->address,
                'zone'              => $loc->zone,
                'has_survey'        => $hasSurvey,
                'survey_amount_fmt' => ($surveyAmount > 0) ? number_format($surveyAmount, 0, ',', '.') : null,
                'survey_label'      => $surveyLabel,
                'is_locked'         => ($lockedStatus !== null),
                'color_code'        => $rowColor,
                'status'            => $finalStatus,
                'disabled'          => $isDisabled,
                'amount'            => $todayData ? number_format($todayData->amount, 0, ',', '.') : '',
                'notes'             => $todayData ? $todayData->notes : '',
            ];
        }

        // 2. QUERY PENGELUARAN (DATA BARU)
        $expensesRaw = $expenseModel->getByDate($takeoverId, $date);
        $expenses    = [];
        foreach ($expensesRaw as $exp) {
            $expenses[] = [
                'description' => $exp->description,
                'amount'      => number_format($exp->amount, 0, ',', '.'),
            ];
        }
        // Jika kosong, kirim satu baris kosong untuk form
        if (empty($expenses)) {
            $expenses[] = ['description' => '', 'amount' => ''];
        }

        echo json_encode([
            'locations' => $locations,
            'expenses'  => $expenses, // Kirim data pengeluaran ke view
        ]);
        exit;
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('Token Invalid');
            }

            $depositModel = $this->model('TakeoverDeposit');
            $expenseModel = $this->model('TakeoverExpense');

            $deposits   = $_POST['deposits'] ?? [];
            $expenses   = $_POST['expenses'] ?? []; // Data Pengeluaran
            $date       = $_POST['date'];
            $takeoverId = $_POST['takeover_id'];

            // 1. Simpan Setoran
            foreach ($deposits as $locId => $data) {
                $cleanAmount = isset($data['amount']) ? str_replace('.', '', $data['amount']) : 0;
                if ($cleanAmount != '' || ! empty($data['notes'])) {
                    $depositModel->upsert([
                        'takeover_id' => $takeoverId,
                        'location_id' => $locId,
                        'user_id'     => $_SESSION['user_id'],
                        'date'        => $date,
                        'amount'      => $cleanAmount ?: 0,
                        'status'      => $data['status'],
                        'notes'       => $data['notes'],
                    ]);
                }
            }

            // 2. Simpan Pengeluaran
            $expenseModel->saveBatch($takeoverId, $_SESSION['user_id'], $date, $expenses);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data setoran & pengeluaran berhasil disimpan!'];
            $this->redirect('teaminput');
        }
    }

}
