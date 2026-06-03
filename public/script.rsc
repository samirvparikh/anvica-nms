# System Resource Monitoring

:local routerName [/system identity get name]
:local reportUrl "http://nms.anvica.in/api/test"

:local cpuLoad [/system resource get cpu-load]
:local freeMemory [/system resource get free-memory]
:local totalMemory [/system resource get total-memory]
:local freeHdd [/system resource get free-hdd-space]
:local totalHdd [/system resource get total-hdd-space]
:local uptime [/system resource get uptime]

:local usedMemory ($totalMemory - $freeMemory)
:local usedHdd ($totalHdd - $freeHdd)

:log info ("SYSTEM | Router: " . $routerName . " | CPU: " . $cpuLoad . "% | RAM Used: " . $usedMemory . "/" . $totalMemory . " | Disk Used: " . $usedHdd . "/" . $totalHdd . " | Uptime: " . $uptime)

:foreach i in=[/interface find] do={

    :local ifName [/interface get $i name]
    :local ifRunning [/interface get $i running]
    :local rxByte [/interface get $i rx-byte]
    :local txByte [/interface get $i tx-byte]
    :local rxPacket [/interface get $i rx-packet]
    :local txPacket [/interface get $i tx-packet]

    :local postData ("SYSTEM | Router: " . $routerName . " | CPU: " . $cpuLoad . "% | RAM Used: " . $usedMemory . "/" . $totalMemory . " | Disk Used: " . $usedHdd . "/" . $totalHdd . " | Uptime: " . $uptime . " | INTERFACE: " . $ifName . " | Running: " . $ifRunning . " | RX: " . $rxByte . " | TX: " . $txByte . " | RX Packet: " . $rxPacket . " | TX Packet: " . $txPacket)
/tool fetch url=$reportUrl http-method=post http-data=$postData keep-result=no
}