<?php require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php';
// Display Board — public screen. Refreshes every 5 seconds, no reload.
?>
<section class="card">
  <span class="badge">AUTO-REFRESH 5s</span>
  <h1>Now Serving</h1>
  <div id="nowServing"><span class="muted">Loading…</span></div>
  <p class="muted" id="boardTime"></p>
  <h2>Next in line</h2>
  <table><thead><tr><th>Token</th><th>Patient</th><th>Doctor</th><th>Room</th></tr></thead>
  <tbody id="liveBoard"><tr><td colspan="4">Loading…</td></tr></tbody></table>
</section>
<script src="js/live-queue.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
