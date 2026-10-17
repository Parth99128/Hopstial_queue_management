<?php require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php'; require_role(['admin']);
// Staff accounts — admin only: create + reset password + delete (never yourself).
$note = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $n = ucwords(strtolower(trim($_POST['uname'] ?? '')));
        $m = preg_replace('/\D/', '', $_POST['umobile'] ?? '');
        $p = $_POST['upass'] ?? ''; $r = ($_POST['urole'] ?? 'staff') === 'admin' ? 'admin' : 'staff';
        if (strlen($n) < 3) $note = '<div class="err">Name 3+ letters.</div>';
        elseif (!preg_match('/^[6-9]\d{9}$/', $m)) $note = '<div class="err">Mobile 10 digits starting 6-9.</div>';
        elseif (strlen($p) < 6) $note = '<div class="err">Password 6+ characters.</div>';
        else {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $st = $conn->prepare("INSERT INTO users (name,mobile,password_hash,role) VALUES (?,?,?,?)");
            $st->bind_param('ssss', $n, $m, $hash, $r);
            if ($st->execute()) $note = '<div class="ok">Login created: <b>' . h($n) . '</b> (' . h($r) . ').</div>';
            else $note = '<div class="err">' . (strpos($conn->error, 'Duplicate') !== false ? 'Mobile already registered.' : 'DB error.') . '</div>';
        }
    } elseif (isset($_POST['reset_pw'])) {
        $id = (int)$_POST['user_id']; $p = $_POST['newpass'] ?? '';
        if (strlen($p) < 6) $note = '<div class="err">New password 6+ characters.</div>';
        else { $hash = password_hash($p, PASSWORD_DEFAULT); $st = $conn->prepare("UPDATE users SET password_hash=? WHERE id=?"); $st->bind_param('si', $hash, $id); $st->execute(); $note = '<div class="ok">Password reset.</div>'; }
    } elseif (isset($_POST['del_user'])) {
        $id = (int)$_POST['user_id'];
        if ($id === $user['id']) $note = '<div class="err">You cannot delete yourself.</div>';
        else { $conn->query("DELETE FROM users WHERE id=$id"); $note = '<div class="ok">User deleted.</div>'; }
    }
}
$users = $conn->query("SELECT * FROM users ORDER BY FIELD(role,'admin','staff'), name")->fetch_all(MYSQLI_ASSOC);
?>
<section class="card">
  <p><a href="staff-queue.php">← Queue</a></p>
  <h1>Staff Accounts</h1><?= $note ?>
  <h3>Create login</h3>
  <form method="post" class="grid grid-2">
    <div><label for="uname">Full name</label><input id="uname" name="uname" required></div>
    <div><label for="umobile">Mobile (login id)</label><input id="umobile" name="umobile" required pattern="[6-9][0-9]{9}" placeholder="98XXXXXXXX"></div>
    <div><label for="upass">Password (6+ chars)</label><input id="upass" type="password" name="upass" required minlength="6"></div>
    <div><label for="urole">Role</label><select id="urole" name="urole"><option value="staff">staff</option><option value="admin">admin</option></select></div>
    <p><button class="btn" type="submit" name="add_user" value="1">Create login</button></p>
  </form>
  <table><thead><tr><th>Name / Mobile</th><th>Role</th><th>Since</th><th></th></tr></thead><tbody>
  <?php foreach ($users as $u): ?><tr>
    <td><b><?= h($u['name']) ?></b><br><small class="muted"><?= h($u['mobile']) ?><?= $u['id'] === $user['id'] ? ' (you)' : '' ?></small></td>
    <td><span class="pill <?= $u['role'] === 'admin' ? 'called' : 'done' ?>"><?= h($u['role']) ?></span></td>
    <td><small><?= h($u['created_at']) ?></small></td>
    <td>
      <form method="post" style="display:inline-flex;gap:4px" onsubmit="return confirm('Reset password for <?= h($u['name']) ?>?')">
        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
        <input type="password" name="newpass" required minlength="6" placeholder="New pw" style="width:110px">
        <button name="reset_pw" value="1" class="btn btn-sm btn-ghost">Reset</button>
      </form>
      <?php if ($u['id'] !== $user['id']): ?>
      <form method="post" style="display:inline" onsubmit="return confirm('Delete <?= h($u['name']) ?>?')">
        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
        <button name="del_user" value="1" class="btn btn-sm btn-danger">Del</button>
      </form>
      <?php endif; ?>
    </td></tr><?php endforeach; ?></tbody></table>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
