<?php
require_once __DIR__ . '/../lib/functions.php';

$devices = db()->query("SELECT * FROM devices")->fetchAll();

foreach ($devices as $d) {
    echo "Polling {$d['name']} {$d['ip_address']}...\n";
    $is_up = $d['ping_enabled'] ? ping_host($d['ip_address']) : true;

    if ($is_up) {
        db()->prepare("UPDATE devices SET status='up', last_seen=NOW() WHERE id=?")->execute([$d['id']]);
        close_alert($d['id'], 'Device Down');
    } else {
        db()->prepare("UPDATE devices SET status='down' WHERE id=?")->execute([$d['id']]);
        open_alert($d['id'], 'critical', 'Device Down', "{$d['name']} ({$d['ip_address']}) is down.");
        continue;
    }

    if (!$d['snmp_enabled']) continue;

    // Common OIDs. Change vendor-specific OIDs as needed.
    $uptime = snmp_get_value($d, '1.3.6.1.2.1.1.3.0');
    $sysname = snmp_get_value($d, '1.3.6.1.2.1.1.5.0');

    if ($uptime !== null) save_metric($d['id'], 'uptime_ticks', preg_replace('/\D+/', '', $uptime), 'ticks');

    // Host resources CPU load average across processors.
    if (function_exists('snmp2_walk')) {
        $cpu = @snmp2_walk($d['ip_address'].":".$d['snmp_port'], $d['snmp_community'], '1.3.6.1.2.1.25.3.3.1.2');
        if (is_array($cpu) && count($cpu)) {
            $vals = [];
            foreach ($cpu as $v) {
                preg_match('/(\d+)/', $v, $m);
                if (isset($m[1])) $vals[] = (int)$m[1];
            }
            if ($vals) {
                $avg = array_sum($vals) / count($vals);
                save_metric($d['id'], 'cpu_percent', $avg, '%');
                if ($avg > 85) open_alert($d['id'], 'warning', 'High CPU', "{$d['name']} CPU is {$avg}%");
                else close_alert($d['id'], 'High CPU');
            }
        }

        $ifIn = @snmp2_walk($d['ip_address'].":".$d['snmp_port'], $d['snmp_community'], '1.3.6.1.2.1.2.2.1.10');
        $ifOut = @snmp2_walk($d['ip_address'].":".$d['snmp_port'], $d['snmp_community'], '1.3.6.1.2.1.2.2.1.16');
        if (is_array($ifIn) && is_array($ifOut)) {
            foreach ($ifIn as $idx => $rxRaw) {
                preg_match('/(\d+)/', $rxRaw, $rxm);
                preg_match('/(\d+)/', $ifOut[$idx] ?? '0', $txm);
                $ifIndex = $idx + 1;
                $rx = isset($rxm[1]) ? (int)$rxm[1] : 0;
                $tx = isset($txm[1]) ? (int)$txm[1] : 0;

                $prev = db()->prepare("SELECT rx_bytes,tx_bytes,collected_at FROM interface_traffic WHERE device_id=? AND if_index=? ORDER BY id DESC LIMIT 1");
                $prev->execute([$d['id'], $ifIndex]);
                $p = $prev->fetch();

                $rx_bps = 0; $tx_bps = 0;
                if ($p) {
                    $seconds = max(1, time() - strtotime($p['collected_at']));
                    $rx_bps = max(0, (($rx - $p['rx_bytes']) * 8) / $seconds);
                    $tx_bps = max(0, (($tx - $p['tx_bytes']) * 8) / $seconds);
                }

                $ins = db()->prepare("INSERT INTO interface_traffic(device_id,if_index,rx_bytes,tx_bytes,rx_bps,tx_bps,collected_at) VALUES(?,?,?,?,?,?,NOW())");
                $ins->execute([$d['id'], $ifIndex, $rx, $tx, $rx_bps, $tx_bps]);
            }
        }
    }
}
?>
