<?php require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php';
// Get Token — PATIENTS ONLY (guests). Staff are bounced home to History:
// they manage tokens, never take them.
if (is_logged_in()) { header('Location: history.php'); exit; }
// Numbering restarts daily PER DOCTOR: count today's tokens for that doctor.
$doctors = $conn->query("SELECT * FROM doctors ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$msg = ''; $token_ok = ''; $token_doc = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['patient_name'] ?? '');
    $mobile = preg_replace('/\D/', '', $_POST['mobile'] ?? '');
    $doctor_id = (int)($_POST['doctor_id'] ?? 0);
    $errs = [];
    if (strlen($name) < 3 || !preg_match('/^[A-Za-z ]+$/', $name)) $errs[] = 'Name: 3+ letters only.';
    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) $errs[] = 'Mobile: 10 digits starting 6-9.';
    if ($doctor_id <= 0) $errs[] = 'Choose a doctor.';
    $name = ucwords(strtolower($name));   // tidy: "ravi kumar" → "Ravi Kumar"
    if (!$errs) {
        $d = $conn->query("SELECT name FROM doctors WHERE id=$doctor_id")->fetch_assoc();
        if (!$d) $errs[] = 'Doctor not found — pick again.';
        else {
            // KEY RULE: take the HIGHEST number used today for THIS doctor (0 if none),
            // then +1 → restarts daily per doctor. MAX also survives deleted/old rows.
            $row = $conn->query("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(token_no,'-',-1) AS UNSIGNED)),0) m
                                 FROM tokens WHERE doctor_id=$doctor_id AND booked_date=CURDATE()")->fetch_assoc();
            $tno = make_token_no($doctor_id, (int)$row['m']);
            $st = $conn->prepare("INSERT INTO tokens (token_no,patient_name,mobile,doctor_id,booked_date) VALUES (?,?,?,?,CURDATE())");
            $st->bind_param('sssi', $tno, $name, $mobile, $doctor_id);
            if ($st->execute()) { $token_ok = $tno; $token_doc = $d['name']; }
            else $errs[] = 'Could not save. Try again.';
        }
    }
    $msg = $errs ? '<div class="err">' . implode('<br>', array_map('h', $errs)) . '</div>' : '';
}
?>
<section class="card">
  <h1>Get Your Token</h1>
  <p class="muted">No login needed. Your number is valid today for the doctor you pick.</p>
  <?= $msg ?>
  <?php if ($token_ok): ?>
    <div class="ok"><h2>Your token: <span class="kbd"><?= h($token_ok) ?></span></h2>
    <p>Doctor: <b><?= h($token_doc) ?></b> — watch the <a href="queue-live.php">Display Board</a> and walk in when called.</p></div>
  <?php endif; ?>
  <?php if (!$doctors): ?>
    <div class="err">No doctors available yet — please come back later.</div>
  <?php else: ?>
  <div id="clientErr" class="err" style="display:none"></div>
  <form method="post" id="bookForm" novalidate>
    <label for="patient_name">Your name</label>
    <input id="patient_name" name="patient_name" required minlength="3" placeholder="e.g. Ravi Kumar">
    <label for="mobile">Mobile (10-digit)</label>
    <input id="mobile" name="mobile" required pattern="[6-9][0-9]{9}" placeholder="98XXXXXXXX">
    <label for="doctor_id">Choose doctor</label>
    <select id="doctor_id" name="doctor_id" required>
      <option value="">— Select —</option>
      <?php foreach ($doctors as $d): ?>
      <option value="<?= $d['id'] ?>"><?= h($d['name']) ?><?= $d['room'] ? ' · Room ' . h($d['room']) : '' ?></option>
      <?php endforeach; ?>
    </select>
    <p><button class="btn" type="submit">Get Token</button></p>
  </form>
  <?php endif; ?>
</section>
<script src="js/validation.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
