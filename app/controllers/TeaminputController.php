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

        if (! $coordId) {echo json_encode([]);exit;}

        $takeoverModel = $this->model('PksTakeover');
        $depositModel  = $this->model('TakeoverDeposit');
        $db            = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

        // 1. Ambil Lokasi + Data Survey (Left Join)
        $query = "SELECT pl.*,
                         pd.daily_deposits, pd.weekend_deposits, pd.monthly_deposits
                  FROM parking_locations pl
                  LEFT JOIN parking_deposits pd ON pl.id = pd.parking_location_id
                  WHERE pl.field_coordinator_id = :cid
                  ORDER BY pl.parking_location ASC";

        $stmt = $db->prepare($query);
        $stmt->execute(['cid' => $coordId]);
        $locations = $stmt->fetchAll(PDO::FETCH_OBJ);

        $result    = [];
        $dayOfWeek = date('N', strtotime($date)); // 1-7
        $month     = date('m', strtotime($date));
        $year      = date('Y', strtotime($date));

        foreach ($locations as $loc) {
            // --- A. LOGIKA STATUS SURVEY (Prioritas: Harian > Weekend > Bulanan) ---
            $hasSurvey    = false;
            $surveyAmount = 0;
            $lockedStatus = null; // Status terkunci dari sistem
            $surveyLabel  = '';

            if ($loc->daily_deposits > 0) {
                // Jika ada Harian (walaupun ada Weekend), TETAP HARIAN
                $hasSurvey    = true;
                $lockedStatus = 'harian';
                $surveyAmount = $loc->daily_deposits;
                $surveyLabel  = 'Target Harian';
            } elseif ($loc->weekend_deposits > 0) {
                $hasSurvey    = true;
                $lockedStatus = 'weekend';
                $surveyAmount = $loc->weekend_deposits;
                $surveyLabel  = 'Target Weekend';
            } elseif ($loc->monthly_deposits > 0) {
                $hasSurvey    = true;
                $lockedStatus = 'bulanan';
                $surveyAmount = $loc->monthly_deposits;
                $surveyLabel  = 'Target Bulanan';
            }

            // --- B. LOGIKA HISTORY INPUT (Jika Survey Kosong) ---
            if (! $hasSurvey) {
                // Cek apakah tim ini SUDAH PERNAH menentukan status untuk lokasi ini sebelumnya?
                $history = $depositModel->getHistoryStatus($takeoverId, $loc->id);
                if ($history) {
                    $lockedStatus = $history->status;   // Kunci status sesuai pilihan pertama mereka
                    $surveyLabel  = 'Status Tersimpan'; // Penanda visual
                }
            }

                                   // --- C. LOGIKA WARNA & DEFAULT ---
            $rowColor    = 'gray'; // Default (belum disurvey/input)
            $finalStatus = $lockedStatus;

            // Jika status sudah terkunci (dari Survey atau History)
            if ($finalStatus) {
                if ($finalStatus === 'bulanan') {
                    $rowColor = 'orange';
                } elseif ($finalStatus === 'weekend') {
                    $rowColor = 'yellow';
                } else {
                    $rowColor = 'green';
                }

            } else {
                // Jika masih bebas pilih (Select), defaultkan berdasarkan hari
                $finalStatus = ($dayOfWeek >= 6) ? 'weekend' : 'harian';
            }

            // --- D. CEK DATA HARI INI (Existing Input) ---
            $todayData  = $takeoverModel->getExistingDeposit($loc->id, $date);
            $isDisabled = false;

            // --- E. CEK PEMBAYARAN BULANAN (Khusus status Bulanan) ---
            $monthlyPaid = $takeoverModel->checkMonthlyPayment($loc->id, $month, $year);
            if ($monthlyPaid) {
                // Jika sudah bayar bulanan, disabled KECUALI ini adalah record pembayarannya
                if (! $todayData || $todayData->id != $monthlyPaid->id) {
                    $isDisabled = true;
                    $rowColor   = 'orange-paid'; // Visual beda
                }
            }

            // Jika ada data hari ini, status harus ikut data tsb
            if ($todayData) {
                $finalStatus = $todayData->status;
            }

            $result[] = [
                'id'                => $loc->id,
                'name'              => $loc->parking_location,
                'address'           => $loc->address,

                // Info Survey/History
                'has_survey'        => $hasSurvey,
                'survey_amount_fmt' => ($surveyAmount > 0) ? number_format($surveyAmount, 0, ',', '.') : null,
                'survey_label'      => $surveyLabel,
                'is_locked'         => ($lockedStatus !== null), // True jika status dikunci sistem

                // Tampilan
                'color_code'        => $rowColor,
                'status'            => $finalStatus,
                'disabled'          => $isDisabled,

                // Data Value
                'amount'            => $todayData ? number_format($todayData->amount, 0, ',', '.') : '',
                'notes'             => $todayData ? $todayData->notes : '',
            ];
        }

        echo json_encode($result);
        exit;
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {die('Token Invalid');}

            $depositModel = $this->model('TakeoverDeposit');
            $deposits     = $_POST['deposits'];
            $date         = $_POST['date'];
            $takeoverId   = $_POST['takeover_id'];

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
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Setoran berhasil disimpan!'];
            $this->redirect('teaminput');
        }
    }
}
