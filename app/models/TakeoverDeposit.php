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
}
