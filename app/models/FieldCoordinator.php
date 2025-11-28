<?php
class FieldCoordinator
{
    private $db;
    private $table = 'field_coordinators';

    public function __construct()
    {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function create($data)
    {
        // UPDATE: Tambahkan nik dan phone_number
        $query = "INSERT INTO {$this->table} (name, nik, phone_number, pks_expired)
                  VALUES (:name, :nik, :phone_number, :pks_expired)";
        $stmt = $this->db->prepare($query);

        $pks = ! empty($data['pks_expired']) ? $data['pks_expired'] : null;

        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':nik', $data['nik'] ?? null);
        $stmt->bindValue(':phone_number', $data['phone_number'] ?? null);
        $stmt->bindValue(':pks_expired', $pks);

        return $stmt->execute();
    }

    public function update($id, $data)
    {
        // UPDATE: Tambahkan nik dan phone_number
        $query = "UPDATE {$this->table}
                  SET name = :name,
                      nik = :nik,
                      phone_number = :phone_number,
                      pks_expired = :pks_expired
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);

        $pks = ! empty($data['pks_expired']) ? $data['pks_expired'] : null;

        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':nik', $data['nik'] ?? null);
        $stmt->bindValue(':phone_number', $data['phone_number'] ?? null);
        $stmt->bindValue(':pks_expired', $pks);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function searchByName($term)
    {
        // Menggunakan alias 'text' agar sesuai dengan default TomSelect
        $query = "SELECT id, name as text, pks_expired FROM {$this->table} WHERE name LIKE :term ORDER BY name ASC LIMIT 20";
        $stmt  = $this->db->prepare($query);
        $stmt->execute(['term' => "%$term%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // UPDATE: Tambahkan kolom baru di SELECT count
    public function getTotalCount($searchTerm = null, $zone = null)
    {
        $sql = "SELECT COUNT(DISTINCT fc.id) as total FROM {$this->table} fc";
        if ($zone) {
            $sql .= " JOIN parking_locations pl ON fc.id = pl.field_coordinator_id";
        }
        $conditions = [];
        if ($searchTerm) {
            $conditions[] = "(fc.name LIKE :searchTerm OR fc.nik LIKE :searchTerm)";
        }

        if ($zone) {
            $conditions[] = "pl.zone = :zone";
        }

        if (! empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->db->prepare($sql);
        if ($searchTerm) {
            $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        }

        if ($zone) {
            $stmt->bindValue(':zone', $zone);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ)->total;
    }

    // UPDATE: Tambahkan nik & phone di getPaginated
    public function getPaginated($limit, $offset, $searchTerm = null, $zone = null)
    {
        $sql = "SELECT
                fc.id, fc.name, fc.nik, fc.phone_number, fc.pks_expired,
                COUNT(pl.id) as location_count
            FROM {$this->table} fc
            LEFT JOIN parking_locations pl ON fc.id = pl.field_coordinator_id";

        $conditions = [];
        if ($searchTerm) {
            $conditions[] = "(fc.name LIKE :searchTerm OR fc.nik LIKE :searchTerm)";
        }

        if ($zone) {
            $conditions[] = "pl.zone = :zone";
        }

        if (! empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " GROUP BY fc.id, fc.name, fc.nik, fc.phone_number, fc.pks_expired, fc.created_at";
        $sql .= " ORDER BY fc.name ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if ($searchTerm) {
            $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        }

        if ($zone) {
            $stmt->bindValue(':zone', $zone);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // UPDATE: Tambahkan nik & phone di getAllWithLocations (Untuk PDF)
    public function getAllWithLocations($zone = null)
    {
        $sql = "SELECT
                    fc.id, fc.name, fc.nik, fc.phone_number, fc.pks_expired,
                    pl.parking_location, pl.address, pl.zone
                  FROM {$this->table} fc
                  JOIN parking_locations pl ON fc.id = pl.field_coordinator_id";

        if ($zone) {
            $sql .= " WHERE pl.zone = :zone";
        }

        $sql .= " ORDER BY fc.name ASC, pl.address ASC";

        $stmt = $this->db->prepare($sql);
        if ($zone) {
            $stmt->execute(['zone' => $zone]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getDetailWithLocations($id)
    {
        $coord = $this->getById($id);
        if ($coord) {
            $stmt = $this->db->prepare("SELECT * FROM parking_locations WHERE field_coordinator_id = :id ORDER BY address ASC");
            $stmt->execute(['id' => $id]);
            $coord->locations = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        return $coord;
    }

    // public function getAvailableForTakeover()
    // {
    //     $query = "SELECT fc.* FROM {$this->table} fc
    //               LEFT JOIN pks_takeovers pt ON fc.id = pt.field_coordinator_id
    //               WHERE pt.id IS NULL
    //               ORDER BY fc.name ASC";
    //     $stmt = $this->db->prepare($query);
    //     $stmt->execute();
    //     return $stmt->fetchAll(PDO::FETCH_OBJ);
    // }

    public function getAvailableForTakeover()
    {
        $query = "SELECT fc.* FROM {$this->table} fc
                  LEFT JOIN pks_takeovers pt ON fc.id = pt.field_coordinator_id
                  WHERE pt.id IS NULL
                  AND fc.pks_expired < CURDATE() -- HANYA YANG SUDAH LEWAT TANGGAL HARI INI
                  ORDER BY fc.name ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
