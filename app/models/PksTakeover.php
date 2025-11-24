<?php
class PksTakeover
{
    private $db;
    private $table = 'pks_takeovers';

    public function __construct()
    {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (field_coordinator_id, team_name, start_date) VALUES (:fc_id, :team, :date)";
        $stmt  = $this->db->prepare($query);
        return $stmt->execute([
            'fc_id' => $data['field_coordinator_id'],
            'team'  => $data['team_name'],
            'date'  => $data['start_date'],
        ]);
    }

    // Ambil takeover berdasarkan nama tim (untuk dropdown user)
    public function getByTeam($team_name)
    {
        $query = "SELECT t.*, fc.name as coordinator_name
                  FROM {$this->table} t
                  JOIN field_coordinators fc ON t.field_coordinator_id = fc.id
                  WHERE t.team_name = :team";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['team' => $team_name]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Cek apakah lokasi sudah bayar bulanan di bulan ini
    public function checkMonthlyPayment($location_id, $month, $year)
    {
        $query = "SELECT * FROM takeover_deposits
                  WHERE parking_location_id = :loc_id
                  AND status = 'bulanan'
                  AND MONTH(deposit_date) = :month
                  AND YEAR(deposit_date) = :year";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['loc_id' => $location_id, 'month' => $month, 'year' => $year]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Ambil data setoran yang sudah ada di tanggal tertentu (untuk edit/view)
    public function getExistingDeposit($location_id, $date)
    {
        $query = "SELECT * FROM takeover_deposits WHERE parking_location_id = :loc_id AND deposit_date = :date";
        $stmt  = $this->db->prepare($query);
        $stmt->execute(['loc_id' => $location_id, 'date' => $date]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getAllWithDetails()
    {
        $query = "SELECT t.*, fc.name as coordinator_name
                  FROM {$this->table} t
                  JOIN field_coordinators fc ON t.field_coordinator_id = fc.id
                  ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTeamDashboardStats($team_name)
    {
        // 1. Ambil ID Takeover dan ID Koordinator milik tim ini
        $queryTakeover = "SELECT id, field_coordinator_id FROM {$this->table} WHERE team_name = :team";
        $stmt          = $this->db->prepare($queryTakeover);
        $stmt->execute(['team' => $team_name]);
        $takeovers = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (empty($takeovers)) {
            return [
                'total_locations'     => 0,
                'today_income'        => 0,
                'monthly_achievement' => 0,
                'chart_labels'        => [],
                'chart_target'        => [],
                'chart_real'          => [],
            ];
        }

        $takeoverIds = array_column($takeovers, 'id');
        $coordIds    = array_column($takeovers, 'field_coordinator_id');

        // String untuk IN clause SQL
        $inTakeover = implode(',', $takeoverIds);
        $inCoord    = implode(',', $coordIds);

        // 2. Hitung Total Lokasi yang Dikelola
        $queryLoc       = "SELECT COUNT(*) as total FROM parking_locations WHERE field_coordinator_id IN ($inCoord)";
        $stmtLoc        = $this->db->query($queryLoc);
        $totalLocations = $stmtLoc->fetch(PDO::FETCH_OBJ)->total;

        // 3. Hitung Pendapatan HARI INI (Real)
        $today      = date('Y-m-d');
        $queryToday = "SELECT SUM(amount) as total FROM takeover_deposits
                       WHERE takeover_id IN ($inTakeover) AND deposit_date = '$today'";
        $stmtToday   = $this->db->query($queryToday);
        $todayIncome = $stmtToday->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // 4. Hitung Target Survey Harian (Total semua lokasi di bawah tim ini)
        // Kita asumsikan target harian = penjumlahan daily_deposits dari data survey
        $queryTarget = "SELECT SUM(pd.daily_deposits) as total_target
                        FROM parking_locations pl
                        JOIN parking_deposits pd ON pl.id = pd.parking_location_id
                        WHERE pl.field_coordinator_id IN ($inCoord)";
        $stmtTarget  = $this->db->query($queryTarget);
        $dailyTarget = $stmtTarget->fetch(PDO::FETCH_OBJ)->total_target ?? 0;

        // 5. Hitung Persentase Capaian Bulan Ini
        // (Real Setoran Bulan Ini / (Target Harian * Hari Berjalan)) * 100
        $currentMonth   = date('m');
        $queryMonthReal = "SELECT SUM(amount) as total FROM takeover_deposits
                           WHERE takeover_id IN ($inTakeover) AND MONTH(deposit_date) = '$currentMonth'";
        $stmtMonth = $this->db->query($queryMonthReal);
        $monthReal = $stmtMonth->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // Estimasi target sampai hari ini (sederhana)
        $daysPassed            = date('d');
        $monthTargetProjection = $dailyTarget * $daysPassed;

        $achievement = ($monthTargetProjection > 0) ? ($monthReal / $monthTargetProjection) * 100 : 0;

        // 6. Data Chart (7 Hari Terakhir)
        $chartLabels = [];
        $chartTarget = [];
        $chartReal   = [];

        for ($i = 6; $i >= 0; $i--) {
            $dateLoop      = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = date('d M', strtotime($dateLoop));

            // Target Harian (Flat dari data survey)
            $chartTarget[] = $dailyTarget;

            // Realisasi Harian
            $queryDailyReal = "SELECT SUM(amount) as total FROM takeover_deposits
                               WHERE takeover_id IN ($inTakeover) AND deposit_date = '$dateLoop'";
            $stmtDailyReal = $this->db->query($queryDailyReal);
            $real          = $stmtDailyReal->fetch(PDO::FETCH_OBJ)->total ?? 0;
            $chartReal[]   = $real;
        }

        return [
            'total_locations'     => $totalLocations,
            'today_income'        => $todayIncome,
            'monthly_achievement' => round($achievement, 1),
            'chart_labels'        => $chartLabels,
            'chart_target'        => $chartTarget,
            'chart_real'          => $chartReal,
        ];
    }

    public function getTeamDepositHistory($userId, $startDate, $endDate)
    {
        $query = "SELECT td.*, pl.parking_location, pl.address, pt.team_name, fc.name as coordinator_name
                  FROM takeover_deposits td
                  JOIN parking_locations pl ON td.parking_location_id = pl.id
                  JOIN pks_takeovers pt ON td.takeover_id = pt.id
                  JOIN field_coordinators fc ON pt.field_coordinator_id = fc.id
                  WHERE td.user_id = :uid
                  AND td.deposit_date BETWEEN :start AND :end
                  ORDER BY td.deposit_date DESC, td.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'uid'   => $userId,
            'start' => $startDate,
            'end'   => $endDate,
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getDailyReport($teamName, $date, $coordId = null)
    {
        $sql = "SELECT
                    pl.id, pl.parking_location, pl.address,
                    fc.name as coordinator_name,

                    -- Data Setoran HARI INI (Specific Date)
                    td.amount, td.status, td.notes, td.deposit_date,

                    -- Ambil Data Target Survey
                    survey.daily_deposits, survey.weekend_deposits, survey.monthly_deposits,

                    -- CEK LOGIKA PEMBAYARAN (Subqueries)
                    (SELECT COUNT(*) FROM takeover_deposits td_month
                     WHERE td_month.parking_location_id = pl.id
                     AND td_month.takeover_id = pt.id
                     AND td_month.status = 'bulanan'
                     AND MONTH(td_month.deposit_date) = MONTH(:date1)
                     AND YEAR(td_month.deposit_date) = YEAR(:date2)
                    ) as is_paid_monthly,

                    (SELECT COUNT(*) FROM takeover_deposits td_week
                     WHERE td_week.parking_location_id = pl.id
                     AND td_week.takeover_id = pt.id
                     AND td_week.status = 'weekend'
                     AND YEARWEEK(td_week.deposit_date, 1) = YEARWEEK(:date3, 1)
                    ) as is_paid_weekly

                FROM parking_locations pl
                JOIN pks_takeovers pt ON pl.field_coordinator_id = pt.field_coordinator_id
                JOIN field_coordinators fc ON pl.field_coordinator_id = fc.id
                LEFT JOIN parking_deposits survey ON pl.id = survey.parking_location_id
                LEFT JOIN takeover_deposits td ON pl.id = td.parking_location_id
                                              AND td.deposit_date = :date4
                                              AND td.takeover_id = pt.id
                WHERE pt.team_name = :team";

        // Parameter date kita bind beberapa kali karena dipakai di subquery
        $params = [
            'team'  => $teamName,
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
        ];

        if ($coordId) {
            $sql .= " AND pl.field_coordinator_id = :cid";
            $params['cid'] = $coordId;
        }

        $sql .= " ORDER BY pl.parking_location ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // LAPORAN BULANAN (Updated: Join Survey Data)
    public function getMonthlyReport($teamName, $month, $year, $coordId = null)
    {
        $sql = "SELECT
                    pl.id, pl.parking_location, pl.address,
                    fc.name as coordinator_name,
                    COALESCE(SUM(td.amount), 0) as total_amount,
                    COUNT(td.id) as total_trx,

                    -- FIX: Gunakan MAX() agar lolos validasi ONLY_FULL_GROUP_BY
                    MAX(survey.daily_deposits) as daily_deposits,
                    MAX(survey.weekend_deposits) as weekend_deposits,
                    MAX(survey.monthly_deposits) as monthly_deposits

                FROM parking_locations pl
                JOIN pks_takeovers pt ON pl.field_coordinator_id = pt.field_coordinator_id
                JOIN field_coordinators fc ON pl.field_coordinator_id = fc.id
                LEFT JOIN parking_deposits survey ON pl.id = survey.parking_location_id
                LEFT JOIN takeover_deposits td ON pl.id = td.parking_location_id
                                              AND MONTH(td.deposit_date) = :m
                                              AND YEAR(td.deposit_date) = :y
                                              AND td.takeover_id = pt.id
                WHERE pt.team_name = :team";

        $params = ['team' => $teamName, 'm' => $month, 'y' => $year];

        if ($coordId) {
            $sql .= " AND pl.field_coordinator_id = :cid";
            $params['cid'] = $coordId;
        }

        // Grouping tetap berdasarkan ID lokasi
        $sql .= " GROUP BY pl.id ORDER BY pl.parking_location ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
