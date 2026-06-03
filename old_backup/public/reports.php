<?php
session_start();
require_once __DIR__ . '/../config.php';
// if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$rows = db()->query("
SELECT d.name, d.ip_address,
SUM(CASE WHEN a.id IS NULL THEN 0 ELSE TIMESTAMPDIFF(MINUTE,a.created_at,COALESCE(a.closed_at,NOW())) END) down_minutes
FROM devices d
LEFT JOIN alerts a ON a.device_id=d.id AND a.title='Device Down'
GROUP BY d.id
ORDER BY d.name")->fetchAll();
?>
<!doctype html><html><head><title>Reports</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div class="topbar"><b><?=APP_NAME?></b><a href="index.php">Dashboard</a></div>
<div class="container"><h1>SLA Availability Report</h1>
<table><tr><th>Device</th><th>IP</th><th>Down Minutes</th><th>Approx Availability % / 30 Days</th></tr>
<?php foreach($rows as $r): $avail = max(0, 100 - (($r['down_minutes'] / (30*24*60))*100)); ?>
<tr><td><?=$r['name']?></td><td><?=$r['ip_address']?></td><td><?=$r['down_minutes']?></td><td><?=number_format($avail,2)?>%</td></tr>
<?php endforeach; ?>
</table></div></body></html>
