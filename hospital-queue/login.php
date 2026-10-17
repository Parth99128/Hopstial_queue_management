<?php require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php';
// THE staff login (only one). Logged-in staff skip straight to History (their home).
if (is_logged_in()) { header('Location: history.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = preg_replace('/\D/', '', $_POST['mobile'] ?? '');
    $pass = $_POST['password'] ?? '';
    $st = $conn->prepare("SELECT * FROM users WHERE mobile=?");   // ? = injection-safe
    $st->bind_param('s', $mobile); $st->execute();
    $u = $st->get_result()->fetch_assoc();
    if ($u && password_verify($pass, $u['password_hash'])) {
        login_user($u);
        header('Location: history.php');
        exit;
    }
    $err = 'Invalid mobile or password.';
}
?>
<section class="card" style="max-width:520px;margin:18px auto">
  <h1>Staff Login</h1>
  <p class="muted">Only hospital staff log in. Patients just <a href="book-token.php">get a token</a> — no account needed.<br>Admins use this same form with their admin mobile number — the <b>Accounts</b> tab appears after login.</p>
  <?php if ($err): ?><div class="err"><?= h($err) ?></div><?php endif; ?>
  <form method="post" id="loginForm">
    <label for="mobile">Mobile (login id)</label>
    <input id="mobile" name="mobile" required inputmode="numeric" placeholder="10-digit mobile">
    <label for="password">Password</label>
    <input id="password" type="password" name="password" required>
    <p><button class="btn" type="submit">Login</button></p>
  </form>
</section>
<script src="js/validation.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
