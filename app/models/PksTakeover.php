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

    // ====================================================================
    // 1. MANAJEMEN TAKEOVER (ASSIGN TIM)
    // ====================================================================

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

    // Ambil daftar takeover milik satu tim spesifik (Untuk Dropdown Team Input)
    public function getByTeam($team_name)
    {
        $query = "SELECT t.*, fc.name as coordinator_name, fc.pks_expired
                  FROM {$this->table} t
                  JOIN field_coordinators fc ON t.field_coordinator_id = fc.id
                  WHERE t.team_name = :team
                  ORDER BY fc.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['team' => $team_name]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Ambil SEMUA data takeover (Untuk Tabel Monitoring Admin)
    public function getAllWithDetails()
    {
        $query = "SELECT t.*, fc.name as coordinator_name
                  FROM {$this->table} t
                  JOIN field_coordinators fc ON t.field_coordinator_id = fc.id
                  ORDER BY fc.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // ====================================================================
    // 2. VALIDASI & STATUS SETORAN
    // ====================================================================

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

    public function getExistingDeposit($location_id, $date)
    {
        $query = "SELECT * FROM takeover_deposits WHERE parking_location_id = :loc_id AND deposit_date = :date";
        $stmt  = $this->db->prepare($query);
        $stmt->execute(['loc_id' => $location_id, 'date' => $date]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // ====================================================================
    // 3. LAPORAN OPERASIONAL (HARIAN & BULANAN)
    // ====================================================================

    public function getDailyReport($teamName, $date, $coordId = null)
    {
        $sql = "SELECT
                    pl.id, pl.parking_location, pl.address, pl.zone,
                    fc.name as coordinator_name, fc.nik, fc.phone_number,

                    -- Data Transaksi Harian
                    td.amount, td.status, td.notes, td.deposit_date,

                    -- Data Target Survey
                    survey.daily_deposits, survey.weekend_deposits, survey.monthly_deposits,

                    -- Subquery Cek Status Lunas
                    (SELECT COUNT(*) FROM takeover_deposits td_month
                     WHERE td_month.parking_location_id = pl.id
                     AND td_month.status = 'bulanan'
                     AND MONTH(td_month.deposit_date) = MONTH(:date1)
                     AND YEAR(td_month.deposit_date) = YEAR(:date2)
                    ) as is_paid_monthly,

                    (SELECT COUNT(*) FROM takeover_deposits td_week
                     WHERE td_week.parking_location_id = pl.id
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
                WHERE 1=1";

        $params = ['date1' => $date, 'date2' => $date, 'date3' => $date, 'date4' => $date];

        if ($teamName) {
            $sql .= " AND pt.team_name = :team";
            $params['team'] = $teamName;
        }

        if ($coordId) {
            $sql .= " AND pl.field_coordinator_id = :cid";
            $params['cid'] = $coordId;
        }

        $sql .= " ORDER BY fc.name ASC, pl.address ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getMonthlyReport($teamName, $month, $year, $coordId = null)
    {
        // Menggunakan MAX() pada kolom target survey untuk mengatasi ONLY_FULL_GROUP_BY
        $sql = "SELECT
                    pl.id, pl.parking_location, pl.address, pl.zone,
                    fc.name as coordinator_name, fc.nik, fc.phone_number,

                    COALESCE(SUM(td.amount), 0) as total_amount,
                    COUNT(td.id) as total_trx,

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
                WHERE 1=1";

        $params = ['m' => $month, 'y' => $year];

        if ($teamName) {
            $sql .= " AND pt.team_name = :team";
            $params['team'] = $teamName;
        }

        if ($coordId) {
            $sql .= " AND pl.field_coordinator_id = :cid";
            $params['cid'] = $coordId;
        }

        $sql .= " GROUP BY pl.id ORDER BY fc.name ASC, pl.address ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getMonthlyDepositsRaw($teamName, $month, $year, $coordId = null)
    {
        // 1. Ambil Daftar Lokasi
        $sqlLoc = "SELECT pl.id, pl.parking_location, pl.address, pl.zone,
                          fc.name as coordinator_name, fc.nik, fc.phone_number,
                          pd.daily_deposits, pd.weekend_deposits, pd.monthly_deposits
                   FROM parking_locations pl
                   JOIN pks_takeovers pt ON pl.field_coordinator_id = pt.field_coordinator_id
                   JOIN field_coordinators fc ON pl.field_coordinator_id = fc.id
                   LEFT JOIN parking_deposits pd ON pl.id = pd.parking_location_id
                   WHERE 1=1";

        $paramsLoc = [];
        if ($teamName) {
            $sqlLoc .= " AND pt.team_name = :team";
            $paramsLoc['team'] = $teamName;
        }
        if ($coordId) {
            $sqlLoc .= " AND pl.field_coordinator_id = :cid";
            $paramsLoc['cid'] = $coordId;
        }
        $sqlLoc .= " ORDER BY fc.name ASC, pl.address ASC";

        $stmtLoc = $this->db->prepare($sqlLoc);
        $stmtLoc->execute($paramsLoc);
        $locations = $stmtLoc->fetchAll(PDO::FETCH_OBJ);

        // 2. Ambil Transaksi (Raw Data)
        $sqlTrx = "SELECT td.*, DAY(td.deposit_date) as day_num
                   FROM takeover_deposits td
                   JOIN pks_takeovers pt ON td.takeover_id = pt.id
                   WHERE MONTH(td.deposit_date) = :m AND YEAR(td.deposit_date) = :y";

        $paramsTrx = ['m' => $month, 'y' => $year];
        if ($teamName) {
            $sqlTrx .= " AND pt.team_name = :team";
            $paramsTrx['team'] = $teamName;
        }

        $stmtTrx = $this->db->prepare($sqlTrx);
        $stmtTrx->execute($paramsTrx);
        $transactions = $stmtTrx->fetchAll(PDO::FETCH_OBJ);

        // 3. Mapping Transaksi ke Lokasi
        foreach ($locations as $loc) {
            $loc->deposits = [];
            foreach ($transactions as $trx) {
                if ($trx->parking_location_id == $loc->id) {
                    $loc->deposits[$trx->day_num] = $trx;
                }
            }
        }
        return $locations;
    }

    // ====================================================================
    // 4. LAPORAN PENGELUARAN
    // ====================================================================

    public function getExpensesDaily($teamName, $date, $coordId = null)
    {
        $sql = "SELECT te.*, u.username, fc.name as coordinator_name
                FROM takeover_expenses te
                JOIN pks_takeovers pt ON te.takeover_id = pt.id
                JOIN field_coordinators fc ON pt.field_coordinator_id = fc.id
                JOIN users u ON te.user_id = u.id
                WHERE te.expense_date = :date";

        $params = ['date' => $date];

        if ($teamName) {
            $sql .= " AND pt.team_name = :team";
            $params['team'] = $teamName;
        }

        if ($coordId) {
            $sql .= " AND pt.field_coordinator_id = :cid";
            $params['cid'] = $coordId;
        }

        $sql .= " ORDER BY fc.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getExpensesMonthly($teamName, $month, $year, $coordId = null)
    {
        // Menampilkan detail pengeluaran (tanggal, deskripsi, jumlah)
        $sql = "SELECT te.description, te.amount, te.expense_date, fc.name as coordinator_name
                FROM takeover_expenses te
                JOIN pks_takeovers pt ON te.takeover_id = pt.id
                JOIN field_coordinators fc ON pt.field_coordinator_id = fc.id
                WHERE MONTH(te.expense_date) = :m AND YEAR(te.expense_date) = :y";

        $params = ['m' => $month, 'y' => $year];

        if ($teamName) {
            $sql .= " AND pt.team_name = :team";
            $params['team'] = $teamName;
        }

        if ($coordId) {
            $sql .= " AND pt.field_coordinator_id = :cid";
            $params['cid'] = $coordId;
        }

        $sql .= " ORDER BY fc.name ASC, te.expense_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // ====================================================================
    // 5. STATISTIK DASHBOARD TIM
    // ====================================================================

    public function getTeamDashboardStats($team_name)
    {
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
        $inTakeover  = implode(',', $takeoverIds);
        $inCoord     = implode(',', $coordIds);

        $queryLoc       = "SELECT COUNT(*) as total FROM parking_locations WHERE field_coordinator_id IN ($inCoord)";
        $totalLocations = $this->db->query($queryLoc)->fetch(PDO::FETCH_OBJ)->total;

        $today      = date('Y-m-d');
        $queryToday = "SELECT SUM(amount) as total FROM takeover_deposits
                       WHERE takeover_id IN ($inTakeover) AND deposit_date = '$today'";
        $todayIncome = $this->db->query($queryToday)->fetch(PDO::FETCH_OBJ)->total ?? 0;

        $queryTarget = "SELECT SUM(pd.daily_deposits) as total_target
                        FROM parking_locations pl
                        JOIN parking_deposits pd ON pl.id = pd.parking_location_id
                        WHERE pl.field_coordinator_id IN ($inCoord)";
        $dailyTarget = $this->db->query($queryTarget)->fetch(PDO::FETCH_OBJ)->total_target ?? 0;

        $currentMonth   = date('m');
        $queryMonthReal = "SELECT SUM(amount) as total FROM takeover_deposits
                           WHERE takeover_id IN ($inTakeover) AND MONTH(deposit_date) = '$currentMonth'";
        $monthReal = $this->db->query($queryMonthReal)->fetch(PDO::FETCH_OBJ)->total ?? 0;

        $daysPassed            = date('d');
        $monthTargetProjection = $dailyTarget * $daysPassed;
        $achievement           = ($monthTargetProjection > 0) ? ($monthReal / $monthTargetProjection) * 100 : 0;

        $chartLabels = [];
        $chartTarget = [];
        $chartReal   = [];

        for ($i = 6; $i >= 0; $i--) {
            $dateLoop      = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = date('d M', strtotime($dateLoop));
            $chartTarget[] = $dailyTarget;

            $queryDailyReal = "SELECT SUM(amount) as total FROM takeover_deposits
                               WHERE takeover_id IN ($inTakeover) AND deposit_date = '$dateLoop'";
            $real        = $this->db->query($queryDailyReal)->fetch(PDO::FETCH_OBJ)->total ?? 0;
            $chartReal[] = $real;
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
}
