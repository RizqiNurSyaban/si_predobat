<?php
// config/auth.php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function require_auth(): void {
    if (!is_logged_in()) {
        set_flash('error', 'Silakan masuk terlebih dahulu untuk mengakses sistem.');
        redirect('login.php');
    }
}

function login_user(string $usernameOrEmail, string $password): bool {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE (email = :em OR username = :un) AND is_active = 1 LIMIT 1");
    $stmt->execute([':em' => $usernameOrEmail, ':un' => $usernameOrEmail]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        unset($user['password']);
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
