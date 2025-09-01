<?php
class Controller
{
    public function view($view, $data = [])
    {
        // Ekstrak data agar bisa diakses sebagai variabel di view
        extract($data);

        require_once '../app/views/' . $view . '.php';
    }

    public function model($model)
    {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    // Helper untuk proteksi CSRF
    public function generateCsrf()
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf($token)
    {
        // Hanya cek kecocokan, jangan hapus token di sini
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }
    // Helper untuk redirect
    protected function redirect($url)
    {
        header('Location: ' . BASE_URL . '/' . $url);
        exit();
    }
}
