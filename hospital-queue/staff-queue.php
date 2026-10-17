<?php require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php'; require_login();
// Staff queue — Call / Complete / Cancel. Pure FIFO: oldest waiting first.
$note = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['token_id'] ?? 0);
    $act = $_POST['action'] ?? '';
    if ($act === 'call') { $conn->query("UPDATE tokens SET status='called' WHERE id=$id AND status='waiting'"); $note = '<div class="ok">Called.</div>'; }
    elseif ($act === 'complete') { $conn->query("UPDATE tokens SET status='completed' WHERE id=$id AND status IN ('waiting','called')"); $note = '<div class="ok">Completed.</div>'; }
    elseif ($act === 'cancel') { $conn->query("UPDATE tokens SET status='cancelled' WHERE id=$id AND status IN ('waiting','called')"); $note = '<div class="ok">Cancelled.</div>'; }
}
$docs = $conn->query("SELECT * FROM doctors ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$f = (int)($_GET['doctor'] ?? 0);
$w = $f > 0 ? "AND t.doctor_id=$f" : "";
// Now serving + waiting, today only, oldest first
$now = $conn->query("SELECT t.*, d.name doctor, d.room FROM tokens t JOIN doctors d ON d.id=t.doctor_id WHERE t.status='called' AND DATE(t.created_at)=CURDATE() $w ORDER BY t.created_at ASC")->fetch_all(MYSQLI_ASSOC);
$wait = $conn->query("SELECT t.*, d.name doctor, d.room FROM tokens t JOIN doctors d ON d.id=t.doctor_id WHERE t.status='waiting' AND DATE(t.created_at)=CURDATE() $w ORDER BY t.created_at ASC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
?>
<section class="card">
  <h1>Today's Queue</h1><?= $note ?>
  <form method="get" class="grid grid-2">
    <div><label for="doctor">Show queue for</label>
      <select id="doctor" name="doctor"><option value="0">All doctors</option>
      <?php foreach ($docs as $d): ?><option value="<?= $d['id'] ?>" <?= $f === $d['id'] ? 'selected' : '' ?>><?= h($d['name']) ?></option><?php endforeach; ?></select></div>
    <div><label>&nbsp;</label><button class="btn" type="submit">Go</button> <a class="btn btn-ghost" href="queue-live.php">Display board</a></div>
  </form>
  <h2>Now serving</h2>
  <?php if (!$now): ?><p class="muted">Nobody called right now.</p><?php endif; ?>
  <?php foreach ($now as $t): ?>
    <p><span class="pill called"><b><?= h($t['token_no']) ?></b> → <?= h($t['doctor']) ?> (<?= h($t['room']) ?>)</span>
    <form method="post" style="display:inline"><input type="hidden" name="token_id" value="<?= $t['id'] ?>">
      <button name="action" value="complete" class="btn btn-sm btn-ok">Complete</button>
      <button name="action" value="cancel" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this token?')">Cancel</button></form></p>
  <?php endforeach; ?>
  <h2>Waiting (<?= count($wait) ?>)</h2>
  <table><thead><tr><th>Token</th><th>Patient / Mobile</th><th>Doctor</th><th>Room</th><th>Action</th></tr></thead><tbody>
  <?php if (!$wait): ?><tr><td colspan="5" class="muted">Queue empty — great!</td></tr><?php endif; ?>
  <?php foreach ($wait as $t): ?><tr><td><b><?= h($t['token_no']) ?></b></td>
    <td><?= h($t['patient_name']) ?><br><small class="muted"><?= h($t['mobile']) ?></small></td>
    <td><?= h($t['doctor']) ?></td><td><?= h($t['room']) ?></td>
    <td><form method="post" style="display:inline"><input type="hidden" name="token_id" value="<?= $t['id'] ?>">
      <button name="action" value="call" class="btn btn-sm">Call</button>
      <button name="action" value="cancel" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this token?')">Cancel</button></form></td></tr>
  <?php endforeach; ?></tbody></table>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
