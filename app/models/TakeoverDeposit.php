<?php
class TakeoverDeposit
{
    private $db;
    private $table = 'takeover_deposits';

    public function __construct()
    {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    }

    public function upsert($data)
    {
        // Cek dulu apakah data sudah ada di tanggal & lokasi ini
        $check     = "SELECT id FROM {$this->table} WHERE parking_location_id = :loc_id AND deposit_date = :date";
        $stmtCheck = $this->db->prepare($check);
        $stmtCheck->execute(['loc_id' => $data['location_id'], 'date' => $data['date']]);
        $existing = $stmtCheck->fetch(PDO::FETCH_OBJ);

        if ($existing) {
            // Update
            $query = "UPDATE {$this->table} SET amount=:amt, status=:st, notes=:notes, user_id=:uid WHERE id=:id";
            $stmt  = $this->db->prepare($query);
            return $stmt->execute([
                'amt' => $data['amount'], 'st'  => $data['status'], 'notes' => $data['notes'],
                'uid' => $data['user_id'], 'id' => $existing->id,
            ]);
        } else {
            // Insert
            $query = "INSERT INTO {$this->table} (takeover_id, parking_location_id, user_id, deposit_date, amount, status, notes)
                      VALUES (:tid, :loc_id, :uid, :date, :amt, :st, :notes)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'tid'  => $data['takeover_id'], 'loc_id' => $data['location_id'], 'uid' => $data['user_id'],
                'date' => $data['date'], 'amt'           => $data['amount'], 'st'       => $data['status'], 'notes' => $data['notes'],
            ]);
        }
    }

    public function getHistoryStatus($takeover_id, $location_id)
    {
        $query = "SELECT status FROM {$this->table}
                  WHERE takeover_id = :tid AND parking_location_id = :lid
                  ORDER BY id ASC LIMIT 1"; // Ambil yang pertama kali diinput
        $stmt = $this->db->prepare($query);
        $stmt->execute(['tid' => $takeover_id, 'lid' => $location_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // 1. Ambil Ringkasan Statistik Keuangan (Hari Ini, Bulan Ini, Tahun Ini, Total)
    public function getFinancialStats()
    {
        $today = date('Y-m-d');
        $month = date('m');
        $year  = date('Y');

        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN deposit_date = '$today' THEN amount ELSE 0 END), 0) as today,
                    COALESCE(SUM(CASE WHEN MONTH(deposit_date) = '$month' AND YEAR(deposit_date) = '$year' THEN amount ELSE 0 END), 0) as this_month,
                    COALESCE(SUM(CASE WHEN YEAR(deposit_date) = '$year' THEN amount ELSE 0 END), 0) as this_year,
                    COALESCE(SUM(amount), 0) as total_all_time
                FROM {$this->table}";

        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // 2. Ambil Data Chart (7 Hari Terakhir)
    public function getIncomeChartData()
    {
        $labels = [];
        $data   = [];

        for ($i = 6; $i >= 0; $i--) {
            $date     = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date));

            $sql  = "SELECT COALESCE(SUM(amount), 0) as total FROM {$this->table} WHERE deposit_date = :date";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['date' => $date]);
            $data[] = $stmt->fetch(PDO::FETCH_OBJ)->total;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    // 3. Ambil Performa Tim Bulan Ini (Ranking)
    public function getTeamPerformanceThisMonth()
    {
        $month = date('m');
        $year  = date('Y');

        $sql = "SELECT
                    pt.team_name,
                    COUNT(DISTINCT td.parking_location_id) as active_locations,
                    SUM(td.amount) as total_revenue
                FROM {$this->table} td
                JOIN pks_takeovers pt ON td.takeover_id = pt.id
                WHERE MONTH(td.deposit_date) = :m AND YEAR(td.deposit_date) = :y
                GROUP BY pt.team_name
                ORDER BY total_revenue DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['m' => $month, 'y' => $year]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
