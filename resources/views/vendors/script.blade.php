@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Vendor Script Template</h1>
        <p>MikroTik <strong>script.rsc</strong> template for <strong>{{ $vendor->name }}</strong> ({{ $vendor->service->name }})</p>
    </div>
    <a href="{{ route('vendors.index', ['service_id' => $vendor->service_id]) }}" class="btn-secondary">Back to Vendors</a>
</div>

<form action="{{ route('vendors.script.update', $vendor) }}" method="POST">
    @csrf
    @method('PUT')

    <section class="device-script-section">
        <div class="row">
            <div class="col-6">
                <h3 class="device-script-part-title">Service Points &amp; Chunks</h3>
                <p class="settings-section-desc">Use chunk tokens in the template. Each chunk is replaced with SNMP polling code when a vendor OID code is configured.</p>

                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>OID Code</th>
                                <th>Chunks</th>
                                <th>Var</th>
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

                <p class="settings-section-desc" style="margin-top:0.75rem;">
                    Special blocks: <code>{PING_BLOCK}</code>, <code>{INTERFACES_BLOCK}</code>, <code>{JSON_METRIC_LINES}</code>,
                    <code>{TARGET_IP}</code>, <code>{COMMUNITY}</code>, <code>{NMS_URL}</code>, <code>{IF_INDEXES}</code>
                </p>
            </div>

            <div class="col-6">
                <h3 class="device-script-part-title">
                    Script template
                    @if(!empty($templateIsDefault))
                        <span class="form-hint" style="font-weight:400;">(default)</span>
                    @endif
                </h3>
                <textarea id="vendorTemplate" name="template" class="form-input device-script-preview" rows="28" required>{{ $template }}</textarea>
                @error('template')
                    <span class="form-error">{{ $message }}</span>
                @enderror
                @if(!empty($templateIsDefault))
                    <span class="form-hint">Default template — save to store for this vendor.</span>
                @endif
            </div>
        </div>
    </section>

    <div class="profile-form-actions">
        <button type="submit" class="btn-save">Save Template</button>
    </div>
</form>
@endsection
