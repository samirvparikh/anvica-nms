<?php

namespace App\Services;

use App\Models\Alarm;
use App\Models\Device;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AlarmActionService
{
    public const ACTION_CONVERT_TO_TICKET = 'convert_to_ticket';

    public const ACTION_RESOLVED = 'resolved';

    public function applyUserAction(Alarm $alarm, User $user, string $action, ?string $remarks = null): string
    {
        return match ($action) {
            self::ACTION_CONVERT_TO_TICKET => $this->convertToTicket($alarm, $user, $remarks),
            self::ACTION_RESOLVED => $this->resolve($alarm, $user, $remarks),
            default => throw new InvalidArgumentException('Invalid alarm action.'),
        };
    }

    public function convertToTicket(Alarm $alarm, User $user, ?string $remarks = null): string
    {
        if ($alarm->ticket_id) {
            return 'Alarm is already converted to a ticket.';
        }

        if ($alarm->status === 'Resolved') {
            return 'Alarm is already resolved.';
        }

        return DB::transaction(function () use ($alarm, $user, $remarks) {
            $device = Device::query()
                ->where(function ($query) use ($alarm) {
                    $query->where('asset_name', $alarm->device_name)
                        ->orWhere('hostname', $alarm->device_name);
                })
                ->first();

            $policy = SlaPolicy::query()->orderBy('id')->first()
                ?? SlaPolicy::create([
                    'name' => 'Standard Incident SLA',
                    'response_time_minutes' => 15,
                    'resolution_time_minutes' => 120,
                ]);

            $description = $alarm->message;
            if (filled($remarks)) {
                $description .= "\n\nRemarks: ".$remarks;
            }

            $ticket = new Ticket([
                'ticket_number' => 'INC-'.mt_rand(1000, 9999),
                'type' => 'incident',
                'title' => 'Alarm: '.$alarm->device_name,
                'description' => $description,
                'status' => 'new',
                'priority' => strcasecmp($alarm->severity, 'Critical') === 0 ? 'critical' : 'high',
                'impact' => strcasecmp($alarm->severity, 'Critical') === 0 ? 'high' : 'medium',
                'urgency' => strcasecmp($alarm->severity, 'Critical') === 0 ? 'high' : 'medium',
                'source' => 'Alarm',
                'customer_id' => $device?->customer_id ?? $user->id,
                'assigned_to' => $user->id,
                'device_id' => $device?->id,
                'sla_policy_id' => $policy->id,
                'alarm_alert_id' => (string) $alarm->id,
                'detected_time' => $alarm->created_at,
                'incident_start_time' => now(),
                'business_impact' => $alarm->message,
            ]);
            $ticket->setRelation('slaPolicy', $policy);
            $ticket->calculateSlaDeadlines();
            $ticket->save();

            $alarm->update([
                'status' => 'Acknowledged',
                'remarks' => $remarks,
                'ticket_id' => $ticket->id,
            ]);

            return 'Alarm converted to ticket '.$ticket->ticket_number.' successfully.';
        });
    }

    public function resolve(Alarm $alarm, User $user, ?string $remarks = null): string
    {
        if ($alarm->status === 'Resolved') {
            return 'Alarm is already resolved.';
        }

        $alarm->update([
            'status' => 'Resolved',
            'remarks' => $remarks,
            'resolved_at' => now(),
        ]);

        return 'Alarm resolved successfully.';
    }
}
