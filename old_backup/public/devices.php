<?php
session_start();
require_once __DIR__ . '/../config.php';
// if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare("INSERT INTO devices(name,ip_address,type,vendor,snmp_community) VALUES(?,?,?,?,?)");
    $stmt->execute([$_POST['name'], $_POST['ip_address'], $_POST['type'], $_POST['vendor'], $_POST['snmp_community']]);
}
$devices = db()->query("SELECT * FROM devices ORDER BY id DESC")->fetchAll();
?>
<!doctype html><html><head><title>Devices</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div class="topbar"><b><?=APP_NAME?></b><a href="index.php">Dashboard</a></div>
<div class="container"><h1>Add Device</h1>
<form method="post" class="formgrid">
<input name="name" placeholder="Device Name" required>
<input name="ip_address" placeholder="IP Address" required>
<select name="type"><option>router</option><option>switch</option><option>firewall</option><option>server</option><option>cctv</option><option>ups</option><option>access_point</option><option>other</option></select>
<input name="vendor" placeholder="Vendor">
<input name="snmp_community" placeholder="SNMP Community" value="public">
<button>Add Device</button>
</form>
<h2>Device List</h2>
<table><tr><th>Name</th><th>IP</th><th>Type</th><th>Vendor</th><th>Status</th></tr>
<?php foreach($devices as $d): ?><tr><td><?=$d['name']?></td><td><?=$d['ip_address']?></td><td><?=$d['type']?></td><td><?=$d['vendor']?></td><td><?=$d['status']?></td></tr><?php endforeach; ?>
</table></div></body></html>
