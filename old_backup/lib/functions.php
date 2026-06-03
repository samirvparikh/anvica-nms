<?php
require_once __DIR__ . '/../config.php';

function check_session() {
    if (!isset($_SESSION['user_id'])) {
        // header('Location: login.php');
        exit;
    }
}

function ping_host($ip, $timeout = 1) {
    $cmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
        ? "ping -n 1 -w ".($timeout*1000)." ".escapeshellarg($ip)
        : "ping -c 1 -W ".$timeout." ".escapeshellarg($ip);
    exec($cmd, $output, $status);
    return $status === 0;
}

function snmp_get_value($device, $oid) {
    if (!function_exists('snmp2_get')) return null;
    $value = @snmp2_get($device['ip_address'].":".$device['snmp_port'], $device['snmp_community'], $oid, 1000000, 1);
    if ($value === false) return null;
    return trim(preg_replace('/^[A-Z\-]+:\s*/', '', $value), '" ');
}

function save_metric($device_id, $key, $value, $unit = '') {
    $stmt = db()->prepare("INSERT INTO metrics(device_id, metric_key, metric_value, unit, collected_at) VALUES(?,?,?,?,NOW())");
    $stmt->execute([$device_id, $key, is_numeric($value) ? $value : null, $unit]);
}

function open_alert($device_id, $severity, $title, $message) {
    $check = db()->prepare("SELECT id FROM alerts WHERE device_id <=> ? AND title=? AND status='open' LIMIT 1");
    $check->execute([$device_id, $title]);
    if ($check->fetch()) return;
    $stmt = db()->prepare("INSERT INTO alerts(device_id,severity,title,message,status,created_at) VALUES(?,?,?,?, 'open', NOW())");
    $stmt->execute([$device_id, $severity, $title, $message]);
    send_alert_notification($title, $message);
}

function close_alert($device_id, $title) {
    $stmt = db()->prepare("UPDATE alerts SET status='closed', closed_at=NOW() WHERE device_id <=> ? AND title=? AND status='open'");
    $stmt->execute([$device_id, $title]);
}

function get_setting($key, $default='') {
    $stmt = db()->prepare("SELECT setting_value FROM settings WHERE setting_key=?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function send_alert_notification($title, $message) {
    $to = get_setting('email_to');
    $from = get_setting('email_from');
    if ($to) {
        @mail($to, "[Anvica NMS] ".$title, $message, "From: ".$from);
    }

    foreach (['sms_webhook_url','whatsapp_webhook_url'] as $key) {
        $url = get_setting($key);
        if ($url) {
            $payload = json_encode(['title'=>$title, 'message'=>$message, 'time'=>date('Y-m-d H:i:s')]);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5
            ]);
            @curl_exec($ch);
            @curl_close($ch);
        }
    }
}
?>
