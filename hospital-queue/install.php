<?php
// install.php — ONE-TIME setup wizard. Open in the browser once, then it LOCKS itself.
// NOTE: does NOT load common.php — the database may not exist yet!
// Only needs config.php (the MySQL address).
require_once __DIR__ . '/includes/config.php';

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }  // same helper as db.php

$state = 'form';   // form | done | locked | dead
$err = '';

// 1. Can we reach MySQL at all? (PHP 8 throws on failure — catch it.)
try { $link = new mysqli(DB_HOST, DB_USER, DB_PASS); }
catch (mysqli_sql_exception $e) { $link = null; $dead_why = $e->getMessage(); }
if ($link === null || isset($dead_why)) { $state = 'dead'; }
else {
    $link->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $link->select_db(DB_NAME);
    $link->set_charset('utf8mb4');

    // 2. Already installed? = at least one admin login exists → refuse to run again.
    try {
        $chk = $link->query("SELECT id FROM users WHERE role='admin' LIMIT 1");
        if ($chk && $chk->num_rows > 0) $state = 'locked';
    } catch (mysqli_sql_exception $e) { /* no tables yet — fresh install, keep going */ }

    // 3. Fresh install: build schema + create the FIRST admin account.
    if ($state === 'form' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = ucwords(strtolower(trim($_POST['name'] ?? '')));
        $mobile = preg_replace('/\D/', '', $_POST['mobile'] ?? '');
        $pass = $_POST['password'] ?? '';
        if (strlen($name) < 3) $err = 'Name must be 3+ letters.';
        elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) $err = 'Mobile must be 10 digits starting 6-9.';
        elseif (strlen($pass) < 6) $err = 'Password must be 6+ characters.';
        else {
            try {
                // Run database.sql statement-by-statement ("cut on ; and run each").
                $sql = file_get_contents(__DIR__ . '/database.sql');
                $sql = preg_replace('/^\s*--.*$/m', '', $sql);          // drop full-line -- comments
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) $link->query($stmt);
                // Admin login (bcrypt hash — never plain text).
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $role = 'admin';
                $st = $link->prepare("INSERT INTO users (name,mobile,password_hash,role) VALUES (?,?,?,?)");
                $st->bind_param('ssss', $name, $mobile, $hash, $role);
                $st->execute();
                $state = 'done'; $admin_mobile = $mobile;
            } catch (mysqli_sql_exception $e) {
                $err = (strpos($e->getMessage(), 'Duplicate') !== false)
                    ? 'This mobile is already registered — is the app already installed?'
                    : 'Install failed: ' . h($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Install — <?= h(SITE_NAME) ?></title><link rel="stylesheet" href="css/style.css"></head>
<body><main class="wrap">
<section class="card" style="max-width:560px;margin:24px auto">
  <h1>Queue System — one-time setup</h1>
  <?php if ($state === 'dead'): ?>
    <div class="err"><b>Cannot reach MySQL</b> (<?= h($dead_why ?? '') ?>).<br>Start MySQL in XAMPP first, then reload this page.</div>
  <?php elseif ($state === 'locked'): ?>
    <div class="ok"><b>Already installed.</b> An admin account exists, so this page is locked.</div>
    <p><a class="btn" href="login.php">Go to Staff login</a> <a class="btn btn-ghost" href="index.php">Home</a></p>
  <?php elseif ($state === 'done'): ?>
    <div class="ok"><b>Installed!</b> Created the 3 tables + your admin login (<code><?= h($admin_mobile) ?></code>).</div>
    <p><b>Next:</b> 1. <a href="login.php">Login as staff</a> → add doctors.<br>
    2. Open <a href="book-token.php">Get Token</a> in another tab and take the first real token.</p>
    <p class="muted">This page is now locked and safe to leave.</p>
  <?php else: ?>
    <p class="muted">This creates the <code><?= h(DB_NAME) ?></code> database + 3 tables (from <code>database.sql</code>) and YOUR admin account. No fake data.</p>
    <?php if ($err): ?><div class="err"><?= $err ?></div><?php endif; ?>
    <form method="post">
      <label for="name">Your name (Admin)</label><input id="name" name="name" required>
      <label for="mobile">Your mobile (will be the login id)</label><input id="mobile" name="mobile" required pattern="[6-9][0-9]{9}" placeholder="98XXXXXXXX">
      <label for="password">Choose admin password (6+ chars)</label><input id="password" type="password" name="password" required minlength="6">
      <p><button class="btn" type="submit">Install now</button></p>
    </form>
  <?php endif; ?>
</section>
</main></body></html>
