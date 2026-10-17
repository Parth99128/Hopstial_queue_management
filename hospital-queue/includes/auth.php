<?php
// Session + role guard. Loaded via header.php on EVERY page, so no page
// can be viewed without passing through session + access checks.
if (session_status() === PHP_SESSION_NONE) {
    // HttpOnly + SameSite cookies (XSS/CSRF hygiene — viva point)
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function current_user() { return $_SESSION['user'] ?? null; }
function is_logged_in() { return isset($_SESSION['user']); }

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php?msg=' . urlencode('Please login first.'));
        exit;
    }
}
// Usage: require_role(['doctor']);  require_role(['receptionist','admin']);
function require_role($roles) {
    require_login();
    if (!in_array($_SESSION['user']['role'], (array)$roles)) {
        http_response_code(403);
        die('<h2>403 — Not allowed for your role.</h2><p><a href="index.php">Go home</a></p>');
    }
}
function login_user($row) {
    session_regenerate_id(true); // anti session-fixation on every login
    $_SESSION['user'] = [
        'id'     => $row['id'],
        'name'   => $row['name'],
        'mobile' => $row['mobile'],
        'role'   => $row['role'],
    ];
}
function logout_user() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
