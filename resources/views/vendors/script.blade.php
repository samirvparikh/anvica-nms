@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Vendor Script Template</h1>
        <p>MikroTik <strong>script.rsc</strong> template for <strong>{{ $vendor->name }}</strong> ({{ $vendor->service->name }})</p>
    </div>
    <a href="{{ route('vendors.index', ['service_id' => $vendor->service_id]) }}" class="btn-secondary">Back to Vendors</a>
</div>

<div class="profile-card device-script-card">
    <h3 class="settings-section-title">Service Points &amp; Chunks</h3>
    <p class="settings-section-desc">Use chunk tokens in the template. Each chunk is replaced with SNMP polling code when a vendor OID code is configured.</p>

    <div class="table-scroll" style="margin-bottom:1.5rem;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>OID Code</th>
                    <th>Chunk Tokens</th>
                    <th>Variable</th>
                </tr>
            </thead>
            <tbody>
                @forelse($servicePointRows as $row)
                <tr>
                    <td style="font-weight:600;">{{ $row['name'] }}</td>
                    <td><code>{{ $row['slug'] }}</code></td>
                    <td>{{ $row['code'] ?: '—' }}</td>
                    <td>
                        @foreach($row['chunks'] as $chunk)
                            <code class="chunk-token">{{ $chunk }}</code>
                        @endforeach
                    </td>
                    <td><code>${{ $row['variable'] }}</code></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:1.5rem;color:var(--text-muted);">No service points for this service.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="settings-section-desc">Special blocks: <code>{PING_BLOCK}</code>, <code>{INTERFACES_BLOCK}</code>, <code>{JSON_METRIC_LINES}</code>, <code>{TARGET_IP}</code>, <code>{COMMUNITY}</code>, <code>{NMS_URL}</code>, <code>{IF_INDEXES}</code></p>

    <form action="{{ route('vendors.script.update', $vendor) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group device-script-preview-wrap">
            <label for="vendorTemplate">Script template</label>
            <textarea id="vendorTemplate" name="template" class="form-input device-script-preview" rows="28" required>{{ $template }}</textarea>
            @error('template')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="profile-form-actions">
            <button type="submit" class="btn-save">Save Template</button>
        </div>
    </form>
</div>
@endsection
