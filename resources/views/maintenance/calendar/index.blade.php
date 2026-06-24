@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Maintenance Calendar</h1>
        <p>Monthly overview of scheduled maintenance activities, service breaks, and hardware upgrades.</p>
    </div>
    <a href="{{ route('maintenance.preventive.create') }}" class="btn-add">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        + Schedule Maintenance
    </a>
</div>

<div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); margin-bottom: 2rem;">
    <!-- Calendar Month Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-regular fa-calendar-days" style="color: var(--primary);"></i> June 2026
        </h2>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn-secondary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem; border-radius: 4px; border: 1px solid var(--border-color); background: white; cursor: pointer;">Prev Month</button>
            <button class="btn-secondary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem; border-radius: 4px; border: 1px solid var(--border-color); background: white; cursor: pointer;">Next Month</button>
        </div>
    </div>

    <!-- Calendar Month Agenda -->
    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
        @foreach($events as $event)
        <div style="display: flex; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <!-- Day Block -->
            <div style="width: 80px; background-color: var(--bg-main); display: flex; flex-direction: column; align-items: center; justify-content: center; border-right: 1px solid var(--border-color); padding: 1rem;">
                <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">
                    {{ is_string($event->start_time) ? Carbon\Carbon::parse($event->start_time)->format('D') : $event->start_time->format('D') }}
                </span>
                <span style="font-size: 1.75rem; font-weight: 800; color: var(--text-dark); font-family: 'Outfit';">
                    {{ is_string($event->start_time) ? Carbon\Carbon::parse($event->start_time)->format('d') : $event->start_time->format('d') }}
                </span>
            </div>
            
            <!-- Event details -->
            <div style="flex-grow: 1; padding: 1.25rem; display: flex; flex-direction: column; justify-content: center; gap: 0.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-dark);">{{ $event->title }}</h3>
                    <span class="status-badge {{ $event->status === 'scheduled' ? 'warning' : 'active' }}" style="font-size: 0.7rem;">
                        {{ ucfirst($event->status) }}
                    </span>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted);">
                    <i class="fa-solid fa-server" style="margin-right: 0.4rem;"></i> {{ $event->device->name ?? 'None' }} &nbsp;•&nbsp; 
                    <i class="fa-solid fa-clock" style="margin-right: 0.4rem; margin-left: 0.4rem;"></i> 
                    {{ is_string($event->start_time) ? Carbon\Carbon::parse($event->start_time)->format('H:i') : $event->start_time->format('H:i') }} - 
                    {{ is_string($event->end_time) ? Carbon\Carbon::parse($event->end_time)->format('H:i') : $event->end_time->format('H:i') }}
                    ({{ $event->expected_downtime_minutes }}m downtime)
                </p>
                <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                    <span class="status-badge" style="font-size: 0.65rem; background-color: var(--bg-up); color: var(--status-up); border: 1px solid rgba(34,197,94,0.1);">
                        Category: {{ $event->category ?? 'Network' }}
                    </span>
                    @if($event->exclude_sla)
                    <span class="status-badge" style="font-size: 0.65rem; background-color: var(--bg-up); color: var(--primary); border: 1px solid rgba(116,198,43,0.1);">
                        SLA Exemption Active
                    </span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
