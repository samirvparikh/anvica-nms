@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <a href="{{ route('reports.index') }}" class="report-back-link">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Back to Reports Dashboard
        </a>
        <h1>Device Management Report</h1>
        <p>
            @if($isAdmin)
                @if($selectedCustomer)
                    Reports for <strong>{{ $selectedCustomer->name }}</strong> — devices and interfaces.
                @else
                    Device and interface reports for all customers.
                @endif
            @else
                Reports for your assigned devices and interfaces, {{ Auth::user()->name }}.
            @endif
        </p>
    </div>
    @if($isAdmin)
    <form method="GET" action="{{ route('reports.device-management') }}" class="report-user-filter">
        <label for="reportUserFilter" class="report-user-filter-label">Select User</label>
        <select id="reportUserFilter" name="user_id" class="form-control report-user-filter-select" onchange="this.form.submit()">
            <option value="">All Users</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ (int) $customerId === (int) $customer->id ? 'selected' : '' }}>
                    {{ $customer->name }} ({{ $customer->email }})
                </option>
            @endforeach
        </select>
    </form>
    @endif
</div>

@include('reports.partials.devices-section', ['showInterfaceList' => true])
@endsection
