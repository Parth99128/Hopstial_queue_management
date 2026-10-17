# Hospital Queue System — Web Technologies Mini-Project (102506)

**What it does:** Patients register without logging in and get a token number for their chosen doctor. Staff log in and use the queue page to Call, Complete or Cancel patients. A public display board shows “Now Serving” and the next tokens, refreshing every 5 seconds. Staff can add and delete doctors. Token numbers restart each day, separately for each doctor (`D2-004` = doctor 2, 4th token today). Validation runs in **both** the JS forms and the PHP handlers (same rules twice — JS for speed, PHP for safety since browser checks can be bypassed).

**Stack:** HTML + CSS + JS + PHP + MySQL on **XAMPP** · **Data:** 100% real, zero seeding

## 1. Run
- Start Apache + MySQL (`C:\xampp\xampp-control.exe`, or run `start-hqms.ps1` — it also syncs code to htdocs and auto-runs at logon).
- FIRST TIME ONLY: `http://localhost/hospital-queue/install.php` → creates tables + YOUR admin account, then locks itself.
- App: `http://localhost/hospital-queue/index.php`
- Code rule: edit ONLY in `C:\Users\Parth\ml7code\hospital-queue` (the starter copies it to htdocs).

## 2. Pages (10 — only what the core idea needs)
- `index.php` — landing: Get Token / Display Board / today's counts
- `book-token.php` — PUBLIC, no login: name + mobile + doctor → token
- `queue-live.php` — PUBLIC display board (Now Serving + next, auto-refresh 5s)
- `login.php` — THE staff login · `logout.php`
- `history.php` — staff HOME: today dashboard + per-doctor details + filterable token history (replaces Home in staff nav; login lands here)
- `staff-queue.php` — staff only: Call / Complete / Cancel per doctor queue
- `staff-doctors.php` — staff only: add + delete doctors
- `staff-accounts.php` — admin only: create + delete staff logins
- `install.php` — one-time setup wizard (self-locking)
- Folders: `includes/` (common/config/db/auth/header/footer) · `api/` (1 JSON feed) · `css/` (1 stylesheet) · `js/` (3 scripts)
- Every page = `require common.php` (DB + user) → `require header.php` → guard if walled → handle POST → load data → HTML → `require footer.php`

## 3. Demo (4 minutes)
1. `install.php` (fresh) → login as admin → `staff-doctors.php`: add 2 doctors.
2. New tab, logged OUT: `book-token.php` → book 2 tokens for doctor 1 (`D1-001`, `D1-002`), 1 for doctor 2 (`D2-001`) — numbering restarts per doctor.
3. `queue-live.php`: board shows them waiting, auto-refreshing.
4. As staff: `staff-queue.php` → Call `D1-001` → board flips to Now Serving → Complete it → Cancel another.
5. Delete a doctor → its tokens go too (foreign key CASCADE — viva point).

## 4. Viva one-liners
- Queue rule: FIFO per doctor; daily restart = `MAX(today's seq for this doctor)` + 1, formatted `D{id}-NNN` by `make_token_no()`; DB enforces `UNIQUE(booked_date, token_no)` so numbers repeat across days.
- Injection: every query is a prepared statement (`?` + `bind_param`).
- XSS: every echo wrapped in `h()` (`htmlspecialchars`).
- Passwords: `password_hash`/`password_verify` (bcrypt, salted — never plain).
- Sessions: server-side `$_SESSION`, `HttpOnly; SameSite=Lax` cookie, regenerate id on login; logged-out staff pages 302 to login.
- JS + PHP validation mirror each other (`validation.js` ↔ book-token/login handlers).
- FK: deleting a doctor CASCADEs its tokens; deleting a user is admin-only with self-delete blocked.
