@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Asset Groups</h1>
        <p>Categorize assets and devices into logical functional categories.</p>
    </div>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">Group Name</th>
                <th style="padding: 1rem 0.5rem;">Description</th>
                <th style="padding: 1rem 0.5rem;">Device Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--text-dark);">{{ $group->name }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $group->description }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 700;"><span class="status-badge active" style="font-size: 0.75rem;">{{ $group->count }} Assets</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
