<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function ticketsIndex(Request $request)
    {
        $tickets = Ticket::latest()->get();
        return view('service-desk.tickets.index', compact('tickets'));
    }

    public function incidentsIndex(Request $request)
    {
        $query = Ticket::where('type', 'incident');

        // Simple sorting
        $sort = $request->query('sort', 'created_at');
        $dir = $request->query('direction', 'desc');
        if (in_array($sort, ['ticket_number', 'title', 'priority', 'status', 'created_at'])) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        // Filtering
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        $incidents = $query->get();

        if ($incidents->isEmpty()) {
            $incidents = $this->getMockIncidents();
        }

        return view('service-desk.incidents.index', compact('incidents', 'sort', 'dir'));
    }

    public function incidentsCreate()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $devices = Device::all();
            $users = User::all();
        } else {
            $devices = Device::where('customer_id', $user->id)->get();
            $users = User::where('id', $user->id)->get();
        }
        $policies = SlaPolicy::all();
        return view('service-desk.incidents.create', compact('devices', 'users', 'policies'));
    }

    public function incidentsStore(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            $request->merge(['customer_id' => auth()->id()]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'required|exists:users,id',
            'priority' => 'required|in:critical,high,medium,low',
        ]);

        $policy = SlaPolicy::first() ?? SlaPolicy::create([
            'name' => 'Standard Incident SLA',
            'response_time_minutes' => 15,
            'resolution_time_minutes' => 120,
        ]);

        $ticket = new Ticket([
            'ticket_number' => 'INC-' . mt_rand(1000, 9999),
            'type' => 'incident',
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => 'new',
            'priority' => $request->input('priority'),
            'impact' => $request->input('impact', 'medium'),
            'urgency' => $request->input('urgency', 'medium'),
            'source' => $request->input('source', 'Manual'),
            'customer_id' => $request->input('customer_id'),
            'assigned_to' => $request->input('assigned_to'),
            'device_id' => $request->input('device_id'),
            'sla_policy_id' => $policy->id,
            'contact_person' => $request->input('contact_person'),
            'contact_number' => $request->input('contact_number'),
            'sub_category' => $request->input('sub_category'),
            'service_impacted' => $request->input('service_impacted'),
            'ci_service' => $request->input('ci_service'),
            'affected_users' => (int) $request->input('affected_users', 0),
            'business_impact' => $request->input('business_impact'),
            'alarm_alert_id' => $request->input('alarm_alert_id'),
            'detected_time' => $request->filled('detected_time') ? Carbon::parse($request->input('detected_time')) : null,
            'incident_start_time' => $request->filled('incident_start_time') ? Carbon::parse($request->input('incident_start_time')) : null,
            'planned_outage' => $request->has('planned_outage'),
            'assign_group' => $request->input('assign_group'),
        ]);

        $ticket->calculateSlaDeadlines();
        $ticket->save();

        return redirect()->route('incidents.index')->with('success', 'Incident created successfully.');
    }

    public function problemsIndex(Request $request)
    {
        $problems = Ticket::where('type', 'problem')->latest()->get();
        if ($problems->isEmpty()) {
            $problems = $this->getMockProblems();
        }
        return view('service-desk.problems.index', compact('problems'));
    }

    public function changesIndex(Request $request)
    {
        $query = Ticket::where('type', 'change');

        // Simple sorting
        $sort = $request->query('sort', 'created_at');
        $dir = $request->query('direction', 'desc');
        if (in_array($sort, ['ticket_number', 'title', 'priority', 'status', 'created_at'])) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        $changes = $query->get();

        if ($changes->isEmpty()) {
            $changes = $this->getMockChanges();
        }

        return view('service-desk.changes.index', compact('changes', 'sort', 'dir'));
    }

    public function changesCreate()
    {
        $devices = Device::all();
        $users = User::all();
        return view('service-desk.changes.create', compact('devices', 'users'));
    }

    public function changesStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'required|exists:users,id',
            'priority' => 'required|in:critical,high,medium,low',
        ]);

        $policy = SlaPolicy::first() ?? SlaPolicy::create([
            'name' => 'Standard Change SLA',
            'response_time_minutes' => 1440,
            'resolution_time_minutes' => 10080,
        ]);

        Ticket::create([
            'ticket_number' => 'CHG-' . mt_rand(1000, 9999),
            'type' => 'change',
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => 'new',
            'priority' => $request->input('priority'),
            'impact' => $request->input('impact', 'medium'),
            'urgency' => $request->input('urgency', 'medium'),
            'customer_id' => $request->input('customer_id'),
            'assigned_to' => $request->input('assigned_to'),
            'device_id' => $request->input('device_id'),
            'sla_policy_id' => $policy->id,
            'change_category' => $request->input('change_category'),
            'risk_description' => $request->input('risk_description'),
            'impact_on_sla' => $request->has('impact_on_sla'),
            'rollback_plan' => $request->input('rollback_plan'),
            'backout_time_minutes' => (int) $request->input('backout_time_minutes', 30),
            'change_planned_start' => $request->filled('change_planned_start') ? Carbon::parse($request->input('change_planned_start')) : null,
            'change_planned_end' => $request->filled('change_planned_end') ? Carbon::parse($request->input('change_planned_end')) : null,
            'planned_downtime' => $request->has('planned_downtime'),
            'change_window' => $request->input('change_window'),
            'implementation_steps' => $request->input('implementation_steps'),
        ]);

        return redirect()->route('changes.index')->with('success', 'Change Request created successfully.');
    }

    public function knowledgeBaseIndex()
    {
        return view('service-desk.knowledge-base.index');
    }

    protected function getMockIncidents()
    {
        return collect([
            (object)[
                'id' => 1,
                'ticket_number' => 'INC-1024',
                'title' => 'VPN Connectivity Issue - Mumbai DC',
                'priority' => 'critical',
                'status' => 'in_progress',
                'customer' => (object)['name' => 'Western Railway'],
                'assignedTo' => (object)['name' => 'Vijay Kumar'],
                'device' => (object)['name' => 'Core-Router-01'],
                'created_at' => Carbon::now()->subHours(1),
            ],
            (object)[
                'id' => 2,
                'ticket_number' => 'INC-1021',
                'title' => 'High Packet Loss - Delhi DC',
                'priority' => 'high',
                'status' => 'new',
                'customer' => (object)['name' => 'Northern Railway'],
                'assignedTo' => null,
                'device' => (object)['name' => 'Firewall-02'],
                'created_at' => Carbon::now()->subHours(2),
            ],
        ]);
    }

    protected function getMockProblems()
    {
        return collect([
            (object)[
                'id' => 1,
                'ticket_number' => 'PRB-2026-001',
                'title' => 'Repeated Core-Router-01 Connectivity Failures',
                'priority' => 'high',
                'status' => 'under_investigation',
                'created_at' => Carbon::now()->subDays(5),
            ],
        ]);
    }

    protected function getMockChanges()
    {
        return collect([
            (object)[
                'id' => 1,
                'ticket_number' => 'CHG-2026-0001',
                'title' => 'Core Router-01 Firmware Upgrade',
                'priority' => 'high',
                'status' => 'scheduled',
                'created_at' => Carbon::now()->subDays(2),
            ],
        ]);
    }
}
