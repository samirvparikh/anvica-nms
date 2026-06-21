@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        @include('reports.partials.back-dashboard')
        <h1>Performance &amp; Traffic Report</h1>
        <p>Bandwidth usage, latency, packet loss and resource utilization.</p>
    </div>
</div>

<div id="nmsKpiGrid" class="nms-kpi-grid nms-kpi-grid--5"></div>

<div class="nms-report-grid nms-report-grid--2">
    <div class="nms-chart-card"><h3>Bandwidth Utilization Trend</h3><div class="nms-chart-wrap"><canvas id="bandwidthChart"></canvas></div></div>
    <div class="nms-chart-card"><h3>Latency Trend</h3><div class="nms-chart-wrap"><canvas id="latencyChart"></canvas></div></div>
    <div class="nms-chart-card"><h3>Packet Loss Trend</h3><div class="nms-chart-wrap"><canvas id="packetLossChart"></canvas></div></div>
    <div class="nms-chart-card"><h3>CPU Utilization Trend</h3><div class="nms-chart-wrap"><canvas id="cpuChart"></canvas></div></div>
</div>

<div class="nms-chart-card nms-chart-card--wide">
    <h3>Memory Utilization Trend</h3>
    <div class="nms-chart-wrap"><canvas id="memoryChart"></canvas></div>
</div>

<div class="card-table-container">
    <div class="table-toolbar"><h3>Top Interfaces by Bandwidth</h3></div>
    <div class="table-scroll">
        <table class="data-table">
            <thead id="interfacesHead"></thead>
            <tbody id="interfacesBody"></tbody>
        </table>
    </div>
</div>

<script src="{{ asset('js/reports/data/performance-traffic.js') }}"></script>
<script src="{{ asset('js/reports/report-utils.js') }}"></script>
<script src="{{ asset('js/reports/pages/performance-traffic.js') }}"></script>
@endsection
