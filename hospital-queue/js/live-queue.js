// live-queue.js — display board: re-fetch JSON every 5s, repaint, no reload.
// (Practical 10 Fetch + Practical 11 DOM.)
document.addEventListener('DOMContentLoaded', () => {
  const board = document.getElementById('liveBoard');
  if (!board) return;
  const load = () => fetch('api/live-queue.php')
    .then(r => r.json())
    .then(d => {
      document.getElementById('nowServing').innerHTML =
        (d.now_serving || []).map(x => `<span class="pill called"><b>${x.token_no}</b> → ${x.doctor} (Room ${x.room})</span>`).join(' ') || '<span class="muted">Nobody called right now.</span>';
      board.innerHTML = (d.queue || []).length
        ? d.queue.map(t => `<tr><td><b>${t.token_no}</b></td><td>${t.patient_name}</td><td>${t.doctor}</td><td>${t.room}</td></tr>`).join('')
        : `<tr><td colspan="4">No one waiting — great!</td></tr>`;
      document.getElementById('boardTime').textContent = 'Updated ' + new Date().toLocaleTimeString();
    })
    .catch(() => { board.innerHTML = `<tr><td colspan="4">Board unreachable — is MySQL running?</td></tr>`; });
  load();
  setInterval(load, 5000);   // the "every 5 seconds" rule
});
