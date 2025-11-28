<?php
class User
{
    private $db;
    private $table = 'users';

    public function __construct()
    {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Dipakai untuk Login
    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // --- FUNGSI CRUD BARU ---

    public function getAll()
    {
        // Urutkan berdasarkan role agar rapi
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY role ASC, username ASC");
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
        $query = "INSERT INTO {$this->table} (username, password, role, team_name) VALUES (:username, :password, :role, :team_name)";
        $stmt  = $this->db->prepare($query);

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Team name hanya diisi jika role = 'team', selain itu null
        $teamName = ($data['role'] === 'team') ? $data['team_name'] : null;

        $stmt->bindValue(':username', $data['username']);
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->bindValue(':role', $data['role']);
        $stmt->bindValue(':team_name', $teamName);

        return $stmt->execute();
    }

    public function update($id, $data)
    {
        // Cek apakah password diisi atau tidak
        if (! empty($data['password'])) {
            // Jika password diisi, update password juga
            $query          = "UPDATE {$this->table} SET username = :username, password = :password, role = :role, team_name = :team_name WHERE id = :id";
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // Jika kosong, jangan ubah password lama
            $query = "UPDATE {$this->table} SET username = :username, role = :role, team_name = :team_name WHERE id = :id";
        }

        $stmt = $this->db->prepare($query);

        $teamName = ($data['role'] === 'team') ? $data['team_name'] : null;

        $stmt->bindValue(':username', $data['username']);
        $stmt->bindValue(':role', $data['role']);
        $stmt->bindValue(':team_name', $teamName);
        $stmt->bindValue(':id', $id);

        if (! empty($data['password'])) {
            $stmt->bindValue(':password', $hashedPassword);
        }

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    // Helper untuk Dropdown Tim (Sudah ada sebelumnya)
    public function getAllTeams()
    {
        $stmt = $this->db->prepare("SELECT DISTINCT team_name FROM users WHERE role = 'team' AND team_name IS NOT NULL ORDER BY team_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getMembersByTeam($team_name)
    {
        $stmt = $this->db->prepare("SELECT username FROM users WHERE team_name = :team");
        $stmt->execute(['team' => $team_name]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
