<?php
class AuthController extends Controller
{

    public function login()
    {
        $data['title']      = 'Login';
        $data['csrf_token'] = $this->generateCsrf();
        $this->view('layouts/login_header', $data);
        $this->view('auth/login', $data);
        $this->view('layouts/footer');
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (! $this->verifyCsrf($_POST['csrf_token'])) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sesi Anda tidak valid. Silakan coba login kembali.'];
                $this->redirect('auth/login');
                return;
            }

            // HAPUS TOKEN SETELAH BERHASIL DIVERIFIKASI
            unset($_SESSION['csrf_token']);

            $userModel = $this->model('User');
            $user      = $userModel->findByUsername($_POST['username']);

            if ($user && password_verify($_POST['password'], $user->password)) {
                // Login sukses
                $_SESSION['user_id']   = $user->id;
                $_SESSION['username']  = $user->username;
                $_SESSION['user_role'] = $user->role;
                $_SESSION['flash']     = ['type' => 'success', 'message' => 'Login berhasil!'];
                $_SESSION['user_team'] = $user->team_name;

                // LOGIKA REDIRECT BARU BERDASARKAN ROLE
                if ($user->role === 'admin') {
                    $this->redirect('admin'); // Akan memanggil AdminController->index()
                } elseif ($user->role === 'guest') {
                    $this->redirect('guest'); // Akan memanggil GuestController->index()
                } elseif ($user->role === 'team') {
                    $this->redirect('team'); // Akan memanggil GuestController->index()
                } else {
                    // Fallback jika ada role lain yang tidak terdefinisi
                    $this->redirect('auth/login');
                }
            } else {
                // Login gagal
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Username atau password salah.'];
                $this->redirect('auth/login');
            }
        }
    }

    public function logout()
    {
        session_destroy();
        $this->redirect('auth/login');
    }
}
