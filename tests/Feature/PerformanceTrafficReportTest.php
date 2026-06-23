<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceInterfaceLog;
use App\Models\DeviceMetricLog;
use App\Models\User;
use App\Services\PerformanceTrafficReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTrafficReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_performance_traffic_report_returns_simulated_fallback_trends_on_empty_database(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $from = Carbon::now()->subDays(7)->startOfDay();
        $to = Carbon::now()->endOfDay();

        $response = $this->actingAs($user)
            ->getJson(route('reports.performance-traffic.data', [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ]));

        $response->assertStatus(200);

        $data = $response->json();

        // Range
        $this->assertEquals($from->toIso8601String(), $data['range']['from']);

        // Assert trends are populated and NOT zero
        $this->assertNotEmpty($data['trendLabels']);
        $this->assertNotEmpty($data['bandwidthTrend']);
        $this->assertNotEmpty($data['latencyTrend']);
        $this->assertNotEmpty($data['packetLossTrend']);
        $this->assertNotEmpty($data['cpuTrend']);
        $this->assertNotEmpty($data['memoryTrend']);

        // Assert at least some values are greater than zero due to simulated curves
        $this->assertGreaterThan(0.0, array_sum($data['cpuTrend']));
        $this->assertGreaterThan(0.0, array_sum($data['memoryTrend']));
        $this->assertGreaterThan(0.0, array_sum($data['latencyTrend']));

        // Assert KPIs exist and have formatted values
        $kpis = collect($data['kpis']);
        $this->assertTrue($kpis->contains('label', 'Bandwidth Utilization'));
        $this->assertTrue($kpis->contains('label', 'Average Latency'));
        $this->assertTrue($kpis->contains('label', 'Packet Loss'));
        $this->assertTrue($kpis->contains('label', 'CPU Utilization'));
        $this->assertTrue($kpis->contains('label', 'Memory Utilization'));

        // Assert Top Interfaces are populated with fallback/simulated entries
        $this->assertNotEmpty($data['topInterfaces']);
        $this->assertEquals('Core-Switch01', $data['topInterfaces'][0]['device']);
    }

    public function test_performance_traffic_report_computes_dynamic_averages_from_database_logs(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $device = Device::factory()->create(['user_id' => $user->id, 'name' => 'Edge-Router']);

        $from = Carbon::now()->subDays(2)->startOfDay();
        $to = Carbon::now()->endOfDay();

        $dayOfLog = Carbon::now()->subDays(1);

        // 1. CPU metric log (value: 45.0)
        DeviceMetricLog::create([
            'device_id' => $device->id,
            'metric_slug' => 'CPU',
            'metric_value' => 45.0000,
            'recorded_at' => $dayOfLog,
        ]);

        // 2. Latency metric log (metric_text: '00:00:00.015000' -> 15.0 ms)
        DeviceMetricLog::create([
            'device_id' => $device->id,
            'metric_slug' => 'Ping_Latency',
            'metric_value' => 0.0000,
            'metric_text' => '00:00:00.015000',
            'recorded_at' => $dayOfLog,
        ]);

        // 3. Memory byte-ratio metric logs (Ram_Used: 25000, Total_Ram: 100000 -> 25%)
        DeviceMetricLog::create([
            'device_id' => $device->id,
            'metric_slug' => 'Ram_Used',
            'metric_value' => 25000.0000,
            'recorded_at' => $dayOfLog,
        ]);

        DeviceMetricLog::create([
            'device_id' => $device->id,
            'metric_slug' => 'Total_Ram',
            'metric_value' => 100000.0000,
            'recorded_at' => $dayOfLog,
        ]);

        // 4. Interface throughput logs (two consecutive records to compute Mbps)
        DeviceInterfaceLog::create([
            'device_id' => $device->id,
            'interface_name' => 'ether1',
            'status' => 'Up',
            'rx' => 100000,
            'tx' => 200000,
            'recorded_at' => $dayOfLog,
        ]);

        DeviceInterfaceLog::create([
            'device_id' => $device->id,
            'interface_name' => 'ether1',
            'status' => 'Up',
            'rx' => 150000,
            'tx' => 250000,
            'recorded_at' => $dayOfLog->copy()->addSeconds(10), // delta = 10s, rx_diff=50k, tx_diff=50k, combined bytes diff=100k
        ]);

        // Build report via service directly to assert raw outputs
        $service = app(PerformanceTrafficReportService::class);
        $report = $service->build($user, null, $from, $to);

        // Find the index of $dayOfLog in labels
        $dayLabel = $dayOfLog->format('M d');
        $labelIndex = array_search($dayLabel, $report['trendLabels']);
        $this->assertNotFalse($labelIndex);

        // Assert memory trend calculation
        $this->assertEquals(25.00, $report['memoryTrend'][$labelIndex]);

        // Assert CPU trend calculation
        $this->assertEquals(45.00, $report['cpuTrend'][$labelIndex]);

        // Assert Latency trend calculation
        $this->assertEquals(15.00, $report['latencyTrend'][$labelIndex]);

        // Assert Top Interfaces table content
        $this->assertNotEmpty($report['topInterfaces']);
        $topIf = collect($report['topInterfaces'])->firstWhere('interface', 'ether1');
        $this->assertNotNull($topIf);
        $this->assertEquals('Edge-Router', $topIf['device']);
    }
}
