@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        @include('reports.partials.back-dashboard')
        <h1>SLA Ticketing &amp; User Performance</h1>
        <p>Track SLA compliance, tickets and user performance.</p>
    </div>
</div>

<div id="nmsKpiGrid" class="nms-kpi-grid nms-kpi-grid--7"></div>

<div class="nms-report-grid nms-report-grid--3">
    <div class="nms-chart-card">
        <h3>SLA Compliance Over Time</h3>
        <div class="nms-chart-wrap"><canvas id="slaComplianceTrendChart"></canvas></div>
    </div>
    <div class="nms-chart-card">
        <h3>SLA Compliance by Customer</h3>
        <div class="nms-chart-wrap"><canvas id="slaCustomerChart"></canvas></div>
    </div>
    <div class="nms-chart-card">
        <h3>SLA Breaches by Priority</h3>
        <div class="nms-chart-wrap"><canvas id="breachPriorityChart"></canvas></div>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar"><h3>SLA Tickets Overview</h3></div>
    <div class="table-scroll">
        <table class="data-table">
            <thead id="ticketsHead"></thead>
            <tbody id="ticketsBody"></tbody>
        </table>
    </div>
</div>

<div class="nms-report-grid nms-report-grid--2">
    <div class="card-table-container">
        <div class="table-toolbar"><h3>User Performance</h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead id="userPerfHead"></thead>
                <tbody id="userPerfBody"></tbody>
            </table>
        </div>
    </div>
    <div class="nms-report-grid nms-report-grid--1">
        <div class="nms-chart-card">
            <h3>Ticket Trend</h3>
            <div class="nms-chart-wrap"><canvas id="ticketTrendChart"></canvas></div>
        </div>
        <div class="nms-chart-card">
            <h3>Ticket Status Summary</h3>
            <div class="nms-chart-wrap"><canvas id="ticketStatusChart"></canvas></div>
        </div>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar"><h3>Top Issues</h3></div>
    <div class="table-scroll">
        <table class="data-table">
            <thead id="topIssuesHead"></thead>
            <tbody id="topIssuesBody"></tbody>
        </table>
    </div>
</div>

<div id="nmsSummaryCards" class="nms-summary-grid"></div>

<script src="{{ asset('js/reports/data/sla-ticketing.js') }}"></script>
<script src="{{ asset('js/reports/report-utils.js') }}"></script>
<script src="{{ asset('js/reports/pages/sla-ticketing.js') }}"></script>
@endsection
