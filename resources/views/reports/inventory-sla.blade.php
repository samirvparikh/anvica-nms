@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        @include('reports.partials.back-dashboard')
        <h1>Inventory &amp; SLA Reports</h1>
        <p>Device inventory, software tracking &amp; SLA compliance.</p>
    </div>
</div>

<div id="nmsKpiGrid" class="nms-kpi-grid nms-kpi-grid--6"></div>

<div class="card-table-container">
    <div class="table-toolbar"><h3>Device Inventory</h3></div>
    <div class="table-scroll">
        <table class="data-table">
            <thead id="inventoryHead"></thead>
            <tbody id="inventoryBody"></tbody>
        </table>
    </div>
</div>

<div class="nms-report-grid nms-report-grid--2">
    <div class="card-table-container">
        <div class="table-toolbar"><h3>Software / Firmware Compliance</h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead id="firmwareHead"></thead>
                <tbody id="firmwareBody"></tbody>
            </table>
        </div>
    </div>
    <div class="nms-chart-card">
        <h3>Warranty Status</h3>
        <div class="nms-chart-wrap"><canvas id="warrantyChart"></canvas></div>
    </div>
</div>

<div class="nms-report-grid nms-report-grid--2">
    <div class="nms-chart-card">
        <h3>SLA Compliance Overview</h3>
        <div class="nms-chart-wrap"><canvas id="slaOverviewChart"></canvas></div>
    </div>
    <div class="nms-chart-card">
        <h3>SLA Compliance Trend</h3>
        <div class="nms-chart-wrap"><canvas id="slaTrendChart"></canvas></div>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar"><h3>SLA Compliance by Customer</h3></div>
    <div class="table-scroll">
        <table class="data-table">
            <thead id="slaCustomerHead"></thead>
            <tbody id="slaCustomerBody"></tbody>
        </table>
    </div>
</div>

<div id="nmsFeatureBanner" class="nms-feature-banner"></div>

<script src="{{ asset('js/reports/data/inventory-sla.js') }}"></script>
<script src="{{ asset('js/reports/report-utils.js') }}"></script>
<script src="{{ asset('js/reports/pages/inventory-sla.js') }}"></script>
@endsection
