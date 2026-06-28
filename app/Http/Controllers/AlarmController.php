<?php

namespace App\Http\Controllers;

use App\Models\Alarm;
use App\Models\Device;
use Illuminate\Http\Request;

class AlarmController extends Controller
{
    public function index()
    {
        $criticalCount = Alarm::where('severity', 'Critical')->where('status', 'Open')->count();
        $warningCount = Alarm::where('severity', 'Warning')->where('status', 'Open')->count();
        $ackCount = Alarm::where('status', 'Acknowledged')->count();

        $alarms = Alarm::orderBy('status', 'asc')->orderByDesc('created_at')->get();
        $isAdmin = (bool) request()->user()?->isAdmin();

        return view('alarms.index', [
            'criticalCount' => $criticalCount,
            'warningCount' => $warningCount,
            'ackCount' => $ackCount,
            'alarms' => $alarms,
            'isAdmin' => $isAdmin,
            'devices' => $isAdmin ? Device::orderBy('name')->get(['id', 'asset_name']) : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedAlarm($request);

        Alarm::create([
            'device_name' => $validated['device_name'],
            'message' => $validated['message'],
            'severity' => $validated['severity'],
            'status' => $validated['status'] ?? 'Open',
        ]);

        return redirect()->route('alarms.index')->with('success', 'Alarm created successfully.');
    }

    public function update(Request $request, Alarm $alarm)
    {
        $validated = $this->validatedAlarm($request);

        $alarm->update([
            'device_name' => $validated['device_name'],
            'message' => $validated['message'],
            'severity' => $validated['severity'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('alarms.index')->with('success', 'Alarm updated successfully.');
    }

    public function destroy(Alarm $alarm)
    {
        $alarm->delete();

        return redirect()->route('alarms.index')->with('success', 'Alarm deleted successfully.');
    }

    public function acknowledge(Alarm $alarm)
    {
        $alarm->update(['status' => 'Acknowledged']);

        return redirect()->route('alarms.index')->with('success', 'Alarm acknowledged successfully.');
    }

    /**
     * @return array{device_name: string, message: string, severity: string, status: string}
     */
    protected function validatedAlarm(Request $request): array
    {
        return $request->validate([
            'device_name' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'severity' => 'required|in:Critical,Warning',
            'status' => 'required|in:Open,Acknowledged',
        ]);
    }
}
