<?php


//handles all authentication logic like login, register, logout, session, all that shit
//returns onl data or redirects.

require_once __DIR__ . '/../entities/User.php';

class AuthController {


    //login with email and password
    //if success store userid in session
    //if fail return error
    public function login(string $email, string $password): array {
        $row = DB::first(
            'SELECT * FROM users WHERE email = ?',
            [strtolower(trim($email))]
        );

        if (!$row || !password_verify($password, $row['password'])) {
            return ['error' => 'Invalid email or password.'];
        }
        if ($row['is_suspended']) {
            return ['error' => 'This account has been suspended.'];
        }

        $_SESSION['user_id'] = $row['id'];
        session_regenerate_id(true);

        return ['ok' => true];
    }


    //register new free user
    public function register(string $name, string $email, string $password, string $confirm): array {
        $email = strtolower(trim($email));
        $name  = trim($name);

        if (empty($name)) {
            return ['error' => 'Full name is required.'];
        }
        if ($password !== $confirm) {
            return ['error' => "Passwords don't match."];
        }
        if (strlen($password) < 6) {
            return ['error' => 'Password must be at least 6 characters.'];
        }
        if (DB::first('SELECT id FROM users WHERE email = ?', [$email])) {
            return ['error' => 'An account with that email already exists.'];
        }

        //hash password with bcrypt
        DB::execute(
            'INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, ?)',
            [$email, password_hash($password, PASSWORD_BCRYPT), $name, 'free']
        );

        return ['ok' => true];
    }


    //logout, destroy session
    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }


    //return log in user or null if not log in
    public function currentUser(): ?User {
        if (empty($_SESSION['user_id'])) return null;
        static $cache = [];
        $id = $_SESSION['user_id'];
        if (!isset($cache[$id])) {
            $row        = DB::first('SELECT * FROM users WHERE id = ?', [$id]);
            $cache[$id] = $row ? new User($row) : null;
        }
        return $cache[$id];
    }

    //redirect to login page if no session
    public function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
    }
}
