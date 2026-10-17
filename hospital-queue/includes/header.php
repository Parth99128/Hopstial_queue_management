<?php
// header.php — shared TOP of every page. Needs common.php FIRST
// (it gives us $conn = database, $user = logged-in staff member or null).
$active = basename($_SERVER['PHP_SELF']);   // e.g. "book-token.php" → highlights that link

// Staff land on History (dashboard + details), guests land on Home.
// Everyone sees the Display Board; Get Token stays guest-only.
$nav_public = ['queue-live.php' => 'Display Board'];
$is_login = ($active === 'login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h(SITE_NAME) ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="navbar">
  <div class="wrap nav-inner">
    <a class="brand" href="index.php"><span class="brand-cross">+</span> CityCare <small>Queue</small></a>
    <button class="nav-toggle" id="navToggle" aria-label="Menu">&#9776;</button>
    <nav class="nav-links" id="navLinks">
      <?php if ($user): ?>
        <a class="<?= $active === 'history.php' ? 'active' : '' ?>" href="history.php">History</a>
      <?php else: ?>
        <a class="<?= $active === 'index.php' ? 'active' : '' ?>" href="index.php">Home</a>
      <?php endif; ?>
      <?php foreach ($nav_public as $file => $label): ?>
        <a class="<?= $file === $active ? 'active' : '' ?>" href="<?= $file ?>"><?= h($label) ?></a>
      <?php endforeach; ?>
      <?php if ($user): ?>
        <a class="<?= $active === 'staff-queue.php' ? 'active' : '' ?>" href="staff-queue.php">Queue</a>
        <a class="<?= $active === 'staff-doctors.php' ? 'active' : '' ?>" href="staff-doctors.php">Doctors</a>
        <?php if ($user['role'] === 'admin'): ?>
          <a class="<?= $active === 'staff-accounts.php' ? 'active' : '' ?>" href="staff-accounts.php">Accounts</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-sm">Logout (<?= h($user['name']) ?> · <?= h($user['role']) ?>)</a>
      <?php else: ?>
        <a class="<?= $active === 'book-token.php' ? 'active' : '' ?>" href="book-token.php" style="font-weight:700">Get Token</a>
        <a class="<?= $is_login ? 'active' : '' ?>" href="login.php" class="btn btn-sm">Staff Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<div class="ticker" id="ticker">Loading live queue…</div>
<main class="wrap">
