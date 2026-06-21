@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        @include('reports.partials.back-dashboard')
        <h1>Fault Management Report</h1>
        <p>Detailed alerts, active alarms and downtime analysis.</p>
    </div>
</div>

<div id="nmsKpiGrid" class="nms-kpi-grid nms-kpi-grid--4"></div>

<div class="nms-report-grid nms-report-grid--2">
    <div class="card-table-container">
        <div class="table-toolbar"><h3>Active Alarms</h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead id="activeAlarmsHead"></thead>
                <tbody id="activeAlarmsBody"></tbody>
            </table>
        </div>
    </div>
    <div class="card-table-container">
        <div class="table-toolbar"><h3>Downtime Summary</h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead id="downtimeHead"></thead>
                <tbody id="downtimeBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="nms-report-grid nms-report-grid--2">
    <div class="nms-chart-card">
        <h3>Alarm Summary by Severity</h3>
        <div class="nms-chart-wrap"><canvas id="severityChart"></canvas></div>
    </div>
    <div class="nms-chart-card">
        <h3>Alarms Over Time</h3>
        <div class="nms-chart-wrap nms-chart-wrap--tall"><canvas id="alarmsTrendChart"></canvas></div>
    </div>
</div>

<div id="nmsFeatureBanner" class="nms-feature-banner"></div>

<script src="{{ asset('js/reports/data/fault-management.js') }}"></script>
<script src="{{ asset('js/reports/report-utils.js') }}"></script>
<script src="{{ asset('js/reports/pages/fault-management.js') }}"></script>
@endsection
