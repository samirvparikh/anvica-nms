@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        @include('reports.partials.back-dashboard')
        <h1>Performance &amp; Traffic Report</h1>
        <p>
            @if($isAdmin)
                @if($selectedCustomer)
                    Performance and traffic from metric and interface logs for <strong>{{ $selectedCustomer->name }}</strong>.
                @else
                    Performance and traffic from metric and interface logs across all customers.
                @endif
            @else
                Performance and traffic from metric and interface logs for your assigned devices, {{ Auth::user()->name }}.
            @endif
        </p>
        <form method="GET" action="{{ route('reports.performance-traffic') }}" class="report-fault-date-filter">
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
    <form method="GET" action="{{ route('reports.performance-traffic') }}" class="report-user-filter">
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

<div id="nmsPerfLoading" class="nms-report-loading">Loading performance report…</div>
<div id="nmsPerfError" class="nms-report-error" hidden></div>

<div id="nmsKpiGrid" class="nms-kpi-grid nms-kpi-grid--5" hidden></div>

<div class="nms-report-grid nms-report-grid--2" id="nmsPerfCharts" hidden>
    <div class="nms-chart-card"><h3>Bandwidth Utilization Trend</h3><div class="nms-chart-wrap"><canvas id="bandwidthChart"></canvas></div></div>
    <div class="nms-chart-card"><h3>Latency Trend</h3><div class="nms-chart-wrap"><canvas id="latencyChart"></canvas></div></div>
    <div class="nms-chart-card"><h3>Packet Loss Trend</h3><div class="nms-chart-wrap"><canvas id="packetLossChart"></canvas></div></div>
    <div class="nms-chart-card"><h3>CPU Utilization Trend</h3><div class="nms-chart-wrap"><canvas id="cpuChart"></canvas></div></div>
</div>

<div class="nms-chart-card nms-chart-card--wide" id="nmsPerfMemoryChart" hidden>
    <h3>Memory Utilization Trend</h3>
    <div class="nms-chart-wrap"><canvas id="memoryChart"></canvas></div>
</div>

<div class="card-table-container" id="nmsPerfTable" hidden>
    <div class="table-toolbar"><h3>Top Interfaces by Bandwidth</h3></div>
    <div class="table-scroll">
        <table class="data-table">
            <thead id="interfacesHead"></thead>
            <tbody id="interfacesBody"></tbody>
        </table>
    </div>
</div>

<script>
window.NmsPerfReportConfig = {
    dataUrl: @json(route('reports.performance-traffic.data')),
    userId: @json($customerId),
    from: @json($from->format('Y-m-d')),
    to: @json($to->format('Y-m-d')),
};
</script>
<script src="{{ asset('js/reports/report-utils.js') }}"></script>
<script src="{{ asset('js/reports/pages/performance-traffic.js') }}"></script>
@endsection
