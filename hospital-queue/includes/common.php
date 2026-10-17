<?php
// ============================================================
// common.php — load THIS FIRST on every page and API.
// It prepares 2 variables every page needs:
//   $conn = the one shared MySQL connection (from db.php)
//   $user = logged-in person as array, or null for guests (from auth.php)
// So each page starts the same 2 lines:
//   require_once __DIR__ . '/includes/common.php';
//   require_once __DIR__ . '/includes/header.php';
// ============================================================
require_once __DIR__ . '/config.php';   // 1. DB address + site name
require_once __DIR__ . '/db.php';       // 2. connect function + helpers
require_once __DIR__ . '/auth.php';     // 3. session + login guards

$conn = db();
$user = current_user();
