@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Network Maps</h1>
        <p>Geographic overview of monitored sites.</p>
    </div>
</div>

<!-- Map Canvas -->
<div class="map-canvas-container">
    <div class="map-canvas">
        @foreach($sites as $site)
        <div class="map-node" style="left: {{ $site->x_pos }}%; top: {{ $site->y_pos }}%;">
            <div class="node-marker"></div>
            <div class="node-label">{{ $site->name }}</div>
        </div>
        @endforeach
    </div>
</div>

<!-- Sites List Card -->
<div class="sites-list-card">
    <div class="sites-list-header">
        <h3>Sites</h3>
    </div>
    
    <div class="sites-list-body">
        @forelse($sites as $site)
        <div class="site-item-row">
            <div class="site-name-wrapper">
                <span class="site-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </span>
                <span>{{ $site->name }}</span>
            </div>
            <div class="site-stats-count">
                <span class="bold">{{ $site->up_devices }}</span>/{{ $site->total_devices }} devices up
            </div>
        </div>
        @empty
        <p style="text-align: center; color: var(--text-muted); font-size: 0.9rem; padding: 2rem 0;">No sites defined.</p>
        @endforelse
    </div>
</div>
@endsection
