<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'type',
        'title',
        'description',
        'status',
        'priority',
        'impact',
        'urgency',
        'source',
        'customer_id',
        'assigned_to',
        'device_id',
        'sla_policy_id',
        'response_sla_deadline',
        'resolution_sla_deadline',
        'responded_at',
        'resolved_at',
        'closed_at',
        
        // Incident specific
        'contact_person',
        'contact_number',
        'sub_category',
        'service_impacted',
        'ci_service',
        'affected_users',
        'business_impact',
        'alarm_alert_id',
        'detected_time',
        'incident_start_time',
        'planned_outage',
        'assign_group',
        
        // Change specific
        'change_category',
        'risk_description',
        'impact_on_sla',
        'rollback_plan',
        'backout_time_minutes',
        'change_planned_start',
        'change_planned_end',
        'planned_downtime',
        'change_window',
        'implementation_steps',
    ];

    protected function casts(): array
    {
        return [
            'response_sla_deadline' => 'datetime',
            'resolution_sla_deadline' => 'datetime',
            'responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'detected_time' => 'datetime',
            'incident_start_time' => 'datetime',
            'change_planned_start' => 'datetime',
            'change_planned_end' => 'datetime',
            'planned_outage' => 'boolean',
            'planned_downtime' => 'boolean',
            'impact_on_sla' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function breaches(): HasMany
    {
        return $this->hasMany(SlaBreach::class);
    }

    public function calculateSlaDeadlines(): void
    {
        if (!$this->slaPolicy) {
            return;
        }

        $now = $this->created_at ? Carbon::parse($this->created_at) : Carbon::now();
        $responseMinutes = $this->slaPolicy->response_time_minutes;
        $resolutionMinutes = $this->slaPolicy->resolution_time_minutes;

        // Calculate initial deadlines
        $responseDeadline = $now->copy()->addMinutes($responseMinutes);
        $resolutionDeadline = $now->copy()->addMinutes($resolutionMinutes);

        // Check if there are active maintenance windows for this device that overlap with the ticket's lifespan
        if ($this->device_id) {
            $overlappingWindows = MaintenanceWindow::where('primary_device_id', $this->device_id)
                ->where('exclude_sla', true)
                ->where('start_time', '<', $resolutionDeadline)
                ->where('end_time', '>', $now)
                ->get();

            foreach ($overlappingWindows as $window) {
                // Shift deadlines forward by the duration of the maintenance window
                $downtime = $window->expected_downtime_minutes ?? $window->end_time->diffInMinutes($window->start_time);
                $responseDeadline->addMinutes($downtime);
                $resolutionDeadline->addMinutes($downtime);
            }
        }

        $this->response_sla_deadline = $responseDeadline;
        $this->resolution_sla_deadline = $resolutionDeadline;
    }
}
