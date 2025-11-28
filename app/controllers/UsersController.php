<?php

class UsersController extends Controller
{
    public function __construct()
    {
        // Hanya Admin yang boleh akses
        if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak.'];
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $userModel = $this->model('User');

        $data['users']      = $userModel->getAll();
        $data['title']      = 'Manajemen Pengguna';
        $data['csrf_token'] = $this->generateCsrf();

        $this->view('layouts/header', $data);
        $this->view('users/index', $data);
        $this->view('layouts/footer');
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('Token Invalid');
            }

            // Validasi sederhana: Cek username unik (bisa dikembangkan)
            $userModel = $this->model('User');
            if ($userModel->findByUsername($_POST['username'])) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Username sudah digunakan!'];
                $this->redirect('users');
                return;
            }

            if ($userModel->create($_POST)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'User baru berhasil ditambahkan!'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambah user.'];
            }
            $this->redirect('users');
        }
    }

    public function getUserJson($id)
    {
        header('Content-Type: application/json');
        $user = $this->model('User')->getById($id);
        // Hapus password hash dari response JSON demi keamanan
        unset($user->password);
        echo json_encode($user);
        exit;
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                die('Token Invalid');
            }

            $userModel = $this->model('User');

            // Cek duplikasi username jika username diganti (opsional, disini kita skip dulu biar simpel)

            if ($userModel->update($id, $_POST)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data user berhasil diupdate!'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal update user.'];
            }
            $this->redirect('users');
        }
    }

    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Cegah hapus diri sendiri
            if ($id == $_SESSION['user_id']) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Anda tidak bisa menghapus akun sendiri!'];
                $this->redirect('users');
                return;
            }

            $userModel = $this->model('User');
            if ($userModel->delete($id)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'User berhasil dihapus!'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus user.'];
            }
            $this->redirect('users');
        }
    }
}
