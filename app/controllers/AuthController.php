<?php
require_once BASE_PATH . '/app/middleware/Auth.php';

class AuthController
{
    public function loginForm()
    {
        if (Auth::check()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $error = Session::getFlash('login_error');
        $oldUser = Session::getFlash('login_user');
        require BASE_PATH . '/app/views/auth/login.php';
    }

    public function login()
    {
        Csrf::validate();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            Session::flash('login_error', 'Usuario y contrasena son obligatorios');
            Session::flash('login_user', $username);
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (Auth::login($username, $password)) {
            // Operador va directo al panel touch; admin al dashboard
            $destino = Auth::isAdmin() ? '/dashboard' : '/solicitudes/nueva';
            header('Location: ' . BASE_URL . $destino);
            exit;
        }

        Session::flash('login_error', 'Usuario o contrasena incorrectos');
        Session::flash('login_user', $username);
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function logout()
    {
        Auth::logout();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
