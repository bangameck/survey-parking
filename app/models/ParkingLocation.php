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

    // Mengambil semua data dengan JOIN ke tabel koordinator
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

    // Mengambil satu data berdasarkan ID dengan JOIN
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

    // Membuat satu data lokasi baru
    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (field_coordinator_id, address, parking_location)
                  VALUES (:field_coordinator_id, :address, :parking_location)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'field_coordinator_id' => $data['field_coordinator_id'],
            'address'              => $data['address'],
            'parking_location'     => $data['parking_location'],
        ]);
    }

    // FUNGSI BARU: Membuat banyak data sekaligus (untuk import) dengan Transaction
    public function createBatch($locations)
    {
        $this->db->beginTransaction();
        try {
            $query = "INSERT INTO {$this->table} (field_coordinator_id, address, parking_location)
                      VALUES (:field_coordinator_id, :address, :parking_location)";
            $stmt = $this->db->prepare($query);

            foreach ($locations as $loc) {
                $stmt->execute([
                    'field_coordinator_id' => $loc['field_coordinator_id'],
                    'address'              => $loc['address'],
                    'parking_location'     => $loc['parking_location'],
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            // Optional: Log error $e->getMessage();
            return false;
        }
    }

    // Mengupdate data lokasi
    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} SET
                    field_coordinator_id = :field_coordinator_id,
                    address = :address,
                    parking_location = :parking_location
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'id'                   => $id,
            'field_coordinator_id' => $data['field_coordinator_id'],
            'address'              => $data['address'],
            'parking_location'     => $data['parking_location'],
        ]);
    }

    // Menghapus data lokasi
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    public function getTotalCount($coordinator_id = null)
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        if ($coordinator_id) {
            $sql .= " WHERE field_coordinator_id = :coordinator_id";
            $params[':coordinator_id'] = $coordinator_id;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // FUNGSI BARU: Mengambil data dengan limit dan offset (dengan filter opsional)
    public function getPaginated($limit, $offset, $coordinator_id = null)
    {
        $sql = "SELECT pl.*, fc.name as coordinator_name
                FROM {$this->table} pl
                JOIN field_coordinators fc ON pl.field_coordinator_id = fc.id";
        $params = [];
        if ($coordinator_id) {
            $sql .= " WHERE pl.field_coordinator_id = :coordinator_id";
            $params[':coordinator_id'] = $coordinator_id;
        }
        $sql .= " ORDER BY pl.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        // Bind parameter filter jika ada
        if ($coordinator_id) {
            $stmt->bindParam(':coordinator_id', $coordinator_id, PDO::PARAM_INT);
        }

        // Bind parameter pagination (harus sebagai integer)
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function searchByName($term)
    {
        $query = "SELECT id, parking_location as text
              FROM {$this->table}
              WHERE parking_location LIKE :term
              LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['term' => '%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function searchAddress($term)
    {
        // Menggunakan DISTINCT untuk memastikan setiap alamat hanya muncul sekali
        $query = "SELECT DISTINCT address as id, address as text
              FROM {$this->table}
              WHERE address LIKE :term
              ORDER BY address ASC
              LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['term' => '%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

// FUNGSI BARU: Mengambil semua lokasi yang cocok dengan alamat tertentu
    public function getByAddress($address)
    {
        $query = "SELECT pl.*, fc.name as coordinator_name
              FROM {$this->table} pl
              JOIN field_coordinators fc ON pl.field_coordinator_id = fc.id
              WHERE pl.address = :address
              ORDER BY pl.parking_location ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['address' => $address]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
