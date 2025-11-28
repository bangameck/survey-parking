<?php
class TakeoverExpense
{
    private $db;
    private $table = 'takeover_expenses';

    public function __construct()
    {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    }

    // Ambil pengeluaran berdasarkan takeover & tanggal
    public function getByDate($takeover_id, $date)
    {
        $query = "SELECT * FROM {$this->table} WHERE takeover_id = :tid AND expense_date = :date";
        $stmt  = $this->db->prepare($query);
        $stmt->execute(['tid' => $takeover_id, 'date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Simpan batch (Hapus lama, Insert baru)
    public function saveBatch($takeover_id, $user_id, $date, $expenses)
    {
        try {
            // 1. Hapus data lama di tanggal ini (Reset)
            $delSql  = "DELETE FROM {$this->table} WHERE takeover_id = :tid AND expense_date = :date";
            $stmtDel = $this->db->prepare($delSql);
            $stmtDel->execute(['tid' => $takeover_id, 'date' => $date]);

            // 2. Insert data baru (jika ada)
            if (! empty($expenses)) {
                $insSql  = "INSERT INTO {$this->table} (takeover_id, user_id, expense_date, description, amount) VALUES (:tid, :uid, :date, :desc, :amt)";
                $stmtIns = $this->db->prepare($insSql);

                foreach ($expenses as $exp) {
                    // Bersihkan format rupiah
                    $amount = str_replace('.', '', $exp['amount']);
                    if ($amount > 0 && ! empty($exp['description'])) {
                        $stmtIns->execute([
                            'tid'  => $takeover_id,
                            'uid'  => $user_id,
                            'date' => $date,
                            'desc' => $exp['description'],
                            'amt'  => $amount,
                        ]);
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
