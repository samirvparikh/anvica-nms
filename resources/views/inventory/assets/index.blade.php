@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Asset Inventory</h1>
        <p>Manage physical network devices, serial numbers, locations, and manufacturers mapped inside NMS.</p>
    </div>
    <a href="{{ route('inventory.warranty.index') }}" class="btn-add">
        <i class="fa-solid fa-file-shield" style="margin-right: 0.5rem;"></i> Manage Warranty & AMC
    </a>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">Asset ID</th>
                <th style="padding: 1rem 0.5rem;">Asset Name</th>
                <th style="padding: 1rem 0.5rem;">IP Address</th>
                <th style="padding: 1rem 0.5rem;">Manufacturer</th>
                <th style="padding: 1rem 0.5rem;">Model Number</th>
                <th style="padding: 1rem 0.5rem;">Serial Number</th>
                <th style="padding: 1rem 0.5rem;">Status</th>
                <th style="padding: 1rem 0.5rem;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">{{ $asset->asset_id ?? 'AST-' . (10000 + $asset->id) }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $asset->name }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $asset->ip_address }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $asset->manufacturer ?? 'Cisco Systems' }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $asset->model_number ?? 'ISR-4331/K9' }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $asset->serial_number ?? 'FTX' . mt_rand(100000, 999999) }}</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $asset->status === 'online' ? 'active' : 'inactive' }}" style="font-size: 0.7rem;">
                        {{ ucfirst($asset->status) }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem;">
                    <a href="{{ route('inventory.warranty.index', ['device_id' => $asset->id]) }}" class="btn-action edit-btn" title="Edit Warranty / AMC Details">
                        <i class="fa-solid fa-file-pen" style="color: var(--primary); font-size: 1.1rem;"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="padding: 2rem 0.5rem; text-align: center; color: var(--text-muted);">No devices mapped in NMS. Add devices in Monitoring.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
