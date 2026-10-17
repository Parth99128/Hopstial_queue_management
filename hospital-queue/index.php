<?php require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php';
// Home: 3 big actions + today's counts. Nothing else — keep it simple.
$waiting = $conn->query("SELECT COUNT(*) c FROM tokens WHERE DATE(created_at)=CURDATE() AND status='waiting'")->fetch_assoc()['c'] ?? 0;
$served  = $conn->query("SELECT COUNT(*) c FROM tokens WHERE DATE(created_at)=CURDATE() AND status IN ('called','completed')")->fetch_assoc()['c'] ?? 0;
$docs    = $conn->query("SELECT COUNT(*) c FROM doctors")->fetch_assoc()['c'] ?? 0;
?>
<section class="hero">
  <div class="hero-text">
    <h1 style="margin:.3em 0">Take a token. <span style="color:#0e63c9">Skip the line.</span></h1>
    <p class="muted">Pick your doctor, get a token number, and walk in when the display board calls it. Numbers restart every day for each doctor.</p>
    <?php if ($user): ?>
    <p><a class="btn" href="staff-queue.php">Open Today's Queue</a> <a class="btn btn-ghost" href="queue-live.php">Display Board</a></p>
    <?php else: ?>
    <p><a class="btn" href="book-token.php">Get Token</a> <a class="btn btn-ghost" href="queue-live.php">Display Board</a></p>
    <?php endif; ?>
    <div class="grid grid-3" style="margin-top:14px">
      <div class="card"><div class="stat"><?= (int)$waiting ?></div><div class="muted">Waiting now</div></div>
      <div class="card"><div class="stat"><?= (int)$served ?></div><div class="muted">Served today</div></div>
      <div class="card"><div class="stat"><?= (int)$docs ?></div><div class="muted">Doctors</div></div>
    </div>
  </div>
  <div class="hero-card card">
    <span class="badge">LIVE</span>
    <h3>Now serving</h3>
    <div id="nowServingHome" class="muted">Loading…</div>
    <h3>How it works</h3>
    <div class="steps" style="grid-template-columns:repeat(3,1fr)">
      <div class="step"><b>1</b>Get token</div>
      <div class="step"><b>2</b>Watch board</div>
      <div class="step"><b>3</b>Walk in</div>
    </div>
  </div>
</section>
<script>
// Home ticker: how many are waiting right now (Fetch + JSON)
fetch('api/live-queue.php').then(r => r.json()).then(d => {
  const now = (d.now_serving || []).slice(0, 3).map(x => x.token_no + ' (' + x.doctor + ')').join('  •  ') || 'Nobody is being served right now';
  document.getElementById('nowServingHome').innerHTML = '<b>' + now + '</b><br>Waiting: ' + (d.waiting_count ?? 0);
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
