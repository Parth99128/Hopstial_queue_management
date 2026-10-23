# Viva Preparation Guide

**Project:** Hospital Queue Management System (PHP + MySQL on XAMPP)

This guide is for the oral and practical viva. It explains the HTML tags, CSS rules, JavaScript, PHP, and SQL used in this exact project, plus the questions an examiner is likely to ask and the small edits you may be asked to make.

Read sections 1-2 first, then revise the technology you are least confident about. Section 11 is the practical lab.

---

## 1. The project in 60 seconds

- **Patients** open `book-token.php`, enter name + mobile + doctor, and get a token like `D2-004`.
- **Staff** log in at `login.php` and use `staff-queue.php` to Call, Complete, or Cancel tokens.
- **The public board** `queue-live.php` refreshes every 5 seconds and shows "Now Serving" and the waiting list.
- **Numbers restart daily per doctor.** `D2-004` means doctor 2, fourth token today.
- **The queue is FIFO** (First In, First Out) per doctor.
- **Admins** can also create staff accounts and reset passwords in `staff-accounts.php`.
- **Data lives in MySQL** in the `hospital_queue` database with three tables: `users`, `doctors`, `tokens`.

Architecture:

```
Browser (HTML + CSS + JS)
        |
        | HTTP request
        v
Apache + PHP  --->  MySQL (hospital_queue)
        |
        | HTML or JSON response
        v
Browser
```

JavaScript never talks to MySQL directly. It calls `api/live-queue.php`, which is PHP, and that PHP talks to MySQL.

---

## 2. The 5-minute demo flow

Use this order if asked to demonstrate:

1. Open `http://localhost/hospital-queue/index.php`.
2. As a guest, take a token in `book-token.php`. Note the number.
3. Open `queue-live.php` in another tab. It shows the token as waiting.
4. Log in as staff.
5. Open `staff-queue.php`, click **Call** on that token.
6. Switch to the board tab. Within 5 seconds it shows the token under **Now Serving**.
7. Click **Complete**. The token disappears from the live board and appears in `history.php`.
8. Add a doctor in `staff-doctors.php`, then book a token for that doctor and show that numbering starts at `D{id}-001`.
9. Delete the doctor and explain `ON DELETE CASCADE`.

Golden sentence: "PHP runs on the server, MySQL stores the data, JavaScript polls a PHP API to update the board without reloading."

---

## 3. HTML

### 3.1 The page skeleton

Every page is produced by `includes/header.php` + the page file + `includes/footer.php`:

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CityCare Hospital - Queue Management</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="navbar"> ... </header>
  <div class="ticker" id="ticker">Loading live queue...</div>
  <main class="wrap">
    <!-- page content here -->
  </main>
  <footer class="footer"> ... </footer>
  <script src="js/main.js"></script>
</body>
</html>
```

### 3.2 Every HTML tag used in this project

| Tag | Where it is used | Why it is used |
|---|---|---|
| `<!DOCTYPE html>` | header.php, install.php | Tells the browser this is HTML5 |
| `<html lang="en">` | header.php, install.php | Root element; `lang` declares the page language |
| `<head>` | header.php, install.php | Holds metadata, title, CSS link |
| `<meta>` | header.php, install.php | Character encoding and responsive viewport |
| `<title>` | header.php, install.php | Text shown on the browser tab |
| `<link>` | header.php, install.php | Connects the external stylesheet |
| `<body>` | header.php, install.php | Visible page content |
| `<header>` | header.php | Semantic top bar containing the navbar |
| `<nav>` | header.php | Group of navigation links |
| `<main>` | header.php | The main content area (one per page) |
| `<section>` | Most pages | Groups related content inside a card |
| `<footer>` | footer.php | Bottom information area |
| `<div>` | Everywhere | Generic block container for layout |
| `<span>` | Many pages | Generic inline container for a word or pill |
| `<h1>` | Every page | Main heading |
| `<h2>`, `<h3>`, `<h4>` | Pages, footer | Subheadings in order |
| `<p>` | Everywhere | Paragraph of text |
| `<a>` | Navbar, footer, pages | Hyperlink to another page |
| `<b>` | Tables, cards | Bold text (visual only) |
| `<small>` | Tables, labels | Smaller secondary text |
| `<br>` | install.php, tables | Line break |
| `<code>` | install.php | Shows a database name as code |
| `<form>` | Booking, login, filters, actions | Collects and submits data |
| `<label>` | All forms | Describes an input; `for` links it to the input `id` |
| `<input>` | All forms | Text, password, date, and hidden fields |
| `<select>` + `<option>` | Booking, filters, accounts | Dropdown list |
| `<button>` | Forms, navbar | Submits a form or runs an action |
| `<table>` | History, queue, doctors, accounts, board | Tabular data |
| `<thead>` | Table pages | Header row group |
| `<tbody>` | Table pages | Body rows group |
| `<tr>` | Tables | Table row |
| `<th>` | Tables | Header cell |
| `<td>` | Tables | Data cell |
| `<script>` | Footer, specific pages, index | Loads JavaScript or holds an inline script |

Not used in this project: `<img>`, `<ul>`, `<ol>`, `<li>`, `<article>`, `<aside>`, `<video>`, `<iframe>`. The design uses CSS shapes and text only.

### 3.3 Every important HTML attribute used

| Attribute | Example from the project | Meaning |
|---|---|---|
| `lang` | `<html lang="en">` | Language of the document |
| `charset` | `<meta charset="UTF-8">` | Text encoding (supports the rupee sign, etc.) |
| `name="viewport"` | `content="width=device-width, initial-scale=1.0"` | Makes the layout responsive on phones |
| `class` | `class="card"` or `class="btn btn-sm btn-danger"` | Reusable CSS styling; multiple classes allowed |
| `id` | `id="liveBoard"` | Unique identifier for JavaScript or a label |
| `href` | `href="staff-queue.php"` | Link destination |
| `src` | `<script src="js/main.js">` | External file to load |
| `rel` | `rel="stylesheet"` | Relationship of the linked file |
| `for` | `<label for="mobile">` | Connects the label to `id="mobile"` |
| `name` | `name="patient_name"` | Key used in `$_POST` / `$_GET` |
| `type` | `type="password"`, `type="date"`, `type="hidden"` | Kind of input |
| `value` | `value="<?= $t['id'] ?>"` | Initial or submitted value |
| `placeholder` | `placeholder="98XXXXXXXX"` | Hint text, not a value |
| `required` | `<input required>` | Browser must not submit it empty |
| `minlength` / `maxlength` | `minlength="3"`, `maxlength="20"` | Minimum / maximum characters |
| `pattern` | `pattern="[6-9][0-9]{9}"` | Browser-side format check via regex |
| `inputmode` | `inputmode="numeric"` | Shows a numeric keyboard on mobile |
| `method` | `method="post"`, `method="get"` | How the form data is sent |
| `novalidate` | `<form ... novalidate>` | Disables native browser validation (custom JS is used) |
| `selected` | `<option ... selected>` | Pre-selects a dropdown option |
| `colspan` | `colspan="5"` | Makes one cell span five columns |
| `style` | `style="display:inline"` | Small one-off inline style |
| `onclick` | `onclick="return confirm('Cancel this token?')"` | Inline confirmation before an action |
| `onsubmit` | `onsubmit="return confirm(...)"` | Inline confirmation before form submission |
| `aria-label` | `<button ... aria-label="Menu">` | Accessible name for the hamburger button |

### 3.4 Forms: GET vs POST in this project

The project uses both:

| Form | Method | Why |
|---|---|---|
| Booking form (`book-token.php`) | POST | Creates data; should not appear in the URL |
| Login form (`login.php`) | POST | Password must not appear in the URL |
| Staff Call/Complete/Cancel | POST | Changes data |
| Add/delete doctor | POST | Changes data |
| Create/reset/delete account | POST | Changes data |
| History filters | GET | Read-only; the URL can be bookmarked |
| Staff queue doctor filter | GET | Read-only |

When `action` is omitted, the form submits back to the **same page**. That is why `book-token.php` handles both displaying the form and processing it:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // process the submitted values
}
```

Important: GET and POST are not security encryption. HTTPS provides encryption. POST only keeps values out of the URL.

### 3.5 HTML questions you should be able to answer

1. **What is `<!DOCTYPE html>`?** It tells the browser to use modern HTML5 rules.
2. **Difference between `<div>` and `<section>`?** Both are block containers, but `<section>` is semantic: it represents a themed section of content. `<div>` has no meaning.
3. **Why use `<header>`, `<nav>`, `<main>`, `<footer>`?** They describe the purpose of each region, which helps accessibility, SEO, and readability.
4. **Difference between `<b>` and `<strong>`?** `<b>` is visual bold with no extra importance; `<strong>` means important. This project uses `<b>`.
5. **Difference between `id` and `class`?** `id` must be unique on the page and is used by JavaScript/labels. `class` can repeat and is mainly for CSS.
6. **Why does a `<label>` need `for`?** It links the label to the input's `id`, so clicking the label focuses the input and screen readers announce it correctly.
7. **Difference between `name` and `id`?** `name` is the key sent to PHP (`$_POST['mobile']`). `id` is for the browser, CSS, labels, and JavaScript.
8. **Why is `<input type="hidden">` used?** To send `token_id` or `user_id` with the form without showing it to the user.
9. **Why do buttons have `name="action" value="call"`?** When clicked, the button's name/value pair is submitted, so PHP can detect which action was chosen.
10. **What does `required` do?** Prevents submitting an empty field, using the browser's built-in validation.
11. **What does `pattern` do?** Checks the value against a regular expression in the browser, e.g. `[6-9][0-9]{9}` for a 10-digit mobile starting with 6-9.
12. **Why is `novalidate` on the booking form?** The project uses its own JavaScript error box instead of the browser's popup. PHP still validates everything.
13. **GET vs POST?** GET puts data in the URL and is for reading/filtering. POST puts data in the request body and is for creating/changing data, login, and sensitive values.
14. **What is `colspan`?** Makes one table cell cover several columns, used for "No records" messages.
15. **Why `thead` and `tbody`?** They separate header rows from data rows, improve semantics, and allow CSS such as `tbody tr:hover`.
16. **`th` vs `td`?** `th` is a header cell (bold, centered by default, announced as a header); `td` is a data cell.
17. **Why `meta viewport`?** It makes the page width follow the device width, which is required for responsive CSS.
18. **Why `&amp;`, `&nbsp;`, `&#9776;`?** They are HTML entities: an ampersand, a non-breaking space, and the hamburger menu character.
19. **Why put `<script>` at the end of `<body>`?** The HTML exists before the script runs, so `getElementById` can find the elements. `DOMContentLoaded` also protects this.
20. **What is `aria-label`?** A text label for assistive technology when the button has no visible text (the hamburger button shows only a symbol).
21. **What is `<code>`?** Inline text displayed in a monospace style to indicate code, such as a database name.
22. **Why is the stylesheet external?** One file styles all pages, is cached by the browser, and is easier to maintain than repeating `<style>` blocks.
23. **Why can `style="..."` be a problem?** It mixes structure and presentation and cannot be reused; the project uses it only for tiny one-off tweaks.
24. **What does `<main>` mean?** The unique main content of the document; it should not include the navbar or footer.
25. **How does the page show the logged-in user's name?** `header.php` prints `<?= h($user['name']) ?>` inside the Logout button.

### 3.6 Practical HTML edits

**Add a line to the footer:** edit `includes/footer.php`, inside `.foot-grid`, e.g.:

```html
<p>Open Monday to Saturday, 9 AM to 6 PM.</p>
```

**Add a navbar link:** edit `includes/header.php` inside `<nav class="nav-links">`:

```html
<a class="<?= $active === 'about.php' ? 'active' : '' ?>" href="about.php">About</a>
```

**Add a table column:** add one `<th>` and one matching `<td>` in every row of that table. For example, the history table already loads `d.room`, so:

```php
<th>Room</th>
...
<td><?= h($t['room']) ?></td>
```

**Add a new page:** create `hospital-queue/about.php`:

```php
<?php require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php';
?>
<section class="card">
  <h1>About Us</h1>
  <p>CityCare Hospital queue management system.</p>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
```

For a staff-only page, add `require_login();` after the header line. For an admin-only page, add `require_role(['admin']);`.

---

## 4. CSS

### 4.1 How CSS is connected

One external stylesheet is loaded in `includes/header.php`:

```html
<link rel="stylesheet" href="css/style.css">
```

Rule anatomy:

```css
.selector {
  property: value;
}
```

### 4.2 Selector types used

| Selector | Example | Meaning |
|---|---|---|
| Element | `body`, `table`, `h1` | All elements of that type |
| Class | `.card`, `.btn` | Elements with that class |
| Element + class | `a.btn`, `tbody tr:hover td` | More specific combination |
| ID | Not used in the CSS file | IDs are used only by JS/labels here |
| Descendant | `.nav-links a` | Links inside `.nav-links` |
| Group | `th, td` | Apply the same rule to both |
| Universal | `*` | Every element (`box-sizing`) |
| Root | `:root` | Holds the CSS variables |
| Attribute | `input[readonly]` | Inputs that have the `readonly` attribute |
| Pseudo-class | `:hover`, `:focus`, `:active`, `:focus-visible` | State-based styling |
| Structural | `:nth-child(even)`, `:not(:last-child)` | Position-based styling |
| Two classes | `.nav-links.open` | Element must have both classes |
| Media query | `@media (max-width: 860px)` | Rules for smaller screens |

### 4.3 CSS variables (the theme)

At the top of `css/style.css`:

```css
:root {
  --bg: #f4f7fa;
  --card: #ffffff;
  --ink: #10233c;
  --muted: #5d6f88;
  --brand: #0b6bcb;
  --brand-d: #0958a8;
  --accent: #0d9488;
  --danger: #dc2626;
  --radius: 14px;
  --shadow: 0 6px 20px rgba(16,35,60,.07);
}
```

Use a variable with `var(--brand)`. Changing `--brand` here changes the color everywhere it is used. This is the fastest way to re-theme the site.

### 4.4 The box model and `box-sizing`

Every element has content, padding, border, and margin.

```css
* { box-sizing: border-box; }
```

With `border-box`, a declared `width` includes padding and border, which makes layouts much easier to predict.

### 4.5 Layout: Flexbox, Grid, position

**Flexbox** is used for one-dimensional layouts:

```css
.nav-inner { display: flex; align-items: center; gap: 14px; }
.hero { display: flex; gap: 26px; flex-wrap: wrap; }
.hero-text { flex: 1 1 330px; }
```

- `display: flex` makes the children line up in a row by default.
- `align-items: center` centers them vertically.
- `justify-content: center` centers them horizontally (used on `.hero-text` and `.steps`).
- `gap` adds space between children.
- `flex-wrap: wrap` lets children move to a new line on small screens.
- `flex: 1 1 330px` means grow, shrink, and start from a 330px basis.

**CSS Grid** is used for two-dimensional layouts:

```css
.grid { display: grid; gap: 14px; }
.grid-2 { grid-template-columns: repeat(2, 1fr); }
.grid-3 { grid-template-columns: repeat(3, 1fr); }
.grid-4 { grid-template-columns: repeat(4, 1fr); }
```

`1fr` means one fraction of the available space.

**Position:**

| Value | Used on | Effect |
|---|---|---|
| `sticky` | `.navbar` (top 0), `.ticker` (top 62px) | Stays visible while scrolling |
| `relative` | `.card`, `.hero-card` | Normal position but becomes a reference for absolute children |
| `absolute` | `.badge` | Placed relative to the nearest positioned parent |
| `z-index` | navbar 50, ticker 40 | Higher number stays on top |

### 4.6 CSS properties used in this project

| Property | Example | Purpose |
|---|---|---|
| `color` | `color: var(--ink)` | Text color |
| `background` | `background: linear-gradient(...)` | Background color or gradient |
| `font-family` | `"Inter", "Segoe UI", system-ui, ...` | Font stack |
| `font-size` | `1.7rem` | Text size |
| `font-weight` | `600` / `800` | Boldness |
| `line-height` | `1.6` | Space between lines |
| `letter-spacing` | `.01em` | Space between letters |
| `text-align` | `center` | Horizontal text alignment |
| `text-decoration` | `none` / `underline` | Removes or adds underlines |
| `white-space` | `nowrap` | Keeps a token pill on one line |
| `font-variant-numeric` | `tabular-nums` | Even-width digits in statistics |
| `width` / `max-width` | `width:100%`, `max-width:1080px` | Element width |
| `height` / `min-height` | `min-height:62px` | Element height |
| `margin` | `margin:0 auto` | Outer spacing; `auto` centers a block |
| `padding` | `padding:20px 22px` | Inner spacing |
| `border` | `border:1px solid var(--line)` | Outline |
| `border-radius` | `var(--radius)` | Rounded corners |
| `box-shadow` | `var(--shadow)` | Drop shadow |
| `box-sizing` | `border-box` | Includes padding/border in width |
| `overflow` | `hidden` | Clips content; rounds table corners |
| `display` | `flex`, `grid`, `block`, `none` | Layout type |
| `position` | `sticky`, `relative`, `absolute` | Positioning method |
| `top` / `right` | `top:0`, `right:14px` | Offset for positioned elements |
| `z-index` | `50` | Stacking order |
| `flex` | `flex: 1 1 330px` | Grow/shrink/basis |
| `flex-direction` | `column` | Vertical flex layout |
| `flex-wrap` | `wrap` | Allows wrapping |
| `justify-content` | `center` | Main-axis alignment |
| `align-items` | `center` | Cross-axis alignment |
| `gap` | `14px` | Space between flex/grid children |
| `grid-template-columns` | `repeat(3, 1fr)` | Grid column sizes |
| `vertical-align` | `-7px` | Aligns the logo cross with the text |
| `background` gradient | `linear-gradient(180deg, var(--navy), var(--navy-2))` | Navbar/footer color |
| `filter` | `brightness(.96)` | Slightly darkens a pill on hover |
| `transform` | `translateY(-1px)` | Small lift on button hover |
| `transition` | `background .15s` | Smooth state change |
| `cursor` | `pointer` | Hand cursor on buttons |
| `outline` | `3px solid rgba(...)` | Visible keyboard focus ring |
| `outline-offset` | `2px` | Space between outline and element |
| `opacity` | `.75` | Slight transparency |
| `scroll-behavior` | `smooth` | Smooth anchor scrolling |
| `-webkit-font-smoothing` | `antialiased` | Smoother text on WebKit browsers |
| `border-collapse` | `separate` | Table border model |
| `border-spacing` | `0` | Removes gaps between cells |

Special mention: `!important` appears on `.btn` text colors. It forces the button text to stay white (or brand-colored for `.btn-ghost`) even though the general `a:hover` rule would otherwise override it.

### 4.7 Responsive design

The site uses a desktop-first approach with two breakpoints:

```css
@media (max-width: 860px) {
  .grid-4, .grid-3 { grid-template-columns: repeat(2, 1fr); }
  .steps { grid-template-columns: repeat(2, 1fr); }
  .foot-grid { grid-template-columns: 1fr 1fr; }
  .nav-toggle { display: block; }
  .nav-links { display: none; width: 100%; }
  .nav-links.open { display: flex; flex-direction: column; }
  .hero { flex-direction: column; }
}

@media (max-width: 560px) {
  .grid-2, .grid-4, .grid-3 { grid-template-columns: 1fr; }
  .steps { grid-template-columns: 1fr; }
  .foot-grid { grid-template-columns: 1fr; }
  th:nth-child(4), td:nth-child(4) { display: none; }
  h1 { font-size: 1.45rem; }
}
```

What to say orally: "At 860px the hamburger appears, the 3- and 4-column grids become 2 columns, and the hero stacks. At 560px everything becomes one column and the fourth table column is hidden to save width. `@media (prefers-reduced-motion: reduce)` turns off smooth scrolling and button transitions for users who prefer less motion."

### 4.8 CSS questions you should be able to answer

1. **What is CSS?** Cascading Style Sheets; it controls the presentation of HTML.
2. **Three ways to add CSS?** Inline (`style` attribute), internal (`<style>`), external (`<link>`). This project uses external, plus a few inline tweaks.
3. **What is a selector?** The part before `{}` that chooses which elements to style.
4. **Class vs ID selector?** Class can repeat and is written `.name`; ID is unique and written `#name`. The project styles with classes.
5. **What is specificity?** The rule that decides which conflicting style wins: inline > ID > class/attribute/pseudo-class > element. If equal, the later rule wins. `!important` overrides normal rules.
6. **What is the box model?** Content + padding + border + margin.
7. **Why `box-sizing: border-box`?** Width includes padding and border, making sizing predictable.
8. **Block vs inline vs inline-block?** Block starts on a new line and takes full width; inline flows with text; inline-block flows like inline but accepts width/height.
9. **Flexbox vs Grid?** Flexbox is best for a row or column; Grid is best for rows and columns at the same time.
10. **What does `justify-content` do?** Aligns items along the main axis. `align-items` aligns along the cross axis.
11. **What is `position: sticky`?** The element scrolls normally until it reaches the specified offset, then sticks. Used for the navbar and ticker.
12. **Absolute vs relative?** Absolute is removed from normal flow and positioned relative to the nearest positioned ancestor; relative stays in flow and can be an anchor.
13. **What is `z-index`?** Stacking order for positioned elements; higher is in front.
14. **What is a media query?** A conditional block of CSS that applies only at certain screen sizes or preferences.
15. **Why `rem` instead of `px`?** `rem` is relative to the root font size, so it scales better with user settings.
16. **What is `:root`?** The highest-level element (`<html>`); commonly used for CSS variables.
17. **How do CSS variables work?** Define `--brand: #0b6bcb` in `:root` and use `var(--brand)` elsewhere; change it once to re-theme.
18. **What is a pseudo-class?** A state such as `:hover`, `:focus`, `:active`, `:nth-child`.
19. **Why `:focus-visible`?** Shows a focus ring for keyboard users without showing it on every mouse click.
20. **What does `overflow: hidden` do on a table?** Clips the corners so `border-radius` looks rounded.
21. **Why is the table striped?** `tbody tr:nth-child(even) td` gives even rows a light background.
22. **What does `white-space: nowrap` do?** Prevents text from wrapping; used for token pills.
23. **What is a gradient?** A smooth transition between colors; used in the navbar and footer.
24. **What is `transition`?** Animates property changes over time, e.g. hover color changes.
25. **Why `!important`?** Forces a declaration to win; it should be used sparingly.
26. **What is `prefers-reduced-motion`?** A media feature that detects a user's request for less animation.
27. **How would you change the theme color?** Change `--brand` and `--brand-d` in `:root` in `css/style.css`.
28. **How is the mobile menu shown?** `main.js` toggles the `open` class; `.nav-links.open { display:flex; }` shows it.
29. **Why hide the fourth table column on phones?** It keeps the remaining columns readable on narrow screens.
30. **What is the difference between `display:none` and `visibility:hidden`?** `display:none` removes the element from layout and screen readers; `visibility:hidden` keeps its space.

### 4.9 Practical CSS edits

**Change the primary color to green:**

```css
:root {
  --brand: #15803d;
  --brand-d: #166534;
}
```

**Change the page background:**

```css
:root { --bg: #f0f4f0; }
```

**Make buttons more rounded:**

```css
.btn { border-radius: 999px; }
```

**Change the mobile breakpoint from 860px to 900px:** change `@media (max-width: 860px)` to `@media (max-width: 900px)`.

**Hide the ticker:**

```css
.ticker { display: none; }
```

---

## 5. JavaScript

### 5.1 The three JavaScript files

| File | Loaded by | Job |
|---|---|---|
| `js/main.js` | Every page via `footer.php` | Mobile menu toggle + live ticker every 8 seconds |
| `js/validation.js` | `book-token.php`, `login.php` | Client-side form validation |
| `js/live-queue.js` | `queue-live.php` | Public display board, refreshed every 5 seconds |

There is also a small inline script at the bottom of `index.php` that fetches the API once to show "Now serving" on the home page.

### 5.2 JavaScript methods and concepts used

| Code | Meaning |
|---|---|
| `document.addEventListener('DOMContentLoaded', ...)` | Wait until the HTML is ready |
| `document.getElementById('liveBoard')` | Find an element by its `id` |
| `element.addEventListener('click', ...)` | Run code when the element is clicked |
| `classList.toggle('open')` | Add the class if missing, remove it if present |
| `fetch('api/live-queue.php')` | Request the URL from the browser |
| `.then(r => r.json())` | Convert the response body to a JavaScript object |
| `.then(d => { ... })` | Use the data when it arrives |
| `setInterval(load, 5000)` | Run `load` every 5 seconds |
| `setTimeout` | Run once after a delay (not used here) |
| `new Date().toLocaleTimeString()` | Current time as a readable string |
| `element.innerHTML = ...` | Replace the element's HTML content |
| `element.textContent = ...` | Replace only its text |
| `.map(...)` | Transform every array item |
| `.join(' ')` | Join array items into one string |
| `.slice(0, 3)` | Take the first three items |
| `` `${x.token_no}` `` | Template literal (string interpolation) |
| `d.waiting_count ?? 0` | Use 0 if the value is null or undefined |
| `a || b` | Use `b` if `a` is falsy |
| `e.preventDefault()` | Stop the form's normal submission |
| `/^[6-9]\d{9}$/.test(value)` | Test a value against a regex |
| `alert(...)` | Show a browser popup |
| `confirm(...)` | Ask OK/Cancel; returns true/false |
| `const` / `let` | Block-scoped variables; `let` can be reassigned, `const` cannot |
| Arrow function `() => {}` | Short function syntax |

### 5.3 How the live board actually updates

1. `queue-live.php` prints empty placeholders.
2. `live-queue.js` runs after `DOMContentLoaded`.
3. It calls `api/live-queue.php`.
4. PHP queries MySQL and returns JSON.
5. JavaScript builds HTML strings and assigns them to `#nowServing`, `#liveBoard`, and `#boardTime`.
6. `setInterval(load, 5000)` repeats the process.

This is called **polling**. It is not WebSockets. Polling is simpler but slightly less real-time and creates a request every 5 seconds.

### 5.4 JavaScript questions you should be able to answer

1. **Can JavaScript connect directly to MySQL?** Not in this project. Browsers do not speak MySQL; JS calls a PHP API, and PHP talks to MySQL.
2. **What is the DOM?** The browser's object model of the page; JS uses it to read and change elements.
3. **`innerHTML` vs `textContent`?** `innerHTML` parses HTML; `textContent` treats everything as plain text. `textContent` is safer against XSS.
4. **What is Fetch?** A browser API for making HTTP requests without reloading the page.
5. **What is a Promise?** An object representing a future result; `.then()` runs when the result is ready.
6. **What is JSON?** JavaScript Object Notation, a text format for exchanging structured data.
7. **`setInterval` vs `setTimeout`?** `setInterval` repeats; `setTimeout` runs once.
8. **Why `DOMContentLoaded`?** To ensure the elements exist before trying to find them.
9. **Why validate in JS if PHP validates too?** JS gives instant feedback and a better user experience; PHP is the security boundary because JS can be disabled or bypassed.
10. **What does `preventDefault()` do?** Stops the browser's default behavior, such as submitting a form when validation fails.
11. **How would you change the board refresh to 3 seconds?** Change `5000` to `3000` in `js/live-queue.js`.
12. **How would you change the ticker refresh to 10 seconds?** Change `8000` to `10000` in `js/main.js`.
13. **Why is the API public?** The display board is public; it only exposes the same information shown on the waiting-room screen. A production system would consider privacy.
14. **What happens if the API fails?** The board shows "Board unreachable - is MySQL running?" instead of freezing.

### 5.5 Practical JavaScript edits

**Board refresh faster:**

```js
setInterval(load, 3000);
```

**Ticker refresh slower:**

```js
setInterval(load, 10000);
```

**Show only the first 10 waiting tokens in the API:** in `api/live-queue.php`, change `LIMIT 20` to `LIMIT 10`.

**Change the board timestamp format:** in `live-queue.js`:

```js
document.getElementById('boardTime').textContent =
  'Last updated: ' + new Date().toLocaleTimeString();
```

---

## 6. PHP

### 6.1 PHP syntax you will see

| Syntax | Meaning |
|---|---|
| `<?php ... ?>` | PHP block |
| `<?= $x ?>` | Short echo of a value |
| `$conn` | A variable |
| `->` | Access a method/property of an object (`$conn->query(...)`) |
| `=>` | Key/value pair in an array |
| `.` | String concatenation (`'D' . $id`) |
| `??` | Null coalescing: use the right side if the left is null |
| `?:` | Short ternary: use the right side if the left is falsy |
| `===` / `!==` | Strict equality/inequality (type and value) |
| `(int)` | Cast to integer |
| `if / elseif / else` | Conditional branches |
| `foreach ($rows as $r)` | Loop over an array |
| `function name($x) { }` | Define a function |
| `return` | Return a value from a function |
| `require_once` | Load a file only once; fatal if missing |
| `__DIR__` | Directory of the current file |
| `$_POST`, `$_GET`, `$_SESSION` | Superglobals for form data, query data, and session data |
| `header('Location: ...')` | Send a redirect header |
| `exit` / `die` | Stop the script |

### 6.2 PHP functions used in this project

**Loading and configuration**

| Function | Used for |
|---|---|
| `require_once` | Include common, header, footer, config, db, auth |
| `define` | Define constants such as `DB_NAME`, `SITE_NAME` |
| `getenv` | Read optional environment-variable overrides |
| `basename($_SERVER['PHP_SELF'])` | Current file name for navigation highlighting |

**Strings and validation**

| Function | Used for |
|---|---|
| `trim` | Remove spaces from both ends |
| `strlen` | Check minimum length |
| `strtolower`, `ucwords` | Tidy names: `ravi kumar` -> `Ravi Kumar` |
| `preg_replace('/\D/', '', ...)` | Keep only digits in a mobile number |
| `preg_match` | Validate name/mobile/date formats |
| `substr` | Limit the room string to 20 characters |
| `strip_tags` | Remove HTML tags from the room input |
| `str_pad(..., 3, '0', STR_PAD_LEFT)` | Build `001`, `002`, etc. |
| `htmlspecialchars` (inside `h()`) | Escape output to prevent XSS |
| `urlencode` | Safely put a message in a URL |
| `implode`, `array_map`, `array_filter` | Join error messages / clean SQL statements |
| `count` | Count array rows |
| `date('Y-m-d')` | Default history date |
| `file_get_contents` | Read `database.sql` during install |
| `explode(';', $sql)` | Split the SQL file into statements |
| `in_array` | Check a status against the allowed list |

**Database**

| Function | Used for |
|---|---|
| `new mysqli(...)` | Connect to MySQL |
| `set_charset('utf8mb4')` | Use full Unicode |
| `query` | Run a simple SQL query |
| `prepare` | Create a prepared statement |
| `bind_param('sssi', ...)` | Bind values safely; letters are types (`s` string, `i` integer) |
| `execute` | Run the prepared statement |
| `get_result` | Get a result set from a prepared SELECT |
| `fetch_assoc` | Fetch one row as an associative array |
| `fetch_all(MYSQLI_ASSOC)` | Fetch all rows |
| `num_rows` | Number of rows returned |
| `$conn->error` | Last database error message |

**Sessions, security, and flow**

| Function | Used for |
|---|---|
| `session_start` | Start the session |
| `session_set_cookie_params` | Set HttpOnly/SameSite cookie options |
| `session_regenerate_id(true)` | Prevent session fixation at login |
| `session_destroy` | Destroy the session at logout |
| `password_hash` | Store a bcrypt hash |
| `password_verify` | Compare a plain password with the hash |
| `current_user`, `is_logged_in` | Read login state (project functions) |
| `login_user`, `logout_user` | Manage the session (project functions) |
| `require_login`, `require_role` | Page guards (project functions) |
| `header`, `exit` | Redirect and stop |
| `http_response_code(403)` | Send "Forbidden" |
| `mysqli_sql_exception` | Catch database errors in PHP 8 |
| `static $conn` | Reuse one connection per request |

**Project helper functions**

| Function | File | Job |
|---|---|---|
| `h($s)` | `db.php` | `htmlspecialchars` shortcut |
| `db()` | `db.php` | One shared MySQL connection |
| `status_label($s)` | `db.php` | `waiting` -> `Waiting` |
| `make_token_no($doctor_id, $last)` | `db.php` | Build `D2-004` |
| `current_user()` | `auth.php` | Logged-in user array or null |
| `is_logged_in()` | `auth.php` | True/false |
| `require_login()` | `auth.php` | Redirect guests to login |
| `require_role($roles)` | `auth.php` | Enforce staff/admin role |
| `login_user($row)` | `auth.php` | Save user in the session |
| `logout_user()` | `auth.php` | Clear the session |

### 6.3 Key snippets explained line by line

**Login check (`login.php`):**

```php
$mobile = preg_replace('/\D/', '', $_POST['mobile'] ?? '');
$pass = $_POST['password'] ?? '';
$st = $conn->prepare("SELECT * FROM users WHERE mobile=?");
$st->bind_param('s', $mobile);
$st->execute();
$u = $st->get_result()->fetch_assoc();
if ($u && password_verify($pass, $u['password_hash'])) {
    login_user($u);
    header('Location: history.php');
    exit;
}
```

- `preg_replace` removes non-digits from the submitted mobile.
- `?? ''` avoids an "undefined index" warning if the field is missing.
- `prepare` + `?` + `bind_param` prevent SQL injection. The `s` means the value is a string.
- `password_verify` checks the plain password against the stored bcrypt hash. No decryption happens.
- `login_user` writes the user to `$_SESSION` and regenerates the session ID.
- `header` + `exit` redirect without running the rest of the page.

**Token creation (`book-token.php`):**

```php
$row = $conn->query("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(token_no,'-',-1) AS UNSIGNED)),0) m
                     FROM tokens WHERE doctor_id=$doctor_id AND booked_date=CURDATE()")->fetch_assoc();
$tno = make_token_no($doctor_id, (int)$row['m']);
$st = $conn->prepare("INSERT INTO tokens (token_no,patient_name,mobile,doctor_id,booked_date)
                      VALUES (?,?,?,?,CURDATE())");
$st->bind_param('sssi', $tno, $name, $mobile, $doctor_id);
$st->execute();
```

- `SUBSTRING_INDEX(token_no,'-',-1)` takes the part after the last `-`, e.g. `004`.
- `CAST(... AS UNSIGNED)` turns it into a number.
- `MAX(...)` finds the highest sequence today for that doctor.
- `COALESCE(...,0)` returns 0 when there are no tokens yet.
- `make_token_no` adds 1 and pads to 3 digits.
- The insert uses a prepared statement for the user-entered name/mobile.

**Status update (`staff-queue.php`):**

```php
$id = (int)($_POST['token_id'] ?? 0);
$act = $_POST['action'] ?? '';
if ($act === 'call') {
    $conn->query("UPDATE tokens SET status='called' WHERE id=$id AND status='waiting'");
}
```

- The ID is cast to an integer, so it cannot inject SQL.
- The `AND status='waiting'` condition enforces a valid transition.
- The action comes from the clicked button's `name="action"` value.

**Page guard (`auth.php`):**

```php
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php?msg=' . urlencode('Please login first.'));
        exit;
    }
}
function require_role($roles) {
    require_login();
    if (!in_array($_SESSION['user']['role'], (array)$roles)) {
        http_response_code(403);
        die('<h2>403 - Not allowed for your role.</h2>');
    }
}
```

- `header` sends the browser to login; `exit` stops the current page.
- `in_array` checks the role; `(array)$roles` allows a single string or an array.

### 6.4 PHP questions you should be able to answer

1. **What is PHP?** A server-side scripting language that runs on the web server and can talk to databases.
2. **How is PHP different from JavaScript?** PHP runs on the server before the page is sent; JS runs in the browser after it arrives.
3. **`include` vs `require`?** `require` stops the script with a fatal error if the file is missing; `include` only warns. `require_once` also prevents loading the same file twice.
4. **What is `__DIR__`?** The absolute directory of the current file, used to build reliable include paths.
5. **What are `$_POST`, `$_GET`, `$_SESSION`?** Superglobals for POST data, query-string data, and session data.
6. **Why use `password_hash`?** It creates a one-way bcrypt hash; even the admin cannot read the original password.
7. **What is `password_verify`?** It hashes the entered password with the stored salt and compares the result.
8. **Why `header('Location: ...')`?** It tells the browser to request a different URL.
9. **Why `exit` after `header`?** The redirect header does not stop PHP; `exit` prevents the rest of the page from running or leaking.
10. **What is a prepared statement?** A query template sent separately from the data, so user input cannot change the SQL structure.
11. **What do the letters in `bind_param('sssi', ...)` mean?** `s` = string, `i` = integer, `d` = double, `b` = blob. The order must match the `?` placeholders.
12. **What is a session?** Server-side storage tied to a browser cookie, used to remember the logged-in user.
13. **Why `session_regenerate_id(true)`?** A new session ID at login prevents session fixation attacks.
14. **What does `h()` do?** It runs `htmlspecialchars`, converting `<`, `>`, `&`, and quotes into safe entities to prevent XSS.
15. **Why is `db()` static?** It keeps one connection for the whole request instead of reconnecting on every call.
16. **What is `mysqli_sql_exception`?** The exception type thrown by MySQLi on database errors in modern PHP. `install.php` catches it to show a friendly message.
17. **What does `(int)` do?** Converts a value to an integer, which also removes any malicious SQL text from an ID.
18. **What is the difference between `==` and `===`?** `==` compares after type conversion; `===` compares both type and value. The project uses `===` for important checks.
19. **Why does `install.php` not load `common.php`?** Because the database may not exist yet; `common.php` would try to connect immediately.
20. **How does the app know a user is an admin?** The role is stored in the `users` table and copied into `$_SESSION` at login; `require_role(['admin'])` checks it.

### 6.5 Practical PHP edits

**Change the site name:**

```php
define('SITE_NAME', 'Your Hospital - Queue Management');
```

File: `includes/config.php`.

**Change the token format to a different prefix:**

```php
return 'T' . (int)$doctor_id . '-' . str_pad($last_seq_today + 1, 3, '0', STR_PAD_LEFT);
```

File: `includes/db.php` (change only the prefix; keep the rest).

**Use four digits:**

```php
str_pad($last_seq_today + 1, 4, '0', STR_PAD_LEFT)
```

**Allow 2-character names:** update `strlen($name) < 3` to `< 2` in `book-token.php`, change `minlength="3"` to `minlength="2"` in the booking form, and change `/^[A-Za-z ]{3,}$/` to `{2,}` in `js/validation.js`.

**Protect a new page for staff only:** add after the header line:

```php
require_login();
```

**Protect a new page for admins only:**

```php
require_role(['admin']);
```

**Change the history row limit:** change `LIMIT 200` in `history.php`.

---

## 7. SQL and MySQL

### 7.1 The schema

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  mobile VARCHAR(15) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('staff','admin') NOT NULL DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE doctors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  room VARCHAR(20) NOT NULL DEFAULT ''
);

CREATE TABLE tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token_no VARCHAR(30) NOT NULL,
  booked_date DATE NOT NULL,
  patient_name VARCHAR(100) NOT NULL,
  mobile VARCHAR(15) NOT NULL,
  doctor_id INT NOT NULL,
  status ENUM('waiting','called','completed','cancelled') DEFAULT 'waiting',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
  UNIQUE KEY uq_token_day (booked_date, token_no),
  INDEX idx_doc_status (doctor_id, status),
  INDEX idx_doc_created (doctor_id, created_at)
);
```

### 7.2 SQL concepts used

| Concept | Where | Meaning |
|---|---|---|
| `CREATE DATABASE IF NOT EXISTS` | install, database.sql | Create the database only if missing |
| `CREATE TABLE IF NOT EXISTS` | database.sql | Create each table only if missing |
| `PRIMARY KEY` | all tables | Unique identifier for a row |
| `AUTO_INCREMENT` | all `id` columns | Automatically increments |
| `UNIQUE` | `users.mobile`, `(booked_date, token_no)` | No duplicate values |
| `FOREIGN KEY` | `tokens.doctor_id` | Links a token to a doctor |
| `ON DELETE CASCADE` | `tokens.doctor_id` | Deleting a doctor deletes their tokens |
| `INDEX` | two token indexes | Speeds up lookups |
| `ENUM` | `role`, `status` | Only listed values allowed |
| `NOT NULL` | Most columns | Value is required |
| `DEFAULT` | `role`, `status`, `created_at` | Value used when none is given |
| `SELECT` | Everywhere | Read data |
| `INSERT` | Booking, doctor/user creation | Add data |
| `UPDATE` | Queue actions, password reset | Change data |
| `DELETE` | Doctor/user deletion | Remove data |
| `WHERE` | Most queries | Filter rows |
| `ORDER BY` | Queues, history, lists | Sort results |
| `LIMIT` | Board, history | Limit row count |
| `COUNT`, `SUM`, `MAX` | Stats, numbering | Aggregate values |
| `GROUP BY` | History dashboard | One row per group |
| `INNER JOIN` | Queue, history, API | Match tokens to their doctor |
| `LEFT JOIN` | Per-doctor dashboard | Keep doctors with zero tokens |
| `COALESCE` | Token numbering | Replace NULL with 0 |
| `CAST` | Token numbering | Convert the text suffix to a number |
| `SUBSTRING_INDEX` | Token numbering | Extract the part after the last `-` |
| `CURDATE()` | Today filters | Today's date |
| `DATE(created_at)` | Today filters | Extract the date part of a timestamp |
| `IN` | `index.php` served count | Match any value in a list |
| `FIELD()` | Account ordering | Custom order: admin before staff |
| `LIKE` | Not currently used | Pattern search; useful for a "search patient" feature |

### 7.3 Every query in the project

| File | Query | What it does |
|---|---|---|
| `index.php` | `COUNT(*) ... status='waiting'` | Number waiting today |
| `index.php` | `COUNT(*) ... status IN ('called','completed')` | Number served today |
| `index.php` | `COUNT(*) FROM doctors` | Total doctors |
| `book-token.php` | `SELECT * FROM doctors ORDER BY name` | Fill the doctor dropdown |
| `book-token.php` | `SELECT name FROM doctors WHERE id=$doctor_id` | Verify the doctor exists |
| `book-token.php` | `MAX(CAST(SUBSTRING_INDEX(...))) ...` | Highest sequence today for that doctor |
| `book-token.php` | `INSERT INTO tokens ...` | Create the token |
| `history.php` | `SELECT status, COUNT(*) ... GROUP BY status` | Today's status cards |
| `history.php` | `LEFT JOIN ... GROUP BY d.id` | Per-doctor today table |
| `history.php` | `SELECT ... WHERE date/doctor/status ... LIMIT 200` | Filtered token history |
| `staff-queue.php` | `UPDATE tokens SET status='called' ...` | Call a token |
| `staff-queue.php` | `UPDATE ... 'completed' ...` | Complete a token |
| `staff-queue.php` | `UPDATE ... 'cancelled' ...` | Cancel a token |
| `staff-queue.php` | `SELECT ... status='called' ... ORDER BY created_at ASC` | Now-serving list |
| `staff-queue.php` | `SELECT ... status='waiting' ... LIMIT 100` | Waiting list |
| `staff-doctors.php` | `INSERT INTO doctors ...` | Add a doctor |
| `staff-doctors.php` | `DELETE FROM doctors WHERE id=...` | Delete a doctor (tokens cascade) |
| `staff-doctors.php` | Correlated `COUNT(*)` subquery | Tokens today per doctor |
| `staff-accounts.php` | `INSERT INTO users ...` | Create a login |
| `staff-accounts.php` | `UPDATE users SET password_hash=? ...` | Reset a password |
| `staff-accounts.php` | `DELETE FROM users ...` | Delete a login |
| `staff-accounts.php` | `ORDER BY FIELD(role,'admin','staff'), name` | Admins first, then name |
| `login.php` | `SELECT * FROM users WHERE mobile=?` | Find the login account |
| `install.php` | `SELECT id FROM users WHERE role='admin' LIMIT 1` | Check whether installed |
| `install.php` | `INSERT INTO users ...` | Create the first admin |
| `api/live-queue.php` | `SELECT ... status='called' ... LIMIT 5` | Now serving |
| `api/live-queue.php` | `SELECT ... status='waiting' ... LIMIT 20` | Waiting queue |
| `api/live-queue.php` | `COUNT(*) ... status='waiting'` | Waiting count |

### 7.4 SQL questions you should be able to answer

1. **What is SQL?** Structured Query Language, the standard language for relational databases.
2. **DDL vs DML?** DDL defines structure (`CREATE`, `ALTER`, `DROP`); DML works with data (`SELECT`, `INSERT`, `UPDATE`, `DELETE`).
3. **What is a primary key?** A column that uniquely identifies each row; it cannot be NULL.
4. **What is a unique key?** It prevents duplicate values but can allow one NULL. `users.mobile` is unique.
5. **Primary key vs unique key?** A table has one primary key; it can have multiple unique keys. Primary key rows are the main identifier.
6. **What is a foreign key?** A column that references another table's primary key, enforcing referential integrity. `tokens.doctor_id` references `doctors.id`.
7. **What is `ON DELETE CASCADE`?** When the parent row is deleted, child rows are automatically deleted. Deleting a doctor deletes their tokens.
8. **What is `AUTO_INCREMENT`?** MySQL automatically assigns the next integer when a new row is inserted.
9. **Why `ENUM` for status?** It restricts values to `waiting`, `called`, `completed`, `cancelled`, preventing typos.
10. **What does `NOT NULL` do?** The column must have a value.
11. **What does `DEFAULT` do?** Supplies a value when the insert does not provide one.
12. **What is an index?** A data structure that speeds up reads on the indexed columns. `idx_doc_status` helps "doctor + status" queries.
13. **Why is the unique key `(booked_date, token_no)`?** Token numbers restart daily, so `D1-001` may exist on different dates but only once per date.
14. **What is `CURDATE()`?** MySQL's current date.
15. **Why `DATE(created_at)=CURDATE()`?** `created_at` includes a time; `DATE()` keeps only the date for comparison.
16. **What is `COALESCE`?** Returns the first non-NULL argument; `COALESCE(MAX(...), 0)` avoids a NULL when there are no tokens.
17. **What does `CAST(... AS UNSIGNED)` do?** Converts text such as `'004'` into the number 4.
18. **What does `SUBSTRING_INDEX(token_no,'-',-1)` do?** Returns the part after the last hyphen, e.g. `004`.
19. **What is `GROUP BY`?** Groups rows so aggregates such as `COUNT` or `SUM` apply per group.
20. **What is the difference between `WHERE` and `HAVING`?** `WHERE` filters rows before grouping; `HAVING` filters groups after grouping. This project only needs `WHERE`.
21. **INNER JOIN vs LEFT JOIN?** INNER returns only matching rows; LEFT returns all left-table rows plus matches (or NULL). The per-doctor dashboard uses LEFT JOIN so doctors with zero tokens still appear.
22. **What is a subquery?** A query inside another query. `staff-doctors.php` uses a correlated subquery to count today's tokens per doctor.
23. **What is `LIMIT`?** Restricts the number of returned rows.
24. **What is `ORDER BY ... ASC/DESC`?** Sorts ascending or descending.
25. **What does `SUM(t.status='waiting')` do?** In MySQL, the comparison is 1 or 0, so summing it counts waiting tokens.
26. **What is a prepared statement?** A two-step query where SQL and values are sent separately; `?` is a placeholder.
27. **Why not build SQL with string concatenation?** It allows SQL injection. Prepared statements prevent it.
28. **`DELETE` vs `TRUNCATE` vs `DROP`?** `DELETE` removes selected rows (can be rolled back); `TRUNCATE` empties the table quickly; `DROP` removes the table itself.
29. **How do you add a column?** `ALTER TABLE tokens ADD COLUMN age INT NULL;`
30. **How do you inspect the database visually?** Open phpMyAdmin at `http://localhost/phpmyadmin/`, choose `hospital_queue`, then Browse/Structure/SQL.

### 7.5 Practical SQL tasks and answers

**Show all tables:**

```sql
SHOW TABLES;
```

**Describe a table:**

```sql
DESCRIBE tokens;
```

**Today's waiting tokens:**

```sql
SELECT token_no, patient_name, doctor_id
FROM tokens
WHERE booked_date = CURDATE() AND status = 'waiting'
ORDER BY created_at ASC;
```

**Count today's tokens per doctor:**

```sql
SELECT d.name, COUNT(t.id) AS total
FROM doctors d
LEFT JOIN tokens t ON t.doctor_id = d.id AND DATE(t.created_at) = CURDATE()
GROUP BY d.id, d.name;
```

**Join tokens with doctor names:**

```sql
SELECT t.token_no, t.patient_name, d.name AS doctor, d.room
FROM tokens t
JOIN doctors d ON d.id = t.doctor_id
ORDER BY t.created_at DESC;
```

**Call a token manually:**

```sql
UPDATE tokens SET status = 'called' WHERE id = 5 AND status = 'waiting';
```

**Complete a token manually:**

```sql
UPDATE tokens SET status = 'completed' WHERE id = 5;
```

**Add a doctor manually:**

```sql
INSERT INTO doctors (name, room) VALUES ('Dr Test', 'R9');
```

**Delete a doctor (and their tokens):**

```sql
DELETE FROM doctors WHERE id = 3;
-- All tokens with doctor_id = 3 are removed by ON DELETE CASCADE.
```

**Add an age column (advanced):**

```sql
ALTER TABLE tokens ADD COLUMN age INT NULL AFTER mobile;
```

Then update the booking form, the PHP `INSERT`, and the history table if you want to use it.

**Search patients by name (prepared statement in PHP):**

```php
$search = '%' . $term . '%';
$st = $conn->prepare("SELECT t.*, d.name AS doctor
                      FROM tokens t JOIN doctors d ON d.id = t.doctor_id
                      WHERE t.patient_name LIKE ?
                      ORDER BY t.created_at DESC");
$st->bind_param('s', $search);
$st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
```

**Export or back up the database:** phpMyAdmin -> `hospital_queue` -> Export -> Go. Choose SQL format.

---

## 8. Project logic questions

1. **How does token numbering work?** For the chosen doctor, find the highest numeric suffix among today's tokens, add 1, and format as `D{doctor_id}-{three digits}`. If none exist, start at 001.
2. **Why not just count rows?** If a token is deleted, counting could reuse an existing number. `MAX` always moves forward.
3. **How do numbers restart daily?** The query filters `booked_date = CURDATE()`, so yesterday's rows are ignored.
4. **Why per doctor?** Each doctor has an independent queue; patients only wait for the doctor they chose.
5. **Why the unique key `(booked_date, token_no)`?** It allows the same number on different days but blocks duplicates on the same day.
6. **What is FIFO?** First In, First Out. `ORDER BY created_at ASC` serves the oldest token first.
7. **How does the board update without reloading?** JavaScript fetches JSON from `api/live-queue.php` every 5 seconds and rewrites the DOM.
8. **What happens when a token is completed?** Its status becomes `completed`, it leaves the live "now serving"/waiting lists, and it remains in history.
9. **What happens when a token is cancelled?** Its status becomes `cancelled`; it disappears from the active queue but stays in history.
10. **Can a completed token be completed again?** The update only matches `waiting` or `called`, so no; the status stays completed.
11. **Can a cancelled token be called?** No, because the call update only matches `waiting`.
12. **What happens when a doctor is deleted?** All of that doctor's tokens are deleted by the foreign key cascade. History for that doctor is lost.
13. **Who can add or delete doctors?** Any logged-in staff member, not only admins.
14. **Who can manage accounts?** Only users with the `admin` role.
15. **Can an admin delete themselves?** No; `staff-accounts.php` checks the current session ID.
16. **Can a patient log in?** No. Patients do not have accounts; they only book.
17. **Can staff book a token?** No; `book-token.php` redirects logged-in users to history.
18. **How is the first admin created?** By `install.php` during the first setup.
19. **Why does install.php lock itself?** It checks for an existing admin. If one exists, it shows "Already installed" and refuses to run again.
20. **What is the difference between staff and admin?** Staff manage the queue and doctors; admins also manage accounts.
21. **Where is the database name configured?** `includes/config.php` (`DB_NAME`, default `hospital_queue`).
22. **Which file starts the session?** `includes/auth.php`, loaded by `common.php` on every page.
23. **Which file protects pages?** `includes/auth.php` with `require_login()` and `require_role()`.
24. **Which file creates the tables?** `database.sql`, executed by `install.php`.
25. **Which file is the API?** `api/live-queue.php`; it returns JSON.
26. **Why does the ticker show on every page?** `header.php` prints it and `main.js` fills it on every page.
27. **Why is `main.js` loaded in the footer?** The DOM exists by then, and it applies to every page.
28. **Why are validation rules duplicated?** JS gives quick feedback; PHP enforces the rule because the client can be bypassed.

---

## 9. Security questions

1. **SQL injection:** User values use prepared statements with `?` and `bind_param`. Numeric IDs are cast to `(int)`. The history date is regex-validated and the status is whitelisted.
2. **XSS:** Output goes through `h()` (`htmlspecialchars`). Patient names are also restricted to letters and spaces at booking time.
3. **Passwords:** `password_hash` stores bcrypt hashes; `password_verify` checks them. There is no decryption.
4. **Session fixation:** `session_regenerate_id(true)` runs at login.
5. **Session cookies:** `HttpOnly` prevents JavaScript from reading the cookie; `SameSite=Lax` limits cross-site sending.
6. **Role checks:** `require_login` and `require_role` block unauthorized pages. The server returns 403 for the wrong role.
7. **Validation:** Both JavaScript and PHP validate. PHP is the real gate.
8. **CSRF:** Not implemented. A production system should add a per-session CSRF token to forms.
9. **HTTPS:** Not configured locally. Production must use HTTPS so passwords are encrypted in transit.
10. **Privacy:** The public board shows patient names. A real hospital might show only token numbers.
11. **Known limitation:** JS inserts board data with `innerHTML`. For a stricter design, use `textContent` or sanitize on output. Patient names are already letters-only; doctor names are added by staff.
12. **Database credentials:** `config.php` contains the local XAMPP defaults. Never commit real production credentials.

Good viva line: "We validate twice - JavaScript for user experience, PHP for security - and we never trust the client."

---

## 10. Rapid-fire one-liners

| Question | Answer |
|---|---|
| Server-side language? | PHP |
| Database? | MySQL |
| Web server? | Apache (XAMPP) |
| Styling? | One external CSS file, `css/style.css` |
| Live update technique? | Polling a JSON API with Fetch every 5 seconds |
| API file? | `api/live-queue.php` |
| Data format? | JSON |
| Token example? | `D2-004` = doctor 2, fourth token today |
| Queue order? | FIFO by `created_at ASC` |
| Daily reset? | Filter by `booked_date = CURDATE()` |
| Unique rule? | `UNIQUE(booked_date, token_no)` |
| Foreign key? | `tokens.doctor_id -> doctors.id` |
| Delete behavior? | `ON DELETE CASCADE` |
| Login ID? | Mobile number |
| Password storage? | Bcrypt hash via `password_hash` |
| Session start? | `auth.php` |
| Admin-only page? | `staff-accounts.php` |
| First admin? | Created by `install.php` |
| Tables? | `users`, `doctors`, `tokens` |
| Board refresh? | 5 seconds (`live-queue.js`) |
| Ticker refresh? | 8 seconds (`main.js`) |
| Max now-serving on board? | 5 |
| Max waiting on board? | 20 |
| History row limit? | 200 |
| Config file? | `includes/config.php` |
| Helper file? | `includes/db.php` |
| Shared bootstrap? | `includes/common.php` |
| Header/footer? | `includes/header.php`, `includes/footer.php` |
| Form method for booking? | POST |
| Form method for filters? | GET |
| Mobile rule? | 10 digits, starts 6-9 |
| Name rule? | At least 3 letters/spaces |
| XSS helper? | `h()` |
| SQL safety? | Prepared statements |
| Role check? | `require_role(['admin'])` |
| 403 means? | Forbidden |

---

## 11. Practical lab: likely edits and exact steps

### 11.1 Before you edit

There are two copies of the code:

- Workspace: `C:\Users\Parth\Hopstial_queue_management\hospital-queue`
- Served by Apache: `C:\xampp\htdocs\hospital-queue`

If you edit the workspace, copy the changed file to `htdocs` (or run the starter script). If you are demoing live, it is fastest to edit the `htdocs` copy directly. After CSS/JS changes, press Ctrl+F5 to bypass the cache.

Check services:

```
http://localhost/hospital-queue/index.php
```

If it fails, start Apache and MySQL in XAMPP.

### 11.2 Ten most likely practical tasks

**1. Change the hospital name**

File: `includes/config.php`

```php
define('SITE_NAME', 'Sunrise Hospital - Queue Management');
```

**2. Change the theme color**

File: `css/style.css`

```css
:root {
  --brand: #15803d;
  --brand-d: #166534;
  --accent: #0d9488;
}
```

**3. Change the board refresh to 3 seconds**

File: `js/live-queue.js`

```js
setInterval(load, 3000);
```

**4. Show 10 waiting tokens instead of 20**

File: `api/live-queue.php`

```sql
... ORDER BY t.created_at ASC LIMIT 10
```

**5. Change the token prefix from D to T**

File: `includes/db.php`

```php
return 'T' . (int)$doctor_id . '-' . str_pad($last_seq_today + 1, 3, '0', STR_PAD_LEFT);
```

**6. Use 4-digit token numbers**

File: `includes/db.php`

```php
str_pad($last_seq_today + 1, 4, '0', STR_PAD_LEFT)
```

**7. Change the mobile validation to 11 digits**

File: `js/validation.js`

```js
function validMobile(m) { return /^[6-9]\d{10}$/.test((m || '').trim()); }
```

File: `book-token.php`

```php
if (!preg_match('/^[6-9]\d{10}$/', $mobile)) $errs[] = 'Mobile: 11 digits starting 6-9.';
```

Also update the HTML `pattern` attribute (`[6-9][0-9]{9}`) in `book-token.php` to match, and change both validation layers.

**8. Add a Room column to the history table**

File: `history.php`

The query already selects `d.room` as `room`. Add the header:

```php
<th>Room</th>
```

and the cell after the Doctor cell:

```php
<td><?= h($t['room']) ?></td>
```

Do the same for the "No records" `colspan` if you want it exact (change 5 to 6).

**9. Add a new public page**

Create `hospital-queue/about.php`:

```php
<?php require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/header.php';
?>
<section class="card">
  <h1>About CityCare</h1>
  <p>This hospital uses a digital token queue.</p>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
```

Add a link in `includes/header.php`:

```html
<a class="<?= $active === 'about.php' ? 'active' : '' ?>" href="about.php">About</a>
```

**10. Make the queue newest-first**

Files: `staff-queue.php` and `api/live-queue.php`

Change every `ORDER BY t.created_at ASC` to `DESC` in the waiting/now-serving queries. Explain that this breaks FIFO, so it is only a demonstration edit.

### 11.3 Other small tasks

| Task | File | Change |
|---|---|---|
| Change the footer credit | `includes/footer.php` | Edit `.foot-base` text |
| Change the "Get Token" heading | `book-token.php` | Edit `<h1>` |
| Change the home headline | `index.php` | Edit the hero `<h1>` |
| Hide the ticker | `css/style.css` | `.ticker { display:none; }` |
| Change the nav breakpoint | `css/style.css` | `@media (max-width: 900px)` |
| Show more "now serving" on home | `index.php` | `.slice(0, 3)` -> `.slice(0, 5)` |
| Change board "Now Serving" limit | `api/live-queue.php` | `LIMIT 5` |
| Change waiting limit in staff queue | `staff-queue.php` | `LIMIT 100` |
| Change history limit | `history.php` | `LIMIT 200` |
| Change doctor room length | `staff-doctors.php` + DB | `substr(...,0,20)` and `VARCHAR(20)` |
| Add a doctor | UI | `staff-doctors.php` form |
| Add a staff login | UI | `staff-accounts.php` (admin) |
| Reset a password | UI | `staff-accounts.php` (admin) |
| Clear today's data | SQL | `DELETE FROM tokens WHERE booked_date = CURDATE();` |

### 11.4 SQL demo commands

```sql
-- See the three tables
SHOW TABLES;

-- See the token structure
DESCRIBE tokens;

-- See the newest tokens
SELECT id, token_no, patient_name, status, booked_date
FROM tokens ORDER BY id DESC LIMIT 10;

-- See today's queue with doctor names
SELECT t.token_no, t.patient_name, d.name AS doctor, t.status
FROM tokens t JOIN doctors d ON d.id = t.doctor_id
WHERE t.booked_date = CURDATE()
ORDER BY t.created_at ASC;

-- Manually change a status
UPDATE tokens SET status = 'called' WHERE id = 1;

-- Count tokens per doctor today
SELECT d.name, COUNT(t.id) AS total
FROM doctors d
LEFT JOIN tokens t ON t.doctor_id = d.id AND t.booked_date = CURDATE()
GROUP BY d.id, d.name;
```

---

## 12. If the examiner asks "explain this file"

Use this map:

| File | One-line explanation |
|---|---|
| `includes/config.php` | Database settings and site name |
| `includes/db.php` | Connects to MySQL and holds `h()`, `status_label()`, `make_token_no()` |
| `includes/auth.php` | Sessions, login state, and role guards |
| `includes/common.php` | Loads config + db + auth and creates `$conn` and `$user` |
| `includes/header.php` | HTML head, navbar, ticker, opens `<main>` |
| `includes/footer.php` | Closes `<main>`, footer, loads `main.js` |
| `index.php` | Public home page with today's counts |
| `book-token.php` | Public booking form and token creation |
| `queue-live.php` | Public display board shell |
| `login.php` | Staff login and password check |
| `logout.php` | Destroys the session |
| `history.php` | Staff dashboard and filterable history |
| `staff-queue.php` | Call / complete / cancel tokens |
| `staff-doctors.php` | Add and delete doctors |
| `staff-accounts.php` | Admin-only account management |
| `install.php` | One-time database and first-admin setup |
| `database.sql` | Three-table schema with foreign key and indexes |
| `api/live-queue.php` | Public JSON feed for the board/ticker |
| `css/style.css` | The entire visual design and responsive layout |
| `js/main.js` | Mobile menu and live ticker |
| `js/validation.js` | Client-side booking/login validation |
| `js/live-queue.js` | Board polling and DOM updates |
| `start-hqms.ps1` | Copies code to htdocs and starts MySQL/Apache |
| `hqms-autostart.log` | Log from the starter script |
| `README.md` | Full beginner documentation |
| `VIVA_GUIDE.md` | This revision guide |

---

## 13. Final pre-viva checklist

- [ ] Apache and MySQL are running.
- [ ] `http://localhost/hospital-queue/index.php` loads.
- [ ] You know the staff login mobile and password (reset via `staff-accounts.php` if needed).
- [ ] You can take a token, call it, complete it, and show it in history.
- [ ] You can explain POST vs GET, prepared statements, `h()`, `password_hash`, sessions, and `ON DELETE CASCADE`.
- [ ] You can change the theme color and refresh interval without looking at notes.
- [ ] You can run `SHOW TABLES`, `DESCRIBE tokens`, and a `SELECT ... JOIN` in phpMyAdmin.
- [ ] You can explain the difference between the workspace copy and the `htdocs` copy.
- [ ] After any edit, you know to copy/sync to `htdocs` and press Ctrl+F5.
- [ ] You have a one-sentence answer for "What is this project?":

> "It is a PHP and MySQL hospital token system where patients book a daily per-doctor queue token, staff move tokens from waiting to called to completed, and a public board polls a JSON API to show the live queue."
