@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Asset Inventory</h1>
        <p>Manage system hardware assets, configurations, and organizational locations.</p>
    </div>
    <a href="{{ route('inventory.assets.create') }}" class="btn-add">
        <i class="fa-solid fa-plus" style="margin-right: 0.5rem;"></i> Create Asset
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
                <th style="padding: 1rem 0.5rem;">Category</th>
                <th style="padding: 1rem 0.5rem;">Status</th>
                <th style="padding: 1rem 0.5rem;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">{{ $asset->asset_id_auto }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $asset->asset_name }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $asset->management_ip }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $asset->manufacturer }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $asset->model_number }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $asset->serial_number }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $asset->asset_category }}</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ strtolower($asset->status) === 'active' ? 'active' : 'inactive' }}" style="font-size: 0.7rem;">
                        {{ ucfirst($asset->status) }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem;">
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <a href="{{ route('inventory.assets.edit', $asset->id) }}" class="btn-action edit-btn" title="Edit Asset" style="text-decoration: none;">
                            <i class="fa-solid fa-file-pen" style="color: var(--primary); font-size: 1.1rem;"></i>
                        </a>
                        <form action="{{ route('inventory.assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this asset?');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer; display: flex; align-items: center;" title="Delete Asset">
                                <i class="fa-solid fa-trash-can" style="color: var(--status-down); font-size: 1.1rem;"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="padding: 2rem 0.5rem; text-align: center; color: var(--text-muted);">No assets found. Click 'Create Asset' to add one.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
