<?php header('Content-Type: application/json');
require_once __DIR__ . '/../includes/common.php';   // public display data — no login needed
$conn = db();

// NOW SERVING: called tokens from today, oldest first
$now = [];
$q = $conn->query("SELECT t.token_no, t.patient_name, d.name doctor, d.room
  FROM tokens t JOIN doctors d ON d.id=t.doctor_id
  WHERE t.status='called' AND DATE(t.created_at)=CURDATE()
  ORDER BY t.created_at ASC LIMIT 5");
while ($r = $q->fetch_assoc()) $now[] = $r;

// NEXT: waiting tokens from today, oldest first (plain FIFO per board)
$queue = [];
$q2 = $conn->query("SELECT t.token_no, t.patient_name, d.name doctor, d.room
  FROM tokens t JOIN doctors d ON d.id=t.doctor_id
  WHERE t.status='waiting' AND DATE(t.created_at)=CURDATE()
  ORDER BY t.created_at ASC LIMIT 20");
while ($r = $q2->fetch_assoc()) { $r['status_label'] = 'Waiting'; $queue[] = $r; }

$wc = $conn->query("SELECT COUNT(*) c FROM tokens WHERE DATE(created_at)=CURDATE() AND status='waiting'")->fetch_assoc()['c'] ?? 0;
echo json_encode(['now_serving' => $now, 'queue' => $queue, 'waiting_count' => (int)$wc]);
