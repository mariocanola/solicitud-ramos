<?php
require_once BASE_PATH . '/app/middleware/Auth.php';
require_once BASE_PATH . '/app/helpers/RateLimiter.php';
require_once BASE_PATH . '/app/models/Usuario.php';

class AuthController
{
    public function loginForm()
    {
        if (Auth::check()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $error   = Session::getFlash('login_error');
        $oldUser = Session::getFlash('login_user');
        if (empty($error) && !empty($_GET['expired'])) {
            $error = 'Tu sesión expiró por inactividad. Por favor iniciá sesión nuevamente.';
        }
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

        if (RateLimiter::tooManyAttempts($username)) {
            $mins = RateLimiter::retryAfterMinutes($username);
            Session::flash('login_error', "Demasiados intentos fallidos. Espere {$mins} minuto(s) e intente de nuevo.");
            Session::flash('login_user', $username);
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (Auth::login($username, $password)) {
            RateLimiter::clear($username);
            $destino = Auth::isAdmin() ? '/dashboard' : '/solicitudes/nueva';
            header('Location: ' . BASE_URL . $destino);
            exit;
        }

        RateLimiter::hit($username);
        Session::flash('login_error', 'Usuario o contrasena incorrectos');
        Session::flash('login_user', $username);
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function cambiarPassword()
    {
        Csrf::validate();

        $actual  = (string)($_POST['password_actual'] ?? '');
        $nueva   = (string)($_POST['password_nueva'] ?? '');
        $confirm = (string)($_POST['password_confirm'] ?? '');

        if ($actual === '' || $nueva === '' || $confirm === '') {
            Response::error('Complete todos los campos');
            return;
        }

        if (strlen($nueva) < 8) {
            Response::error('La nueva contrasena debe tener al menos 8 caracteres');
            return;
        }

        if ($nueva !== $confirm) {
            Response::error('La confirmacion no coincide');
            return;
        }

        if ($nueva === $actual) {
            Response::error('La nueva contrasena debe ser distinta a la actual');
            return;
        }

        $userModel = new Usuario();
        $user = $userModel->getById(Auth::id());
        if (!$user || !password_verify($actual, $user['password_hash'])) {
            Response::error('La contrasena actual no es correcta');
            return;
        }

        $userModel->actualizarPassword((int)$user['id'], $nueva);
        session_regenerate_id(true);
        Response::success(null, 'Contrasena actualizada');
    }

    public function logout()
    {
        Csrf::validate();
        Auth::logout();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
