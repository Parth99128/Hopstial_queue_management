// validation.js — FRONTEND copy of the server rules (Practical 7).
// The SAME checks run again in PHP (book-token.php, login.php) because
// browser checks can be bypassed. JS = speed, PHP = safety.
function validMobile(m) { return /^[6-9]\d{9}$/.test((m || '').trim()); }

document.addEventListener('DOMContentLoaded', () => {
  // Booking form: name + mobile + chosen doctor
  const book = document.getElementById('bookForm');
  if (book) book.addEventListener('submit', (e) => {
    const errs = [];
    if (!/^[A-Za-z ]{3,}$/.test(book.patient_name.value.trim())) errs.push('Name must be 3+ letters.');
    if (!validMobile(book.mobile.value)) errs.push('Mobile must be 10 digits starting 6-9.');
    if (!book.doctor_id.value) errs.push('Choose a doctor.');
    const box = document.getElementById('clientErr');
    if (errs.length) { e.preventDefault(); box.innerHTML = errs.join('<br>'); box.style.display = 'block'; }
  });

  // Staff login form: just the mobile format (password is checked server-side)
  const login = document.getElementById('loginForm');
  if (login && login.mobile) login.addEventListener('submit', (e) => {
    if (!validMobile(login.mobile.value.trim())) { e.preventDefault(); alert('Enter a valid 10-digit mobile number.'); }
  });
});
