<?php require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php'; require_login();
// History + details dashboard — staff only. Every token ever issued,
// with filters. (Patients never see names except their own token slip.)
$docs = $conn->query("SELECT * FROM doctors ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$statuses = ['waiting', 'called', 'completed', 'cancelled'];
$f_doc = (int)($_GET['doctor'] ?? 0);
$f_status = in_array($_GET['status'] ?? 'all', $statuses) ? $_GET['status'] : 'all';
$f_date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'] ?? '') ? $_GET['date'] : date('Y-m-d');

// 1. Today at a glance (cards)
$today = ['total' => 0, 'waiting' => 0, 'called' => 0, 'completed' => 0, 'cancelled' => 0];
$q = $conn->query("SELECT status, COUNT(*) c FROM tokens WHERE DATE(created_at)=CURDATE() GROUP BY status");
while ($r = $q->fetch_assoc()) { $today[$r['status']] = (int)$r['c']; $today['total'] += (int)$r['c']; }

// 2. Per-doctor details for today
$perdoc = $conn->query("SELECT d.name, d.room,
    COUNT(t.id) total, SUM(t.status='waiting') waiting, SUM(t.status='completed') done
  FROM doctors d LEFT JOIN tokens t ON t.doctor_id=d.id AND DATE(t.created_at)=CURDATE()
  GROUP BY d.id ORDER BY d.name")->fetch_all(MYSQLI_ASSOC);

// 3. History rows with filters (newest first, 200 max)
$w = "DATE(t.created_at)='$f_date'";
if ($f_doc > 0) $w .= " AND t.doctor_id=$f_doc";
if ($f_status !== 'all') $w .= " AND t.status='$f_status'";
$rows = $conn->query("SELECT t.*, d.name doctor, d.room FROM tokens t
  JOIN doctors d ON d.id=t.doctor_id WHERE $w
  ORDER BY t.created_at DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
?>
<section class="card">
  <h1>History &amp; Details</h1>
  <div class="grid grid-4">
    <div class="card"><div class="stat"><?= (int)$today['total'] ?></div><div class="muted">Tokens today</div></div>
    <div class="card"><div class="stat"><?= (int)$today['waiting'] ?></div><div class="muted">Waiting</div></div>
    <div class="card"><div class="stat"><?= (int)$today['called'] ?></div><div class="muted">Serving</div></div>
    <div class="card"><div class="stat"><?= (int)$today['completed'] ?></div><div class="muted">Completed</div></div>
  </div>
  <h2>Per-doctor today</h2>
  <table><thead><tr><th>Doctor</th><th>Room</th><th>Total</th><th>Waiting</th><th>Done</th></tr></thead><tbody>
  <?php if (!$perdoc): ?><tr><td colspan="5" class="muted">No doctors yet.</td></tr><?php endif; ?>
  <?php foreach ($perdoc as $d): ?><tr><td><b><?= h($d['name']) ?></b></td><td><?= h($d['room']) ?></td>
    <td><?= (int)$d['total'] ?></td><td><?= (int)$d['waiting'] ?></td><td><?= (int)$d['done'] ?></td></tr>
  <?php endforeach; ?></tbody></table>
  <h2>Token history</h2>
  <form method="get" class="grid grid-3">
    <div><label for="date">Date</label><input id="date" type="date" name="date" value="<?= h($f_date) ?>"></div>
    <div><label for="doctor">Doctor</label><select id="doctor" name="doctor"><option value="0">All doctors</option>
      <?php foreach ($docs as $d): ?><option value="<?= $d['id'] ?>" <?= $f_doc === (int)$d['id'] ? 'selected' : '' ?>><?= h($d['name']) ?></option><?php endforeach; ?></select></div>
    <div><label for="status">Status</label><select id="status" name="status"><option value="all">All</option>
      <?php foreach ($statuses as $s): ?><option value="<?= $s ?>" <?= $f_status === $s ? 'selected' : '' ?>><?= h(status_label($s)) ?></option><?php endforeach; ?></select></div>
    <p><button class="btn btn-sm" type="submit">Filter</button>
    <span class="muted"><?= count($rows) ?> record(s)</span></p>
  </form>
  <table><thead><tr><th>Token</th><th>Patient / Mobile</th><th>Doctor</th><th>Status</th><th>Created</th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="5" class="muted">No records for this filter.</td></tr><?php endif; ?>
  <?php foreach ($rows as $t): ?><tr><td><b><?= h($t['token_no']) ?></b></td>
    <td><?= h($t['patient_name']) ?><br><small class="muted"><?= h($t['mobile']) ?></small></td>
    <td><?= h($t['doctor']) ?></td>
    <td><span class="pill <?= $t['status'] === 'completed' ? 'done' : ($t['status'] === 'waiting' ? 'wait' : 'called') ?>"><?= h(status_label($t['status'])) ?></span></td>
    <td><small><?= h($t['created_at']) ?></small></td></tr>
  <?php endforeach; ?></tbody></table>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
