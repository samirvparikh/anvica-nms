@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Software & Firmware Licenses</h1>
        <p>Monitor active firmware OS versions, vendor licenses, and support agreements.</p>
    </div>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">Software Name</th>
                <th style="padding: 1rem 0.5rem;">Installed Version</th>
                <th style="padding: 1rem 0.5rem;">Vendor</th>
                <th style="padding: 1rem 0.5rem;">Assets Mapped</th>
                <th style="padding: 1rem 0.5rem;">License Contract</th>
            </tr>
        </thead>
        <tbody>
            @foreach($softwares as $sw)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--text-dark);">{{ $sw->name }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $sw->version }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $sw->vendor }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $sw->count }} Devices</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $sw->license }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
