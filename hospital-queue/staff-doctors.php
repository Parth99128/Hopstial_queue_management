<?php require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php'; require_login();
// Doctors — staff can ADD and DELETE. Deleting removes its tokens too (CASCADE).
$note = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_doctor'])) {
        $n = ucwords(strtolower(trim($_POST['dname'] ?? '')));
        $room = substr(strip_tags(trim($_POST['droom'] ?? '')), 0, 20);
        if (strlen($n) < 3) $note = '<div class="err">Name 3+ letters.</div>';
        else { $st = $conn->prepare("INSERT INTO doctors (name,room) VALUES (?,?)"); $st->bind_param('ss', $n, $room); $st->execute(); $note = '<div class="ok">Doctor added.</div>'; }
    } elseif (isset($_POST['del_doctor'])) {
        $conn->query("DELETE FROM doctors WHERE id=" . (int)$_POST['del_doctor']);
        $note = '<div class="ok">Doctor deleted (their tokens went too).</div>';
    }
}
$docs = $conn->query("SELECT d.*, (SELECT COUNT(*) FROM tokens t WHERE t.doctor_id=d.id AND DATE(t.created_at)=CURDATE()) today FROM doctors d ORDER BY d.name")->fetch_all(MYSQLI_ASSOC);
?>
<section class="card">
  <h1>Doctors</h1><?= $note ?>
  <table><thead><tr><th>Name</th><th>Room</th><th>Tokens today</th><th></th></tr></thead><tbody>
  <?php if (!$docs): ?><tr><td colspan="4" class="muted">No doctors yet — add the first one below.</td></tr><?php endif; ?>
  <?php foreach ($docs as $d): ?><tr><td><b><?= h($d['name']) ?></b></td><td><?= h($d['room']) ?></td><td><?= (int)$d['today'] ?></td>
    <td><form method="post" onsubmit="return confirm('Delete <?= h($d['name']) ?> and ALL their tokens?')"><input type="hidden" name="del_doctor" value="<?= $d['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form></td></tr>
  <?php endforeach; ?></tbody></table>
  <h3>Add doctor</h3>
  <form method="post" class="grid grid-2">
    <div><label for="dname">Doctor name</label><input id="dname" name="dname" required placeholder="Dr. Asha Patil"></div>
    <div><label for="droom">Room / counter</label><input id="droom" name="droom" maxlength="20" placeholder="R1"></div>
    <p><button class="btn" type="submit" name="add_doctor" value="1">Add doctor</button></p>
  </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
