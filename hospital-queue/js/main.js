// main.js — mobile nav toggle + the small live ticker on every page.
document.addEventListener('DOMContentLoaded', () => {
  const t = document.getElementById('navToggle'), l = document.getElementById('navLinks');
  if (t && l) t.addEventListener('click', () => l.classList.toggle('open'));

  const ticker = document.getElementById('ticker');
  if (ticker) {
    const load = () => fetch('api/live-queue.php')
      .then(r => r.json())
      .then(d => {
        const now = (d.now_serving || []).slice(0, 3)
          .map(x => `${x.token_no} (${x.doctor})`).join('  •  ') || 'No patient being served';
        ticker.textContent = `NOW SERVING: ${now}   |   Waiting: ${d.waiting_count ?? 0}`;
      })
      .catch(() => { ticker.textContent = 'Live queue unavailable — is MySQL running?'; });
    load();
    setInterval(load, 8000);
  }
});
