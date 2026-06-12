<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\UserScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MapController extends Controller
{
    public function __construct(
        protected UserScopeService $userScope,
    ) {}

    /**
     * Display the network map view (sites derived from user-scoped devices).
     */
    public function index(Request $request)
    {
        $devices = $this->userScope
            ->devicesQuery($request->user())
            ->orderBy('location')
            ->orderBy('name')
            ->get();

        $sites = $this->sitesFromDevices($devices);

        return view('maps.index', compact('sites'));
    }

    /**
     * @param  Collection<int, Device>  $devices
     * @return Collection<int, object{name: string, up_devices: int, total_devices: int, x_pos: int, y_pos: int}>
     */
    private function sitesFromDevices(Collection $devices): Collection
    {
        if ($devices->isEmpty()) {
            return collect();
        }

        $grouped = $devices->groupBy(function (Device $device) {
            $location = trim((string) $device->location);

            return $location !== '' ? $location : 'Unassigned';
        });

        $count = $grouped->count();
        $cols = max(1, (int) ceil(sqrt($count)));
        $rows = max(1, (int) ceil($count / $cols));
        $index = 0;

        return $grouped->map(function (Collection $locationDevices, string $location) use ($cols, $rows, &$index) {
            $row = intdiv($index, $cols);
            $col = $index % $cols;
            $index++;

            $xPos = $cols > 1 ? 12 + (int) round($col * (76 / ($cols - 1))) : 50;
            $yPos = $rows > 1 ? 12 + (int) round($row * (76 / ($rows - 1))) : 50;

            return (object) [
                'name' => $location,
                'total_devices' => $locationDevices->count(),
                'up_devices' => $locationDevices
                    ->where('health_status', Device::HEALTH_UP)
                    ->count(),
                'x_pos' => $xPos,
                'y_pos' => $yPos,
            ];
        })->values();
    }
}
