<?php

class AuthController extends Controller
{
    public function login()
    {
        // Jika sudah login, langsung arahkan ke dashboard sesuai role
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole($_SESSION['user_role']);
            return;
        }

        $data['title']      = 'Login';
        $data['csrf_token'] = $this->generateCsrf();

        // Halaman login berdiri sendiri (tanpa layout header/footer dashboard)
        $this->view('auth/login', $data);
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1. Verifikasi CSRF Token
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                $_SESSION['flash'] = [
                    'type'    => 'error',
                    'message' => 'Sesi Anda tidak valid. Silakan coba login kembali.',
                ];
                $this->redirect('auth/login');
                return;
            }

            // Hapus token setelah dipakai (Single Use)
            unset($_SESSION['csrf_token']);

            // 2. Ambil User dari Database
            $userModel = $this->model('User');
            $user      = $userModel->findByUsername($_POST['username']);

            // 3. Verifikasi Password
            if ($user && password_verify($_POST['password'], $user->password)) {

                // SET SESSION UTAMA
                $_SESSION['user_id']   = $user->id;
                $_SESSION['username']  = $user->username;
                $_SESSION['user_role'] = $user->role;

                // KHUSUS TIM: Simpan Nama Tim ke Session
                if ($user->role === 'team') {
                    $_SESSION['user_team'] = $user->team_name;
                }

                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Login berhasil! Selamat datang.'];

                // REDIRECT SESUAI ROLE
                $this->redirectBasedOnRole($user->role);

            } else {
                // Login Gagal
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Username atau password salah.'];
                $this->redirect('auth/login');
            }
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();

        // Mulai sesi baru untuk flash message logout (opsional)
        session_start();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Anda telah logout.'];

        $this->redirect('auth/login');
    }

    // Helper untuk mengarahkan user ke halaman yang tepat
    private function redirectBasedOnRole($role)
    {
        switch ($role) {
            case 'admin':
                $this->redirect('admin');
                break;
            case 'team':
                $this->redirect('team');
                break;
            case 'pimpinan':
                $this->redirect('pimpinan');
                break;
            case 'bendahara':
                $this->redirect('bendahara');
                break;
            case 'guest':
                $this->redirect('guest'); // Pastikan GuestController sudah ada
                break;
            default:
                // Fallback jika role tidak dikenali
                $this->redirect('auth/login');
                break;
        }
    }
}
