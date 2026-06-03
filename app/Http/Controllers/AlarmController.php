<?php

namespace App\Http\Controllers;

use App\Models\Alarm;
use Illuminate\Http\Request;

class AlarmController extends Controller
{
    /**
     * Display a listing of alarms.
     */
    public function index()
    {
        $criticalCount = Alarm::where('severity', 'Critical')->where('status', 'Open')->count();
        $warningCount = Alarm::where('severity', 'Warning')->where('status', 'Open')->count();
        $ackCount = Alarm::where('status', 'Acknowledged')->count();

        $alarms = Alarm::orderBy('status', 'asc')->orderBy('created_at', 'desc')->get();

        return view('alarms.index', compact('criticalCount', 'warningCount', 'ackCount', 'alarms'));
    }

    /**
     * Acknowledge the specified alarm.
     */
    public function acknowledge(Alarm $alarm)
    {
        $alarm->update(['status' => 'Acknowledged']);

        return redirect()->route('alarms.index')->with('success', 'Alarm acknowledged successfully.');
    }
}
