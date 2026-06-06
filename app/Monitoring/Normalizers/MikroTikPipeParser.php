<?php

namespace App\Monitoring\Normalizers;

class MikroTikPipeParser
{
    /**
     * Parse MikroTik pipe-delimited payload:
     * SYSTEM_|_Router:_Anvica_Demo_|_CPU:_10%_|_INTERFACE:_pppoe-out1_|_Running:_true_|_RX:_123
     */
    public static function parse(string $raw): array
    {
        $parts = explode('_|_', trim($raw));
        $system = [];
        $interface = [];
        $inInterface = false;

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === 'SYSTEM') {
                $inInterface = false;
                continue;
            }

            if ($part === 'INTERFACE') {
                $inInterface = true;
                continue;
            }

            if (! str_contains($part, ':_')) {
                continue;
            }

            [$key, $value] = explode(':_', $part, 2);

            if ($key === 'INTERFACE') {
                $inInterface = true;
                $interface['Name'] = $value;
                continue;
            }

            if ($inInterface) {
                $interface[$key] = $value;
            } else {
                $system[$key] = $value;
            }
        }

        return [
            'SYSTEM' => [
                'Router' => $system['Router'] ?? null,
                'CPU' => $system['CPU'] ?? '0',
                'RAM_Used' => $system['RAM_Used'] ?? '0/0',
                'Disk_Used' => $system['Disk_Used'] ?? '0/0',
                'Uptime' => $system['Uptime'] ?? null,
                'Temperature' => $system['Temperature'] ?? null,
            ],
            'INTERFACE' => [
                'Name' => $interface['Name'] ?? 'unknown',
                'Running' => filter_var($interface['Running'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'RX' => (int) ($interface['RX'] ?? 0),
                'TX' => (int) ($interface['TX'] ?? 0),
                'RX_Packet' => (int) ($interface['RX_Packet'] ?? 0),
                'TX_Packet' => (int) ($interface['TX_Packet'] ?? 0),
            ],
        ];
    }
}
