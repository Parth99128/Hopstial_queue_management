# Hospital Queue Management System

A complete beginner's guide to every file in this project.

This project is a small hospital token-queue website built with **PHP**, **MySQL**, **HTML**, **CSS**, and **JavaScript**, running on **XAMPP** (Apache + MySQL + PHP). Patients take a token without logging in. Hospital staff log in to call, complete, or cancel tokens. A public display board refreshes automatically.

The guide assumes you have never worked with a web project before. Read the "Big picture" section first, then use the file sections as a reference.

---

## 1. What the website does

There are two kinds of users:

| User | Needs an account? | What they can do |
|---|---|---|
| Patient (public) | No | Take a token for a doctor, watch the display board |
| Staff | Yes | See today's queue, call the next patient, complete or cancel tokens, add or delete doctors |
| Admin (a staff role) | Yes | Everything staff can do, plus create staff logins and reset passwords |

Token numbers look like `D2-004`:

- `D2` = doctor number 2
- `004` = the fourth token issued today for that doctor

Numbers restart every day, separately for each doctor. Tomorrow doctor 2 starts again at `D2-001`.

The token lifecycle is:

```
waiting -> called -> completed
   |
   +-> cancelled
```

---

## 2. The simplest way to run it

1. Start **Apache** and **MySQL** using the XAMPP Control Panel (`C:\xampp\xampp-control.exe`).
2. Make sure the `hospital-queue` folder is inside `C:\xampp\htdocs\`.
3. Open `http://localhost/hospital-queue/index.php`.

The first time only, open:

```
http://localhost/hospital-queue/install.php
```

That page creates the database and your admin account. It then locks itself.

Useful URLs:

| Page | URL |
|---|---|
| Home | `http://localhost/hospital-queue/index.php` |
| Get a token | `http://localhost/hospital-queue/book-token.php` |
| Public display board | `http://localhost/hospital-queue/queue-live.php` |
| Staff login | `http://localhost/hospital-queue/login.php` |
| Database admin (phpMyAdmin) | `http://localhost/phpmyadmin/` |

There is also a PowerShell starter script at the top of the project. It is explained below.

> If a page says it cannot reach the database, MySQL is not running, or `includes/config.php` has the wrong username/password.

---

## 3. The big picture: how one page is built

Most PHP pages follow the same pattern:

```
Browser asks for a page
        |
        v
Apache receives the request and runs PHP
        |
        v
common.php loads config.php, db.php, auth.php
        |
        v
$conn = database connection
$user = logged-in staff member (or null for a guest)
        |
        v
header.php prints the page head, navbar, ticker, and opens <main>
        |
        v
the page's own PHP runs: checks login, reads forms, queries MySQL
        |
        v
the page prints its HTML using the data it found
        |
        v
footer.php closes <main>, prints the footer, loads main.js
        |
        v
Browser receives HTML and then runs JavaScript
        |
        v
JavaScript can fetch api/live-queue.php every few seconds for live updates
```

Two important beginner ideas:

- **PHP runs on the server** before the page is sent to the browser. It can talk to MySQL.
- **JavaScript runs in the browser** after the page arrives. It cannot talk to MySQL directly, so it calls a PHP API that returns JSON.

---

## 4. Project map

```
Hopstial_queue_management/
|-- README.md                  <- this guide
|-- start-hqms.ps1             <- PowerShell helper that starts XAMPP and copies the code
|-- hqms-autostart.log         <- log written by the starter script
`-- hospital-queue/           <- the actual website
    |-- install.php
    |-- index.php
    |-- book-token.php
    |-- queue-live.php
    |-- login.php
    |-- logout.php
    |-- history.php
    |-- staff-queue.php
    |-- staff-doctors.php
    |-- staff-accounts.php
    |-- database.sql
    |-- README.md
    |-- api/
    |   `-- live-queue.php
    |-- includes/
    |   |-- config.php
    |   |-- db.php
    |   |-- auth.php
    |   |-- common.php
    |   |-- header.php
    |   `-- footer.php
    |-- css/
    |   `-- style.css
    `-- js/
        |-- main.js
        |-- validation.js
        `-- live-queue.js
```

The `.git` folder (not shown) is Git version-control data. It is not part of the website and you never need to edit it.

The website code is split into three kinds of files:

| Kind | Examples | Runs where |
|---|---|---|
| Pages | `index.php`, `login.php` | Server, then sends HTML |
| Includes | `includes/config.php` | Server, pulled into pages |
| Assets | `css/style.css`, `js/main.js` | Browser |

---

## 5. Files at the top of the project

### `start-hqms.ps1`

A Windows PowerShell helper script. It does three jobs:

1. Copies the website code from a source folder into `C:\xampp\htdocs\hospital-queue`.
2. Starts MySQL if it is not already running, waits until it answers, then starts Apache if the site is not already responding.
3. Writes what happened to `hqms-autostart.log` and prints a one-line status.

The two important settings are at the top:

```powershell
$SRC = "C:\Users\Parth\ml7code\hospital-queue"
$DST = "C:\xampp\htdocs\hospital-queue"
```

- `$SRC` is where you edit the code.
- `$DST` is where Apache serves it from.
- `robocopy ... /MIR` makes `$DST` an exact mirror of `$SRC`. Warning: files that exist only in the destination are deleted.

If your copy of the project lives somewhere else, change `$SRC` to the full path of this project's `hospital-queue` folder before running the script.

Run it from PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File .\start-hqms.ps1
```

The script can also be attached to a Windows Scheduled Task so it runs when you log in.

Beginner note: Apache serves files from `htdocs`. PHP files in your project folder are not visible to the browser until they are copied there.

### `hqms-autostart.log`

A plain text log file generated by `start-hqms.ps1`. Each line starts with a timestamp in square brackets and records a step:

```
[2026-10-17 07:36:40] === starter check (XAMPP) ===
[2026-10-17 07:36:40] code synced
[2026-10-17 07:36:41] starting xampp mysqld...
[2026-10-17 07:36:44] mysql ready=True
```

It is not used by the website. It is safe to delete when Apache and MySQL are stopped. It is useful when the starter script does not work, because it shows which step failed.

### `README.md` (this file)

The guide you are reading. The older `hospital-queue/README.md` is a shorter quick-start and viva cheat sheet. This root guide is the detailed beginner reference.

---

## 6. The website pages (`hospital-queue/`)

All of these files sit directly inside `hospital-queue/`. Most of them start by loading `includes/common.php` and `includes/header.php`. Two small exceptions: `install.php` runs before the database exists, so it loads only `config.php`, and `logout.php` needs no HTML output, so it loads only `common.php`.

### `index.php`

**Who can see it:** everyone.

**What it is:** the home page.

**What it does:**

- Counts today's tokens that are `waiting`.
- Counts today's tokens that are `called` or `completed`.
- Counts all doctors.
- Shows three big buttons: Get Token, Display Board, and (for logged-in staff) Today's Queue.
- Runs a tiny piece of JavaScript that fetches `api/live-queue.php` and shows who is being served right now.

The three number cards at the bottom of the hero section come from three SQL `COUNT(*)` queries at the top of the file.

**Beginner note:** this page is a good first file to read. It shows the standard pattern: load common, load header, query, print HTML, load footer.

### `book-token.php`

**Who can see it:** guests only. If a staff member is logged in, they are redirected to `history.php` because staff manage queues and do not take tokens.

**What it is:** the public token-booking form.

**What it does:**

1. Loads the list of doctors from the `doctors` table.
2. Shows a form asking for patient name, mobile number, and doctor.
3. Validates the input on the server:
   - Name must be at least 3 characters and contain only letters and spaces.
   - Mobile must be exactly 10 digits and start with 6, 7, 8, or 9.
   - A doctor must be selected.
4. Tidies the name with `ucwords(strtolower(...))`, so `ravi kumar` becomes `Ravi Kumar`.
5. Finds the highest token sequence already used today for that doctor.
6. Adds 1 and formats the result with `make_token_no()`.
7. Inserts a new row into `tokens` with the status `waiting`.
8. Shows the patient their token number.

This is where the daily per-doctor numbering rule lives:

```sql
SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(token_no,'-',-1) AS UNSIGNED)),0)
FROM tokens
WHERE doctor_id = ? AND booked_date = CURDATE()
```

If doctor 2 already has `D2-001`, `D2-002`, and `D2-003` today, the next token is `D2-004`.

The form also loads `js/validation.js`, which repeats the same checks in the browser for instant feedback. The PHP checks still run because browser checks can be bypassed.

### `queue-live.php`

**Who can see it:** everyone. It is designed to be left open on a waiting-room screen.

**What it is:** the public "Now Serving" display board.

**What it does:**

- Prints the page shell: a title, a "Now Serving" area, and a table for the next patients.
- Loads `js/live-queue.js`.
- The JavaScript calls `api/live-queue.php` immediately and then every 5 seconds, and repaints the board without reloading the page.

The PHP file itself contains almost no data logic. All live data comes from the API.

### `login.php`

**Who can see it:** guests. A logged-in user is redirected to `history.php`.

**What it is:** the single staff login form.

**What it does:**

1. Reads `mobile` and `password` from the submitted form.
2. Removes everything except digits from the mobile number.
3. Looks up the user by mobile using a prepared statement.
4. Checks the password against the stored bcrypt hash with `password_verify()`.
5. On success, calls `login_user()`, which stores the user in the session and regenerates the session ID, then redirects to `history.php`.
6. On failure, shows `Invalid mobile or password.` The message does not reveal which field was wrong.

The mobile number is the login ID. There are no usernames.

### `logout.php`

**Who can see it:** anyone.

**What it does:** loads `common.php`, calls `logout_user()` to destroy the session and clear the session cookie, then redirects to `index.php`.

It is only four lines long and deliberately has no HTML output.

### `history.php`

**Who can see it:** logged-in staff only. `require_login()` sends guests to `login.php`.

**What it is:** the staff home page and reporting dashboard. Login redirects here.

**What it shows:**

1. Today's totals: total tokens, waiting, serving (called), completed.
2. A per-doctor table for today: total, waiting, and done.
3. A filterable history table of all tokens:
   - Date (defaults to today)
   - Doctor (all or one)
   - Status (all, waiting, called, completed, cancelled)
   - Newest first, limited to 200 rows.

**Beginner note:** filters arrive in the URL as query-string values, for example `history.php?date=2026-10-23&doctor=2&status=waiting`. The PHP reads them from `$_GET`.

### `staff-queue.php`

**Who can see it:** logged-in staff only.

**What it is:** the working queue screen where staff move patients through the flow.

**What it does:**

- Shows everyone currently `called` today, with Complete and Cancel buttons.
- Shows everyone `waiting` today, oldest first (FIFO), with Call and Cancel buttons.
- Can filter both lists by doctor using a dropdown.
- Handles button presses with POST requests and updates the token status in MySQL:
  - Call: `waiting` -> `called`
  - Complete: `waiting` or `called` -> `completed`
  - Cancel: `waiting` or `called` -> `cancelled`

The SQL `WHERE status = ...` condition prevents impossible transitions, for example completing an already cancelled token.

### `staff-doctors.php`

**Who can see it:** logged-in staff only.

**What it is:** doctor management.

**What it does:**

- Lists every doctor with their room and how many tokens they have today.
- Adds a doctor: name must be at least 3 characters; room is optional and limited to 20 characters.
- Deletes a doctor. Because the database foreign key uses `ON DELETE CASCADE`, every token belonging to that doctor is deleted too. The confirm dialog warns about this.

This page is a good demonstration of a one-to-many relationship: one doctor has many tokens.

### `staff-accounts.php`

**Who can see it:** admins only. It calls `require_role(['admin'])`, so a normal staff member gets a 403 page.

**What it is:** staff account management.

**What it does:**

- Lists all users, admins first.
- Creates a login: name, mobile (unique), password (6+ characters), and role (`staff` or `admin`).
- Resets any user's password by entering a new one. The new password is hashed with `password_hash()`.
- Deletes a user, except the currently logged-in user. Self-deletion is blocked.

**Beginner note:** passwords are never stored as plain text. The database only stores a bcrypt hash. That is why nobody, including an admin, can "look up" an existing password.

### `install.php`

**Who can see it:** anyone who opens it, but it refuses to run again after an admin exists.

**What it is:** the one-time setup wizard.

**Why it is special:** it does **not** load `common.php`, because the database might not exist yet. It only loads `includes/config.php` for the database address.

**What it does:**

1. Tries to connect to MySQL.
2. Creates the `hospital_queue` database if it is missing.
3. Checks whether an admin user already exists. If yes, it shows "Already installed" and locks itself.
4. On a fresh install, validates the submitted admin name, mobile, and password.
5. Reads `database.sql`, splits it into separate statements at each semicolon, and runs them.
6. Inserts the first admin account with a hashed password.
7. Shows a success screen with a link to the login page.

**Warning:** only the very first admin is created here. All later accounts are created from `staff-accounts.php` after logging in.

### `database.sql`

**What it is:** the database schema. It contains no demo data.

It creates three tables:

| Table | Purpose |
|---|---|
| `users` | Staff and admin login accounts |
| `doctors` | Doctors and their room or counter |
| `tokens` | Every patient token ever issued |

Important details:

- `tokens.doctor_id` is a foreign key to `doctors.id` with `ON DELETE CASCADE`.
- `tokens` has `UNIQUE (booked_date, token_no)`, which allows `D2-004` to exist on many different days but only once per day.
- `tokens.status` is an `ENUM` limited to `waiting`, `called`, `completed`, and `cancelled`.
- Two indexes speed up the common "doctor plus status" and "doctor plus creation time" queries.
- All tables use InnoDB, which is required for foreign keys.

`install.php` runs this file. You can also import it manually through phpMyAdmin if you prefer.

### `hospital-queue/README.md`

The original short README written for the mini-project. It contains a four-minute demo script and "viva one-liners" (short explanations for an oral exam): FIFO, prepared statements, XSS escaping, password hashing, sessions, validation, and foreign keys.

This root `README.md` expands all of that into a full beginner reference. The app folder's README is still useful as a quick revision sheet.

---

## 7. The `includes/` folder

These are shared building blocks. They are not meant to be opened directly in the browser as standalone pages; they are pulled into pages with `require_once`.

### `includes/config.php`

**What it is:** the single configuration file.

**What it defines:**

| Constant | Meaning | Default |
|---|---|---|
| `DB_HOST` | MySQL server address | `localhost` |
| `DB_USER` | MySQL username | `root` |
| `DB_PASS` | MySQL password | empty string |
| `DB_NAME` | Database name | `hospital_queue` |
| `SITE_NAME` | Name shown in the browser tab | CityCare Hospital - Queue Management |
| `AVG_CONSULT_MIN` | Average consultation minutes used by reports | 8 |

Each value can be overridden by an environment variable, but on XAMPP the defaults are enough.

**When to edit it:** if your MySQL root account has a password, put it in `DB_PASS`. If you rename the database, change `DB_NAME`. If you want a different site name, change `SITE_NAME`.

### `includes/db.php`

**What it is:** the database toolkit.

It provides four functions:

1. `h($s)` - escapes text with `htmlspecialchars`. Every piece of user-provided text must be printed through this function to prevent cross-site scripting (XSS).
2. `db()` - opens one MySQL connection per request and reuses it. If the connection fails, it prints a friendly "Cannot reach the database" page instead of a raw error.
3. `status_label($s)` - turns `waiting` into `Waiting` for display.
4. `make_token_no($doctor_id, $last_seq_today)` - builds a token such as `D2-004` by adding 1 to the last sequence and padding it to three digits.

**Beginner note:** `static $conn` inside `db()` means the connection is created only once even if `db()` is called several times.

### `includes/auth.php`

**What it is:** session and permission handling.

**What it does:**

- Starts the PHP session if one is not already active.
- Sets session cookies to `HttpOnly` and `SameSite=Lax`.
- `current_user()` returns the logged-in user array or `null`.
- `is_logged_in()` returns true or false.
- `require_login()` redirects guests to `login.php` and stops the page.
- `require_role($roles)` first requires login, then checks the role and returns HTTP 403 if the role is not allowed.
- `login_user($row)` saves the user's id, name, mobile, and role in `$_SESSION` and regenerates the session ID to prevent session fixation.
- `logout_user()` clears the session data, expires the cookie, and destroys the session.

Sessions are how the server remembers you between page requests. The browser only receives a session ID cookie; the actual user data stays on the server.

### `includes/common.php`

**What it is:** the glue file. It is the first thing every page loads.

It loads, in order:

1. `config.php`
2. `db.php`
3. `auth.php`

Then it creates two variables that the page can use:

```php
$conn = db();              // the MySQL connection
$user = current_user();    // the logged-in user, or null
```

**Beginner note:** almost every page starts with:

```php
require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php';
```

`__DIR__` means "the folder this file is in", which makes the include path reliable no matter where the project is installed.

### `includes/header.php`

**What it is:** the shared top of every page. It outputs everything from `<!DOCTYPE html>` through the opening `<main>` tag.

It contains:

- The document head: character set, viewport, title, and the link to `css/style.css`.
- The sticky navigation bar with the CityCare logo.
- Different links depending on who is logged in:
  - Guests see Home, Display Board, Get Token, and Staff Login.
  - Staff see History, Display Board, Queue, Doctors, Logout, and (admins only) Accounts.
- The live ticker bar (`Loading live queue...`). It is filled in later by `js/main.js`.
- The opening `<main class="wrap">` element.

The variable `$active` holds the current file name (for example `history.php`) and is used to highlight the matching navigation link.

**Beginner note:** the header assumes `common.php` has already run, because it uses `$user` and `SITE_NAME`.

### `includes/footer.php`

**What it is:** the shared bottom of every page. It:

- Closes the `<main>` element opened by the header.
- Prints the footer with links appropriate to the current user.
- Prints the project credit line.
- Loads `js/main.js`.
- Closes `</body>` and `</html>`.

Because `main.js` is loaded here, every page automatically gets the mobile menu toggle and the live ticker.

---

## 8. The `api/` folder

### `api/live-queue.php`

**Who can call it:** anyone. It is public read-only data with no personal secrets beyond the patient names already shown on the public board.

**What it is:** a small JSON API.

It queries today's tokens and returns JSON like:

```json
{
  "now_serving": [
    { "token_no": "D1-003", "patient_name": "Ravi Kumar", "doctor": "Dr Asha Patil", "room": "R1" }
  ],
  "queue": [
    { "token_no": "D1-004", "patient_name": "Meera Joshi", "doctor": "Dr Asha Patil", "room": "R1", "status_label": "Waiting" }
  ],
  "waiting_count": 4
}
```

Rules:

- `now_serving`: tokens with status `called` from today, oldest first, maximum 5.
- `queue`: tokens with status `waiting` from today, oldest first (FIFO), maximum 20.
- `waiting_count`: total number of waiting tokens today.

Three places consume this API:

1. `queue-live.php` via `js/live-queue.js` (full board, every 5 seconds).
2. Every page via `js/main.js` (small ticker, every 8 seconds).
3. `index.php` (one-time "Now serving" fetch).

**Beginner note:** an API is just a URL that returns data instead of a full web page. This one returns JSON, which JavaScript can turn into objects.

---

## 9. The `css/` folder

### `css/style.css`

**What it is:** the only stylesheet in the project. Every page and component gets its look from this one file.

It defines:

- CSS variables (`--brand`, `--ink`, `--danger`, etc.) for consistent colors.
- Layout with Flexbox (`.nav-inner`, `.hero`) and CSS Grid (`.grid-2`, `.grid-3`, `.grid-4`).
- The sticky navbar and sticky live ticker (`position: sticky`).
- Reusable components: cards, buttons, pills, tables, forms, error and success boxes, badges, steps.
- Responsive rules for tablets (`max-width: 860px`) and phones (`max-width: 560px`), including a hidden fourth table column on small screens.
- Accessibility touches such as visible focus outlines and reduced-motion support.

**When to edit it:** colors, spacing, fonts, layout, or responsive behavior.

**Beginner note:** CSS classes are referenced from PHP files. If you rename a class here, search the PHP files for that class name.

---

## 10. The `js/` folder

### `js/main.js`

Loaded on every page by `footer.php`.

It does two things after the page loads:

1. Wires up the mobile hamburger button (`#navToggle`) to toggle the navigation menu (`.open` class).
2. Fetches `api/live-queue.php`, fills the ticker with the current "Now Serving" tokens and waiting count, and repeats every 8 seconds.

If the fetch fails, it shows `Live queue unavailable - is MySQL running?`.

### `js/validation.js`

Loaded only on `book-token.php` and `login.php`.

It is the browser-side copy of the server rules:

- Booking form: name must be 3+ letters, mobile must be 10 digits starting 6-9, and a doctor must be selected. Errors are shown in the `#clientErr` box.
- Login form: mobile must be 10 digits starting 6-9.

The same rules are enforced again in PHP. This is intentional: JavaScript makes the form feel fast, but PHP is the real security boundary because users can disable JavaScript or send requests directly.

### `js/live-queue.js`

Loaded only on `queue-live.php`.

It:

1. Waits for the page to finish loading.
2. Fetches `api/live-queue.php`.
3. Replaces the "Now Serving" area with colored token pills.
4. Rebuilds the "Next in line" table from the `queue` array.
5. Shows an "Updated HH:MM:SS" timestamp.
6. Repeats every 5 seconds with `setInterval`.

If the API cannot be reached, the board shows `Board unreachable - is MySQL running?` instead of freezing on old data.

**Beginner note:** this file demonstrates the Fetch API, JSON, and DOM updates without a page reload.

---

## 11. The database explained

The database is named `hospital_queue` and has exactly three tables.

### `users`

Stores staff logins.

| Column | Type | Meaning |
|---|---|---|
| `id` | INT, auto-increment | Primary key |
| `name` | VARCHAR(100) | Full name |
| `mobile` | VARCHAR(15), UNIQUE | Login ID |
| `password_hash` | VARCHAR(255) | Bcrypt hash, never the plain password |
| `role` | ENUM('staff','admin') | Permission level |
| `created_at` | TIMESTAMP | When the account was created |

### `doctors`

Stores doctors.

| Column | Type | Meaning |
|---|---|---|
| `id` | INT, auto-increment | Primary key, also used in token numbers |
| `name` | VARCHAR(100) | Doctor's name |
| `room` | VARCHAR(20) | Room or counter shown on the board |

### `tokens`

Stores every patient visit.

| Column | Type | Meaning |
|---|---|---|
| `id` | INT, auto-increment | Primary key |
| `token_no` | VARCHAR(30) | For example `D2-004` |
| `booked_date` | DATE | The day the token belongs to |
| `patient_name` | VARCHAR(100) | Patient's name |
| `mobile` | VARCHAR(15) | Patient's mobile |
| `doctor_id` | INT | Foreign key to `doctors.id` |
| `status` | ENUM | `waiting`, `called`, `completed`, or `cancelled` |
| `created_at` | TIMESTAMP | Exact creation time |

Relationships:

```
users       (standalone login accounts)

doctors 1 -------- many tokens
        ON DELETE CASCADE
```

If a doctor is deleted, all of that doctor's tokens are deleted automatically.

Why the unique key is `(booked_date, token_no)` and not just `token_no`:

- `D1-001` may exist on Monday, Tuesday, and Wednesday.
- It may exist only once on any single day.
- This matches the rule that numbering restarts daily.

You can inspect all of this in phpMyAdmin at `http://localhost/phpmyadmin/`.

---

## 12. How the files work together: four walkthroughs

### A. A patient takes a token

1. The browser requests `book-token.php`.
2. `common.php` loads config, database, and session code.
3. `header.php` prints the top of the page.
4. `book-token.php` reads the doctors table and prints the form.
5. `footer.php` prints the bottom and loads `main.js`.
6. `validation.js` checks the fields in the browser as the patient submits.
7. The form POSTs back to the same `book-token.php`.
8. PHP validates again, computes the next per-doctor number, and inserts a row into `tokens`.
9. The page shows the new token number.

### B. Staff call the next patient

1. Staff logs in through `login.php`; the session stores their identity.
2. `staff-queue.php` checks `require_login()`.
3. Staff clicks Call on a waiting token.
4. The page POSTs `token_id` and `action=call`.
5. PHP updates that token's status to `called`.
6. The page reloads the lists from MySQL.
7. Any open `queue-live.php` board fetches the API within 5 seconds and shows the patient under "Now Serving".

### C. Staff complete a token

1. Staff clicks Complete on a called token.
2. PHP updates the status to `completed`, but only if the token is currently `waiting` or `called`.
3. The token leaves the live board and appears in history as completed.

### D. First-time installation

1. The browser requests `install.php`.
2. It loads only `config.php`, connects to MySQL, and creates the database if needed.
3. It runs `database.sql` to build the three tables.
4. It creates the first admin with a hashed password.
5. Later visits detect the admin and show "Already installed", so the page cannot be used to overwrite the system.

---

## 13. Which file should I edit?

| I want to... | Edit this file |
|---|---|
| Change the site name or database login | `includes/config.php` |
| Change colors, fonts, or layout | `css/style.css` |
| Change the token format (`D2-004`) | `make_token_no()` in `includes/db.php` |
| Change the display board refresh speed | `js/live-queue.js` (currently 5 seconds) |
| Change the ticker refresh speed | `js/main.js` (currently 8 seconds) |
| Change form validation rules | `js/validation.js` and the matching PHP page |
| Change what appears in the navbar | `includes/header.php` |
| Change footer links | `includes/footer.php` |
| Add a new public page | Create a new `.php` file in `hospital-queue/`, load common/header/footer, and add a link in `header.php` |
| Add a new staff-only page | Same as above, plus `require_login()` after the header |
| Add a new admin-only page | Same as above, plus `require_role(['admin'])` |
| Change the database structure | Edit `database.sql`, then apply the change in phpMyAdmin or reinstall on a test database |
| Reset a forgotten staff password | Log in as an admin and use `staff-accounts.php` |
| Inspect or edit raw data | phpMyAdmin |

---

## 14. Security ideas used in this project

This is a learning project, but it already follows several good habits:

- **SQL injection protection:** every query that includes user input uses a prepared statement with `?` placeholders and `bind_param`. A few numeric IDs are cast to `(int)` before being placed in SQL.
- **XSS protection:** every echoed user value goes through `h()`, which calls `htmlspecialchars`.
- **Password safety:** passwords are stored with `password_hash()` (bcrypt) and checked with `password_verify()`. Plain passwords are never saved.
- **Session safety:** the session ID is regenerated at login, and the session cookie is `HttpOnly` and `SameSite=Lax`.
- **Authorization:** staff pages call `require_login()`; the accounts page calls `require_role(['admin'])`.
- **Two-layer validation:** JavaScript checks for speed, PHP checks for safety.
- **Friendly database failure:** `db.php` shows a helpful message instead of exposing a stack trace.

Things a real production system would add:

- CSRF tokens on forms.
- HTTPS.
- Rate limiting on login and booking.
- Stronger password rules.
- Audit logs.
- Backups.
- Hiding patient names on public screens (privacy).

Never put real passwords or personal data into a public Git repository.

---

## 15. Troubleshooting for beginners

### "Cannot reach the database"

- Open the XAMPP Control Panel and start **MySQL**.
- Check `includes/config.php`: the username should usually be `root` and the password should usually be empty on XAMPP.
- If MySQL is running on a different port, `DB_HOST` may need to be `localhost:3307` or similar.

### The website does not load at all

- Start **Apache** in XAMPP.
- Check that the project is in `C:\xampp\htdocs\hospital-queue`.
- Open `http://localhost/` first. If that fails, Apache is not running or another program is using port 80.

### `install.php` says "Already installed"

That is normal. An admin account already exists. Go to `login.php` instead. The page locks itself on purpose.

### I forgot the admin password

1. If another admin exists, log in as that admin and reset the password in `staff-accounts.php`.
2. If no one can log in, reset it directly in the database with phpMyAdmin, or reinstall on a fresh test database. Passwords cannot be recovered because they are hashed.

### I changed the code but the website looks the same

There are two copies of the project:

- The folder you edit (the workspace).
- The copy Apache serves (`C:\xampp\htdocs\hospital-queue`).

Run `start-hqms.ps1`, or copy the changed files into `htdocs`. Also do a hard refresh in the browser (Ctrl+F5) to bypass cached CSS and JavaScript.

### The page is blank or shows a PHP error

- Check the Apache error log at `C:\xampp\apache\logs\error.log`.
- A blank page often means a PHP syntax error. Make sure every statement ends with a semicolon and every `<?php` has a matching `?>` where needed.
- PHP files in this project mix PHP and HTML. That is normal.

### The live board does not update

- Confirm MySQL is running.
- Open `http://localhost/hospital-queue/api/live-queue.php` directly. You should see JSON, not an error page.
- Check the browser's developer console (F12) for JavaScript errors.

### Port 80 or 3306 is already in use

Another Apache/MySQL installation or service is running. Stop it, or change XAMPP's ports in its configuration. If you change Apache's port, the URLs will include that port, for example `http://localhost:8080/hospital-queue/`.

---

## 16. Glossary

| Term | Simple meaning |
|---|---|
| Apache | The web server that receives browser requests and runs PHP files |
| MySQL | The database server that stores users, doctors, and tokens |
| PHP | The server-side language used to build pages and talk to MySQL |
| XAMPP | A package that installs Apache, MySQL, PHP, and phpMyAdmin together |
| htdocs | The folder Apache serves websites from |
| localhost | This computer |
| Server-side | Code that runs on the server before the page is sent |
| Client-side | Code that runs in the browser after the page arrives |
| Session | Server-side memory of who is logged in |
| Cookie | A small value the browser sends back on every request; here it carries the session ID |
| Query | A request for data written in SQL |
| Prepared statement | A safe query template where user values are bound separately, preventing SQL injection |
| Bcrypt | A slow, salted password hash designed for storing passwords |
| JSON | A text format for structured data, easy for JavaScript to read |
| API | A URL that returns data rather than a full page |
| Fetch | A browser function for requesting a URL with JavaScript |
| DOM | The browser's object representation of the page, which JavaScript can change |
| Responsive | A layout that adapts to different screen sizes |
| Foreign key | A database rule linking one table's row to another table's row |
| CASCADE | Automatically delete child rows when the parent row is deleted |
| FIFO | First In, First Out; the oldest waiting token is served first |
| CRUD | Create, Read, Update, Delete; the four basic data operations |
| Role | A permission level; here `staff` or `admin` |
| Redirect | Telling the browser to load a different URL |

---

## 17. A good reading order

If you want to learn the codebase rather than just run it, read these files in this order:

1. `includes/config.php` - the smallest file; see the settings.
2. `includes/db.php` - learn the helper functions.
3. `includes/common.php` - see how the pieces load.
4. `includes/auth.php` - learn sessions and guards.
5. `index.php` - the simplest full page.
6. `book-token.php` - a real form, validation, and insert.
7. `staff-queue.php` - status updates and FIFO.
8. `api/live-queue.php` - how JSON is produced.
9. `js/live-queue.js` - how the browser consumes JSON.
10. `history.php` - filtering and reports.
11. `staff-doctors.php` and `staff-accounts.php` - management screens.
12. `install.php` and `database.sql` - how the database is created.
13. `css/style.css` - the visual layer.

That order goes from "what the app needs" to "what the user sees", which is usually the easiest way to understand a web application.
