@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        @include('reports.partials.back-dashboard')
        <h1>Fault Management Report</h1>
        <p>
            @if($isAdmin)
                @if($selectedCustomer)
                    Faults derived from metric and interface logs for <strong>{{ $selectedCustomer->name }}</strong>.
                @else
                    Faults derived from metric and interface logs across all customers.
                @endif
            @else
                Faults derived from metric and interface logs for your assigned devices, {{ Auth::user()->name }}.
            @endif
        </p>
        <form method="GET" action="{{ route('reports.fault-management') }}" class="report-fault-date-filter">
            @if($isAdmin && $customerId)
                <input type="hidden" name="user_id" value="{{ $customerId }}">
            @endif
            <label for="reportFrom" class="report-user-filter-label">From</label>
            <input type="date" id="reportFrom" name="from" class="form-control report-date-input" value="{{ $from->format('Y-m-d') }}">
            <label for="reportTo" class="report-user-filter-label">To</label>
            <input type="date" id="reportTo" name="to" class="form-control report-date-input" value="{{ $to->format('Y-m-d') }}">
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        </form>
    </div>
    @if($isAdmin)
    <form method="GET" action="{{ route('reports.fault-management') }}" class="report-user-filter">
        <label for="reportUserFilter" class="report-user-filter-label">Select User</label>
        <select id="reportUserFilter" name="user_id" class="form-control report-user-filter-select" onchange="this.form.submit()">
            <option value="">All Users</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ (int) $customerId === (int) $customer->id ? 'selected' : '' }}>
                    {{ $customer->name }} ({{ $customer->email }})
                </option>
            @endforeach
        </select>
        <input type="hidden" name="from" value="{{ $from->format('Y-m-d') }}">
        <input type="hidden" name="to" value="{{ $to->format('Y-m-d') }}">
    </form>
    @endif
</div>

<div id="nmsFaultLoading" class="nms-report-loading">Loading fault report…</div>
<div id="nmsFaultError" class="nms-report-error" hidden></div>

<div id="nmsKpiGrid" class="nms-kpi-grid nms-kpi-grid--4" hidden></div>

<div class="nms-report-grid nms-report-grid--2" id="nmsFaultCharts" hidden>
    <div class="nms-chart-card">
        <h3>Alarm Summary by Severity</h3>
        <div class="nms-chart-wrap"><canvas id="severityChart"></canvas></div>
    </div>
    <div class="nms-chart-card">
        <h3>Alarms Over Time</h3>
        <div class="nms-chart-wrap nms-chart-wrap--tall"><canvas id="alarmsTrendChart"></canvas></div>
    </div>
</div>

<div class="nms-report-grid nms-report-grid--2" id="nmsFaultTables" hidden>
    <div class="card-table-container">
        <div class="table-toolbar"><h3>Active Alarms</h3></div>
        <div class="table-scroll" style="max-height: 520px; overflow: auto;">
            <table class="data-table">
                <thead id="activeAlarmsHead"></thead>
                <tbody id="activeAlarmsBody"></tbody>
            </table>
        </div>
    </div>
    <div class="card-table-container">
        <div class="table-toolbar"><h3>Downtime Summary</h3></div>
        <div class="table-scroll" style="max-height: 520px; overflow: auto;">
            <table class="data-table">
                <thead id="downtimeHead"></thead>
                <tbody id="downtimeBody"></tbody>
            </table>
        </div>
    </div>
</div>



<!-- <div id="nmsFeatureBanner" class="nms-feature-banner" hidden></div> -->

<script>
window.NmsFaultReportConfig = {
    dataUrl: @json(route('reports.fault-management.data')),
    userId: @json($customerId),
    from: @json($from->format('Y-m-d')),
    to: @json($to->format('Y-m-d')),
};
</script>
<script src="{{ asset('js/reports/report-utils.js') }}"></script>
<script src="{{ asset('js/reports/pages/fault-management.js') }}"></script>
@endsection
