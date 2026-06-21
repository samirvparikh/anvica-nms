@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Reports Dashboard</h1>
        <p>Centralized Monitoring &amp; Analytics Reports</p>
    </div>
</div>

<div class="reports-category-grid">
    <a href="{{ route('reports.device-management') }}" class="report-category-card">
        <div class="report-category-icon"><i class="fa-solid fa-server"></i></div>
        <h3>Device Monitoring Report</h3>
        <p>Monitor device health, interfaces, metrics history and performance logs for all registered network devices.</p>
        <span class="report-category-link">Open Report <i class="fa-solid fa-arrow-right"></i></span>
    </a>

    <a href="{{ route('reports.fault-management') }}" class="report-category-card">
        <div class="report-category-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3>Fault Management Report</h3>
        <p>Detailed alerts, active alarms, downtime events and severity analysis across your infrastructure.</p>
        <span class="report-category-link">Open Report <i class="fa-solid fa-arrow-right"></i></span>
    </a>

    <a href="{{ route('reports.performance-traffic') }}" class="report-category-card">
        <div class="report-category-icon"><i class="fa-solid fa-chart-line"></i></div>
        <h3>Performance &amp; Traffic Report</h3>
        <p>Bandwidth utilization, latency, packet loss, CPU and memory trends with top interface analysis.</p>
        <span class="report-category-link">Open Report <i class="fa-solid fa-arrow-right"></i></span>
    </a>

    <a href="{{ route('reports.inventory-sla') }}" class="report-category-card">
        <div class="report-category-icon"><i class="fa-solid fa-shield-check"></i></div>
        <h3>Inventory &amp; SLA Reports</h3>
        <p>Device inventory, firmware compliance, warranty tracking and SLA compliance by customer.</p>
        <span class="report-category-link">Open Report <i class="fa-solid fa-arrow-right"></i></span>
    </a>

    <a href="{{ route('reports.sla-ticketing') }}" class="report-category-card">
        <div class="report-category-icon"><i class="fa-solid fa-ticket"></i></div>
        <h3>SLA Ticketing &amp; User Performance</h3>
        <p>Track SLA compliance, ticket lifecycle, user performance scores and resolution metrics.</p>
        <span class="report-category-link">Open Report <i class="fa-solid fa-arrow-right"></i></span>
    </a>
</div>
@endsection
