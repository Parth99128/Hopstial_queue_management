<?php
// config.php — the ONLY file you edit to match your MySQL setup.
// XAMPP defaults: user "root" with EMPTY password (DB_PASS = '').
// If your MySQL has a password, put it in DB_PASS. Nothing else to change.
// (Advanced: each value can also come from an environment variable.)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'hospital_queue');

define('SITE_NAME', 'CityCare Hospital — Queue Management');
define('AVG_CONSULT_MIN', 8);     // used in reports for avg-wait estimate
