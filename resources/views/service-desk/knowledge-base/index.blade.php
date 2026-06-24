@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Knowledge Base</h1>
        <p>Search troubleshooting SOPs, network architecture articles, and resolution playbooks.</p>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 2rem; box-shadow: var(--card-shadow); margin-bottom: 2rem; text-align: center;">
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-dark);">How can we help you resolve device faults today?</h2>
    <div style="max-width: 600px; margin: 0 auto; position: relative;">
        <input type="text" placeholder="Search articles, keywords, error codes..." class="form-control" style="width: 100%; height: 46px; padding-left: 3rem; border-radius: 23px; font-size: 1rem;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem;"></i>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
    <!-- Article 1 -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 3px solid var(--primary);">
        <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--primary);">Network Routing</span>
        <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0.5rem 0; color: var(--text-dark);">Troubleshooting BGP Session Flapping</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1rem;">Step-by-step diagnostic guide for analyzing keepalive timeouts, prefix limit breaches, and route map mismatches.</p>
        <a href="#" style="color: var(--primary); text-decoration: none; font-size: 0.85rem; font-weight: 600;">Read SOP →</a>
    </div>

    <!-- Article 2 -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 3px solid #3b82f6;">
        <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #3b82f6;">Security & VPN</span>
        <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0.5rem 0; color: var(--text-dark);">IPSec Phase 1 and 2 Tunnel Reset</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1rem;">Resolving mismatched pre-shared keys, diffie-hellman group mismatches, and lifetime expiry warnings on Cisco routers.</p>
        <a href="#" style="color: #3b82f6; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Read SOP →</a>
    </div>

    <!-- Article 3 -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 3px solid #8b5cf6;">
        <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #8b5cf6;">Hardware</span>
        <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0.5rem 0; color: var(--text-dark);">Cisco Catalyst Power Supply Failure</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1rem;">Handling redundant PSU failover alerts, validating power draws, and logging hardware replacement tickets with vendor warranty.</p>
        <a href="#" style="color: #8b5cf6; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Read SOP →</a>
    </div>
</div>
@endsection
