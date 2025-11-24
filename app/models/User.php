<?php
class User
{
    private $db;

    public function __construct()
    {
        // Buat koneksi database di sini atau panggil dari helper
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getAllTeams()
    {
        $stmt = $this->db->prepare("SELECT DISTINCT team_name FROM users WHERE role = 'team' AND team_name IS NOT NULL ORDER BY team_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Ambil anggota berdasarkan nama tim
    public function getMembersByTeam($team_name)
    {
        // Kita ambil username atau nama asli jika ada.
        // Di sini kita ambil bagian nama dari username (misal: tim1_budi -> budi)
        $stmt = $this->db->prepare("SELECT username FROM users WHERE team_name = :team");
        $stmt->execute(['team' => $team_name]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
