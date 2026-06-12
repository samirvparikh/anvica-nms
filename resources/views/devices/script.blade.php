@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Device MikroTik Script</h1>
        <p>Generate <strong>script.rsc</strong> for <strong>{{ $device->name }}</strong> ({{ $device->ip_address }})</p>
    </div>
    <a href="{{ route('devices.index') }}" class="btn-secondary">Back to Devices</a>
</div>

@if($device->vendor)
<div class="device-script-vendor-link">
    <span>Vendor template:</span>
    <a href="{{ route('vendors.script.edit', $device->vendor) }}">{{ $device->vendor->name }}</a>
</div>
@endif

<form action="{{ route('devices.script.update', $device) }}" method="POST" id="deviceScriptForm">
    @csrf
    @method('PUT')

    <section class="device-script-section">
        <div class="row">
            <div class="col-6">
                <h3 class="device-script-part-title">Device Settings</h3>
                <div class="device-script-form-fields">
                    <div class="form-group">
                        <label for="target_ip">Target IP</label>
                        <input type="text" id="target_ip" name="target_ip" class="form-input" value="{{ $config['target_ip'] }}" required>
                        <span class="form-hint">SNMP target device IP (targetIP).</span>
                    </div>
                    <div class="form-group">
                        <label for="snmp_community">SNMP Community</label>
                        <input type="text" id="snmp_community" name="snmp_community" class="form-input" value="{{ $config['snmp_community'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="nms_url">NMS API URL</label>
                        <input type="url" id="nms_url" name="nms_url" class="form-input" value="{{ $config['nms_url'] }}" required>
                        <span class="form-hint">POST endpoint for metrics and interfaces data.</span>
                    </div>
                    <div class="form-group">
                        <label for="interface_indexes">Interface Indexes</label>
                        <input type="text" id="interface_indexes" name="interface_indexes" class="form-input" value="{{ $config['interface_indexes'] }}" placeholder="3,5,6,8" required>
                        <span class="form-hint">Comma-separated SNMP interface indexes.</span>
                        @error('interface_indexes')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-6">
                <h3 class="device-script-part-title">Service Points</h3>
                <p class="settings-section-desc">Chunk tokens like <code>{CPU}</code> or <code>{CPU_Temp}</code> use vendor OID codes.</p>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>OID Code</th>
                                <th>Chunks</th>
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="text-align:center;padding:1.5rem;color:var(--text-muted);">No service points configured.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section class="device-script-section">
        <div class="device-script-section-toolbar">
            <button type="button" class="btn-secondary" id="refreshPreviewBtn">Refresh Preview</button>
        </div>
        <div class="row">
            <div class="col-6">
                <h3 class="device-script-part-title">
                    Generated script.rsc
                    @if(!empty($templateIsDefault))
                        <span class="form-hint" style="font-weight:400;">(default template)</span>
                    @endif
                </h3>
                <textarea id="scriptTemplate" class="form-input device-script-preview" rows="22" readonly>{{ $template }}</textarea>
                <span class="form-hint">
                    @if(!empty($templateIsDefault))
                        Default vendor template — not saved yet.
                        @if($device->vendor)
                            <a href="{{ route('vendors.script.edit', $device->vendor) }}">Save on vendor script page</a>.
                        @endif
                    @else
                        Vendor template with chunk placeholders.
                        @if($device->vendor)
                            Edit on the <a href="{{ route('vendors.script.edit', $device->vendor) }}">vendor script page</a>.
                        @endif
                    @endif
                </span>
            </div>

            <div class="col-6">
                <h3 class="device-script-part-title">Preview</h3>
                <textarea id="scriptPreview" class="form-input device-script-preview" rows="22" readonly>{{ $preview }}</textarea>
                <span class="form-hint">Final script after replacing chunks and device settings.@if($hasPublishedFile) Published file exists.@endif</span>
            </div>
        </div>
    </section>

    @if($publicUrl)
    <div class="device-script-public-url">
        <label>Public script URL</label>
        <div class="device-script-url-row">
            <input type="text" class="form-input is-readonly" value="{{ $publicUrl }}" readonly id="publicScriptUrl">
            <button type="button" class="btn-secondary" id="copyScriptUrlBtn">Copy</button>
            <a href="{{ $publicUrl }}" class="btn-secondary" target="_blank" rel="noopener">Open</a>
        </div>
        <span class="form-hint">Fetch on MikroTik: <code>/tool fetch url="{{ $publicUrl }}" dst-path=script.rsc</code></span>
    </div>
    @endif

    <div class="profile-form-actions">
        <button type="submit" class="btn-save">Save &amp; Publish Script</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('deviceScriptForm');
    const preview = document.getElementById('scriptPreview');
    const refreshBtn = document.getElementById('refreshPreviewBtn');
    const copyBtn = document.getElementById('copyScriptUrlBtn');
    const publicUrlInput = document.getElementById('publicScriptUrl');
    const previewUrl = '{{ route('devices.script.preview', $device) }}';
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let previewTimer = null;

    function formData() {
        return new FormData(form);
    }

    function loadPreview() {
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.textContent = 'Loading...';
        }

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: formData(),
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Preview failed');
                }
                return response.json();
            })
            .then(function (data) {
                preview.value = data.content || '';
            })
            .catch(function () {
                if (refreshBtn) {
                    alert('Unable to refresh preview. Check form values and vendor OID codes.');
                }
            })
            .finally(function () {
                if (refreshBtn) {
                    refreshBtn.disabled = false;
                    refreshBtn.textContent = 'Refresh Preview';
                }
            });
    }

    function schedulePreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(loadPreview, 400);
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', loadPreview);
    }

    ['target_ip', 'snmp_community', 'nms_url', 'interface_indexes'].forEach(function (fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', schedulePreview);
            field.addEventListener('change', schedulePreview);
        }
    });

    if (copyBtn && publicUrlInput) {
        copyBtn.addEventListener('click', function () {
            publicUrlInput.select();
            document.execCommand('copy');
            copyBtn.textContent = 'Copied';
            setTimeout(function () {
                copyBtn.textContent = 'Copy';
            }, 1500);
        });
    }
});
</script>
@endsection
