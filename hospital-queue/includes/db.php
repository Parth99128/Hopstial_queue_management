<?php
// db.php — ONLY talks to MySQL. Tables are NOT created here:
// run install.php once (it executes database.sql). If the database
// is missing, we stop with a friendly hint instead of half-working.

// h() = shortcut for htmlspecialchars — prevents XSS. Use on EVERY echo of user data.
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function db() {
    static $conn = null;          // static = reuse ONE connection per request
    if ($conn !== null) return $conn;

    // PHP 8 turns DB errors into exceptions — catch and show a helpful page instead.
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $e) {
        die('<h2>Cannot reach the database.</h2>'
          . '<p>1. Start <b>MySQL</b> in XAMPP &nbsp; 2. open <b><a href="install.php">install.php</a></b> once to create it.</p>'
          . '<p><small>Technical detail: ' . h($e->getMessage()) . '</small></p>');
    }
    return $conn;
}

function status_label($s) { return ucwords(str_replace('_', ' ', $s)); }

// Token format per doctor, restarted daily: D{doctor_id}-{seq}  e.g. D2-004
// $last_seq_today = HIGHEST seq this doctor already has TODAY (0 if none yet).
// The same number may repeat across days — the DB enforces per-day uniqueness.
function make_token_no($doctor_id, $last_seq_today) {
    return 'D' . (int)$doctor_id . '-' . str_pad($last_seq_today + 1, 3, '0', STR_PAD_LEFT);
}
