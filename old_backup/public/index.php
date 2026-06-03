<?php
session_start();
require_once __DIR__ . '/../config.php';
// if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$counts = db()->query("SELECT status, COUNT(*) total FROM devices GROUP BY status")->fetchAll();
$summary = ['up'=>0,'down'=>0,'warning'=>0,'unknown'=>0];
foreach ($counts as $c) $summary[$c['status']] = $c['total'];

$devices = db()->query("SELECT d.*, l.name location_name FROM devices d LEFT JOIN locations l ON l.id=d.location_id ORDER BY d.name")->fetchAll();
$alerts = db()->query("SELECT a.*, d.name device_name FROM alerts a LEFT JOIN devices d ON d.id=a.device_id WHERE a.status='open' ORDER BY a.created_at DESC LIMIT 10")->fetchAll();
?>
<!doctype html>
<html>
<head>
<title><?=APP_NAME?></title>
<link rel="stylesheet" href="assets/style.css">
<meta http-equiv="refresh" content="60">
</head>
<body>
<div class="topbar"><b><?=APP_NAME?></b><a href="devices.php">Devices</a><a href="reports.php">Reports</a><a href="logout.php">Logout</a></div>
<div class="container">
<h1>Network Dashboard</h1>
<div class="cards">
  <div class="card green"><h2><?=$summary['up']?></h2><p>Up Devices</p></div>
  <div class="card red"><h2><?=$summary['down']?></h2><p>Down Devices</p></div>
  <div class="card orange"><h2><?=$summary['warning']?></h2><p>Warning</p></div>
  <div class="card"><h2><?=array_sum($summary)?></h2><p>Total Devices</p></div>
</div>

<h2>Open Alerts</h2>
<table><tr><th>Time</th><th>Device</th><th>Severity</th><th>Title</th><th>Message</th></tr>
<?php foreach($alerts as $a): ?>
<tr><td><?=$a['created_at']?></td><td><?=$a['device_name']?></td><td><?=$a['severity']?></td><td><?=$a['title']?></td><td><?=$a['message']?></td></tr>
<?php endforeach; ?>
</table>

<h2>Devices</h2>
<table><tr><th>Name</th><th>IP</th><th>Type</th><th>Location</th><th>Status</th><th>Last Seen</th></tr>
<?php foreach($devices as $d): ?>
<tr>
<td><?=$d['name']?></td><td><?=$d['ip_address']?></td><td><?=$d['type']?></td><td><?=$d['location_name']?></td>
<td><span class="badge <?=$d['status']?>"><?=$d['status']?></span></td><td><?=$d['last_seen']?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
</body></html>
