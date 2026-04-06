<?php
require_once BASE_PATH . '/app/middleware/Auth.php';

class AuthController
{
    public function loginForm()
    {
        if (Auth::check()) {
            Response::redirect('dashboard');
            return;
        }

        $error = Session::getFlash('login_error');
        require BASE_PATH . '/app/views/auth/login.php';
    }

    public function login()
    {
        Csrf::validate();

        $password = $_POST['password'] ?? '';

        if (Auth::login($password)) {
            Response::redirect('dashboard');
        } else {
            Session::flash('login_error', 'Contrasena incorrecta');
            Response::redirect('login');
        }
    }

    public function logout()
    {
        Auth::logout();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
