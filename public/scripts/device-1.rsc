:local targetIP "192.168.5.1"
:local router [/system identity get name]
:local community "Anvica_NMS"
:local nmsURL "http://localhost/api/device/data"

# Interface indexes to monitors...
:local ifIndexes {"3";"5";"6";"8";"9"}

:local pingResult 0

:do {
    :set pingResult [/ping address=$targetIP count=2]
} on-error={
    :set pingResult 0
}

:if ($pingResult = 0) do={

    :local json ("{\"target_ip\":\"".$targetIP."\",\"Router\":\"".$router."\",\"Ping_Status\":\"DOWN\"}")

    /tool fetch \
        url=$nmsURL \
        http-method=post \
        http-header-field="Content-Type: application/json" \
        http-data=$json \
        keep-result=no

    :log warning ("NMS: Device Down - " . $targetIP)

} else={
    # ------------------------------
    # DEVICE METRICS
    # ------------------------------

    :local hostData ""

    # ------------------------------
    # INTERFACE JSON ARRAY
    # ------------------------------

    :local interfaces "["
    :local first true

    :foreach idx in=$ifIndexes do={

        :do {

            :local ifName (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.2.".$idx) as-value])->"value")

            :local ifStatus (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.8.".$idx) as-value])->"value")

            :local rxBytes (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.10.".$idx) as-value])->"value")

            :local txBytes (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.16.".$idx) as-value])->"value")

            :local rxPackets (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.11.".$idx) as-value])->"value")

            :local txPackets (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.17.".$idx) as-value])->"value")

            :if (!$first) do={
                :set interfaces ($interfaces . ",")
            }

            :set first false

            :set interfaces ($interfaces . "{")
            :set interfaces ($interfaces . "\"if_index\":\"".$idx."\",")
            :set interfaces ($interfaces . "\"if_name\":\"".$ifName."\",")
            :set interfaces ($interfaces . "\"status\":\"".$ifStatus."\",")
            :set interfaces ($interfaces . "\"rx_bytes\":\"".$rxBytes."\",")
            :set interfaces ($interfaces . "\"tx_bytes\":\"".$txBytes."\",")
            :set interfaces ($interfaces . "\"rx_packets\":\"".$rxPackets."\",")
            :set interfaces ($interfaces . "\"tx_packets\":\"".$txPackets."\"")
            :set interfaces ($interfaces . "}")

        } on-error={
            :log warning ("Interface Poll Failed: " . $idx)
        }
    }

    :set interfaces ($interfaces . "]")

    # ------------------------------
    # FINAL JSON
    # ------------------------------

    :local json "{"

    :set json ($json . "\"target_ip\":\"".$targetIP."\",")
    :set json ($json . "\"Router\":\"".$router."\",")

    :set json ($json . "\"Ping_Status\":\"UP\",")
    :set json ($json . "\"interfaces\":" . $interfaces)

    :set json ($json . "}")

    :log info $json

    /tool fetch \
        url=$nmsURL \
        http-method=post \
        http-header-field="Content-Type: application/json" \
        http-data=$json \
        keep-result=no

    :log info ("NMS Data Sent: " . $targetIP)
}
