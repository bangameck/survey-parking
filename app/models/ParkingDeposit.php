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
    public function getDepositsByCoordinator($coordinator_id)
    {
        $query = "SELECT pd.* FROM {$this->table} pd
                  JOIN parking_locations pl ON pd.parking_location_id = pl.id
                  WHERE pl.field_coordinator_id = :coordinator_id ORDER BY pl.parking_location ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['coordinator_id' => $coordinator_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getByLocationId($location_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE parking_location_id = :location_id");
        $stmt->execute(['location_id' => $location_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getSurveyedLocationsCount()
    {
        $query = "SELECT COUNT(DISTINCT parking_location_id) FROM {$this->table}";
        $stmt  = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getTotalAllDeposits()
    {
        $query = "SELECT
                SUM(daily_deposits) as total_daily,
                SUM(weekend_deposits) as total_weekend,
                SUM(monthly_deposits) as total_monthly
              FROM {$this->table}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // --- FUNGSI UTAMA: SIMPAN DATA (LEBIH TANGGUH) ---
    public function upsertBatch($data, $surveyor1, $surveyor2, $documentPath)
    {
        $this->db->beginTransaction();
        try {
            $checkSql  = "SELECT id, document_survey FROM {$this->table} WHERE parking_location_id = :loc_id";
            $stmtCheck = $this->db->prepare($checkSql);

            $insertSql = "INSERT INTO {$this->table} (
                            parking_location_id, daily_deposits, weekend_deposits, monthly_deposits,
                            surveyor_1, surveyor_2, document_survey, created_at
                          ) VALUES (
                            :loc_id, :daily, :weekend, :monthly,
                            :surv1, :surv2, :doc, NOW()
                          )";
            $stmtInsert = $this->db->prepare($insertSql);

            $updateSql = "UPDATE {$this->table} SET
                            daily_deposits = :daily,
                            weekend_deposits = :weekend,
                            monthly_deposits = :monthly,
                            surveyor_1 = :surv1,
                            surveyor_2 = :surv2,
                            document_survey = :doc
                          WHERE id = :id";
            $stmtUpdate = $this->db->prepare($updateSql);

            foreach ($data as $locationId => $row) {
                $daily   = (float) str_replace('.', '', $row['daily'] ?? 0);
                $weekend = (float) str_replace('.', '', $row['weekend'] ?? 0);
                $monthly = (float) str_replace('.', '', $row['monthly'] ?? 0);

                $stmtCheck->execute(['loc_id' => $locationId]);
                $existingData = $stmtCheck->fetch(PDO::FETCH_OBJ);

                $finalDocPath = $documentPath ? $documentPath : ($existingData->document_survey ?? null);

                if ($existingData) {
                    // UPDATE
                    $stmtUpdate->execute([
                        'daily'   => $daily,
                        'weekend' => $weekend,
                        'monthly' => $monthly,
                        'surv1'   => $surveyor1,
                        'surv2'   => $surveyor2,
                        'doc'     => $finalDocPath,
                        'id'      => $existingData->id,
                    ]);
                } else {
                    // INSERT - Perbaikan logika: simpan jika ada surveyor/doc walau uang 0
                    if ($daily > 0 || $weekend > 0 || $monthly > 0 || $finalDocPath || ! empty($surveyor1) || ! empty($surveyor2)) {
                        $stmtInsert->execute([
                            'loc_id'  => $locationId,
                            'daily'   => $daily,
                            'weekend' => $weekend,
                            'monthly' => $monthly,
                            'surv1'   => $surveyor1,
                            'surv2'   => $surveyor2,
                            'doc'     => $finalDocPath,
                        ]);
                    }
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'DB Error: ' . $e->getMessage()];
            return false;
        }
    }
}
