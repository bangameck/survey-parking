<?php
class FieldCoordinator
{
    private $db;
    private $table = 'field_coordinators';

    public function __construct()
    {
        // Inisialisasi koneksi database
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Mengambil semua data koordinator
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Mengambil satu data koordinator berdasarkan ID
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Menambah data koordinator baru
    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (name) VALUES (:name)";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);

        return $stmt->execute();
    }

    // Mengupdate data koordinator
    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} SET name = :name WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // Menghapus data koordinator
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function searchByName($term)
    {
        // Mengembalikan dalam format ID dan Text, sesuai kebutuhan Tom Select
        $query = "SELECT id, name as text
              FROM {$this->table}
              WHERE name LIKE :term
              ORDER BY name ASC
              LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['term' => '%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // FUNGSI BARU: Menghitung total data (dengan filter pencarian)
    public function getTotalCount($searchTerm = null)
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        if ($searchTerm) {
            $sql .= " WHERE name LIKE :searchTerm";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // FUNGSI BARU: Mengambil data dengan limit dan offset (dengan filter pencarian)
    public function getPaginated($limit, $offset, $searchTerm = null)
    {
        $sql    = "SELECT * FROM {$this->table}";
        $params = [];
        if ($searchTerm) {
            $sql .= " WHERE name LIKE :searchTerm";
        }
        $sql .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if ($searchTerm) {
            $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
