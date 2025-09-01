<?php
class HomeController extends Controller
{
    public function index()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('parkinglocations');
        } else {
            $this->redirect('auth/login');
        }
    }
}
