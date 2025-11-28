<?php

class ParkingLocation
{
    private $db;
    private $table = 'parking_locations';

    public function __construct()
    {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll()
    {
        $query = "SELECT pl.*, fc.name as coordinator_name
                  FROM {$this->table} pl
                  JOIN field_coordinators fc ON pl.field_coordinator_id = fc.id
                  ORDER BY pl.parking_location DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getById($id)
    {
        $query = "SELECT pl.*, fc.name as coordinator_name
                  FROM {$this->table} pl
                  JOIN field_coordinators fc ON pl.field_coordinator_id = fc.id
                  WHERE pl.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function create($data)
    {
        // UPDATE: Tambahkan kolom 'zone'
        $query = "INSERT INTO {$this->table} (field_coordinator_id, address, parking_location, zone)
                  VALUES (:field_coordinator_id, :address, :parking_location, :zone)";
        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':field_coordinator_id', $data['field_coordinator_id']);
        $stmt->bindValue(':address', $data['address']);
        $stmt->bindValue(':parking_location', $data['parking_location']);
        // Handle jika zone kosong
        $zone = ! empty($data['zone']) ? $data['zone'] : null;
        $stmt->bindValue(':zone', $zone);

        return $stmt->execute();
    }

    public function update($id, $data)
    {
        // UPDATE: Tambahkan kolom 'zone'
        $query = "UPDATE {$this->table}
                  SET field_coordinator_id = :field_coordinator_id,
                      address = :address,
                      parking_location = :parking_location,
                      zone = :zone
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':field_coordinator_id', $data['field_coordinator_id']);
        $stmt->bindValue(':address', $data['address']);
        $stmt->bindValue(':parking_location', $data['parking_location']);

        $zone = ! empty($data['zone']) ? $data['zone'] : null;
        $stmt->bindValue(':zone', $zone);

        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }

    // FUNGSI BARU: Bulk Update Zona
    public function updateBatch($ids, $zone)
    {
        if (empty($ids) || ! is_array($ids)) {
            return false;
        }

        // Buat placeholder (?,?,?)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Update zone untuk semua ID yang dipilih
        $query = "UPDATE {$this->table} SET zone = ? WHERE id IN ($placeholders)";

        // Gabungkan parameter: [Zona, ID1, ID2, ID3...]
        $params = array_merge([$zone], $ids);

        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteBatch($ids)
    {
        if (empty($ids) || ! is_array($ids)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query        = "DELETE FROM {$this->table} WHERE id IN ($placeholders)";
        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute($ids);
        } catch (PDOException $e) {
            return false;
        }
    }

    // UPDATE: Tambahkan 'zone' di Export
    public function getExportData($coordinator_id = null)
    {
        $sql = "SELECT
                    pl.id, pl.parking_location, pl.address, pl.zone,
                    fc.name as coordinator_name,
                    pd.daily_deposits, pd.weekend_deposits, pd.monthly_deposits,
                    pd.surveyor_1, pd.surveyor_2, pd.information,
                    pd.created_at as survey_date
                FROM {$this->table} pl
                JOIN field_coordinators fc ON pl.field_coordinator_id = fc.id
                LEFT JOIN parking_deposits pd ON pl.id = pd.parking_location_id
                WHERE 1=1";

        $params = [];
        if (! empty($coordinator_id)) {
            $sql .= " AND pl.field_coordinator_id = :cid";
            $params[':cid'] = $coordinator_id;
        }
        $sql .= " ORDER BY fc.name ASC, pl.parking_location ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Method pagination, total count, dll tetap sama tapi pastikan SELECT-nya update jika perlu
    public function getTotalCount($selected_coordinator = null, $searchTerm = null)
    {
        $sql    = "SELECT COUNT(*) as total FROM {$this->table} pl WHERE 1=1";
        $params = [];
        if ($selected_coordinator) {
            $sql .= " AND pl.field_coordinator_id = :coord_id";
            $params[':coord_id'] = $selected_coordinator;
        }
        if ($searchTerm) {
            $sql .= " AND (pl.parking_location LIKE :search OR pl.address LIKE :search)";
            $params[':search'] = '%' . $searchTerm . '%';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_OBJ)->total;
    }

    public function getPaginated($limit, $offset, $selected_coordinator = null, $searchTerm = null)
    {
        // UPDATE: Select 'zone'
        $sql = "SELECT pl.id, pl.parking_location, pl.address, pl.zone, fc.name as coordinator_name
                FROM {$this->table} pl
                JOIN field_coordinators fc ON pl.field_coordinator_id = fc.id
                WHERE 1=1";

        $params = [];
        if ($selected_coordinator) {
            $sql .= " AND pl.field_coordinator_id = :coord_id";
            $params[':coord_id'] = $selected_coordinator;
        }
        if ($searchTerm) {
            $sql .= " AND (pl.parking_location LIKE :search OR pl.address LIKE :search)";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        $sql .= " ORDER BY pl.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        if (! empty($params)) {
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }

        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Method helper lain (searchByName, dll) tetap dipertahankan jika ada di file asli
    public function searchByName($term)
    {
        $stmt = $this->db->prepare("SELECT id, parking_location as text FROM {$this->table} WHERE parking_location LIKE :term LIMIT 20");
        $stmt->execute(['term' => "%$term%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function searchAddress($term)
    {
        $stmt = $this->db->prepare("SELECT id, address as text FROM {$this->table} WHERE address LIKE :term LIMIT 20");
        $stmt->execute(['term' => "%$term%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function getByAddress($address)
    {
        $stmt = $this->db->prepare("SELECT pl.*, fc.name as coordinator_name FROM {$this->table} pl JOIN field_coordinators fc ON pl.field_coordinator_id = fc.id WHERE pl.address = :address");
        $stmt->execute(['address' => $address]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function getDetailsByCoordinatorId($coordId)
    {
        $query = "SELECT pl.*, pd.daily_deposits, pd.weekend_deposits, pd.monthly_deposits
                  FROM {$this->table} pl
                  LEFT JOIN parking_deposits pd ON pl.id = pd.parking_location_id
                  WHERE pl.field_coordinator_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $coordId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    // Support Import (createBatch) jika diperlukan di file asli
    public function createBatch($data)
    {
        if (empty($data)) {
            return false;
        }

        $query = "INSERT INTO {$this->table} (field_coordinator_id, parking_location, address, zone)
                  VALUES (:fc, :pl, :addr, :zone)";
        $stmt = $this->db->prepare($query);

        foreach ($data as $row) {
            $stmt->execute([
                'fc'   => $row['field_coordinator_id'],
                'pl'   => $row['parking_location'],
                'addr' => $row['address'],
                'zone' => ! empty($row['zone']) ? $row['zone'] : null, // Handle Zona
            ]);
        }
        return true;
    }
}
