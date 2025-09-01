<?php
class ParkingDeposit
{
    private $db;
    private $table = 'parking_deposits';

    public function __construct()
    {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Mengambil data deposit yang sudah ada untuk satu koordinator
    // agar form bisa diisi otomatis jika data sudah pernah diinput
    public function getDepositsByCoordinator($coordinator_id)
    {
        $query = "SELECT pd.* FROM {$this->table} pd
                  JOIN parking_locations pl ON pd.parking_location_id = pl.id
                  WHERE pl.field_coordinator_id = :coordinator_id ORDER BY pl.parking_location ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['coordinator_id' => $coordinator_id]);

        // Mengubah hasil menjadi array asosiatif dengan key parking_location_id
        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $row) {
            $results[$row->parking_location_id] = $row;
        }
        return $results;
    }

    // Fungsi utama untuk menyimpan data (UPSERT BATCH)
    public function upsertBatch($data, $surveyor1, $surveyor2, $documentPath)
    {
        $this->db->beginTransaction();
        try {
            foreach ($data as $location_id => $deposit) {
                // Ubah nilai kosong menjadi NULL
                $daily   = ! empty($deposit['daily_deposits']) ? $deposit['daily_deposits'] : null;
                $weekend = ! empty($deposit['weekend_deposits']) ? $deposit['weekend_deposits'] : null;
                $monthly = ! empty($deposit['monthly_deposits']) ? $deposit['monthly_deposits'] : null;
                $info    = ! empty($deposit['information']) ? $deposit['information'] : null;

                // Hanya proses baris ini jika setidaknya ada satu data yang diisi
                if ($daily !== null || $weekend !== null || $monthly !== null || $info !== null) {

                    $stmt_check = $this->db->prepare("SELECT id, document_survey FROM {$this->table} WHERE parking_location_id = :location_id");
                    $stmt_check->execute(['location_id' => $location_id]);
                    $existing = $stmt_check->fetch(PDO::FETCH_OBJ);

                    // Tentukan path dokumen yang akan disimpan.
                    // Jika ada file baru diupload, gunakan itu.
                    // Jika tidak, tapi sudah ada file lama, pertahankan file lama.
                    // Jika tidak ada keduanya, gunakan NULL.
                    $finalDocumentPath = $documentPath ?: ($existing->document_survey ?? null);

                    if ($existing) {
                        // Jika ada, UPDATE
                        $query = "UPDATE {$this->table} SET
                                daily_deposits = :daily, weekend_deposits = :weekend, monthly_deposits = :monthly,
                                information = :info, surveyor_1 = :s1, surveyor_2 = :s2, document_survey = :doc
                              WHERE id = :id";
                        $stmt = $this->db->prepare($query);
                        $stmt->execute([
                            'daily' => $daily, 'weekend' => $weekend, 'monthly' => $monthly,
                            'info'  => $info, 's1'       => $surveyor1, 's2'    => $surveyor2, 'doc' => $finalDocumentPath,
                            'id'    => $existing->id,
                        ]);
                    } else {
                        // Jika tidak ada, INSERT
                        $query = "INSERT INTO {$this->table}
                                (parking_location_id, daily_deposits, weekend_deposits, monthly_deposits, information, surveyor_1, surveyor_2, document_survey)
                              VALUES
                                (:loc_id, :daily, :weekend, :monthly, :info, :s1, :s2, :doc)";
                        $stmt = $this->db->prepare($query);
                        $stmt->execute([
                            'loc_id' => $location_id, 'daily' => $daily, 'weekend' => $weekend, 'monthly' => $monthly,
                            'info'   => $info, 's1'           => $surveyor1, 's2'  => $surveyor2, 'doc'   => $finalDocumentPath,
                        ]);
                    }
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            // DEBUG: Simpan pesan error asli dari database ke session untuk ditampilkan di Toastr
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'DB Error: ' . $e->getMessage()];
            return false; // Tetap return false agar alur tidak berubah
        }
    }

    public function getByLocationId($location_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE parking_location_id = :location_id");
        $stmt->execute(['location_id' => $location_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getSurveyedLocationsCount()
    {
        // COUNT(DISTINCT ...) memastikan setiap lokasi hanya dihitung sekali
        $query = "SELECT COUNT(DISTINCT parking_location_id) FROM {$this->table}";
        $stmt  = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

}
