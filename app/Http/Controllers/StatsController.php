<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\IoTEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        return view('stats');
    }

    public function data(): JsonResponse
    {
        $totalEvents = IoTEvent::count();
        $totalDevices = Device::count();
        $eventsToday = IoTEvent::whereDate('created_at', today())->count();
        $eventsLastHour = IoTEvent::where('created_at', '>=', now()->subHour())->count();

        // Average events per minute over the last 5 minutes
        $eventsLast5Min = IoTEvent::where('created_at', '>=', now()->subMinutes(5))->count();
        $ingestionRate  = round($eventsLast5Min / 5, 1);

        // Peak events in any single minute over the last hour
        $peakRow = DB::selectOne("
            SELECT MAX(cnt) AS peak
            FROM (
                SELECT COUNT(*) AS cnt
                FROM iot_events
                WHERE created_at >= NOW() - INTERVAL 60 MINUTE
                GROUP BY DATE_FORMAT(created_at, '%H:%i')
            ) sub
        ");
        $peakRate = (int) ($peakRow->peak ?? 0);

        // Events grouped by minute — last 60 minutes (line chart)
        $rawPerMinute = DB::select("
            SELECT DATE_FORMAT(created_at, '%H:%i') AS label, COUNT(*) AS count
            FROM iot_events
            WHERE created_at >= NOW() - INTERVAL 60 MINUTE
            GROUP BY DATE_FORMAT(created_at, '%H:%i')
        ");
        $minuteCounts = collect($rawPerMinute)->pluck('count', 'label');
        $eventsPerMinute = collect(range(59, 0))->map(function ($i) use ($minuteCounts) {
            $utcLabel = now()->subMinutes($i)->format('H:i');
            $displayLabel = now()->subMinutes($i)->format('H:i');
            return (object) ['label' => $displayLabel, 'count' => (int) ($minuteCounts[$utcLabel] ?? 0)];
        })->values();

        // Events grouped by hour — last 12 hours (bar chart)
        $rawPerHour = DB::select("
            SELECT DATE_FORMAT(created_at, '%H:00') AS label, COUNT(*) AS count
            FROM iot_events
            WHERE created_at >= NOW() - INTERVAL 12 HOUR
            GROUP BY DATE_FORMAT(created_at, '%H:00')
        ");

        $hourCounts = collect($rawPerHour)->pluck('count', 'label');
        $eventsPerHour = collect(range(11, 0))->map(function ($i) use ($hourCounts) {
            $utcLabel = now()->subHours($i)->format('H:00');
            $displayLabel = now()->subHours($i)->format('H:00');
            return (object) ['label' => $displayLabel, 'count' => (int) ($hourCounts[$utcLabel] ?? 0)];
        })->values();

        // Event share by entity_type / platform (pie chart)
        $byEntityType = DB::select("
            SELECT entity_type AS label, COUNT(*) AS count
            FROM iot_events
            GROUP BY entity_type
            ORDER BY count DESC
            LIMIT 8
        ");

        // Top 10 most active devices
        $topDevices = DB::select("
            SELECT COALESCE(d.friendly_name, e.entity_id) AS label, COUNT(*) AS count
            FROM iot_events e
            LEFT JOIN devices d ON d.entity_id = e.entity_id
            GROUP BY e.entity_id, d.friendly_name
            ORDER BY count DESC
            LIMIT 10
        ");

        // Latency stats
        $latencyRow = DB::selectOne("
            SELECT
                ROUND(AVG(latency_ms), 0)  AS avg_ms,
                ROUND(MAX(latency_ms), 0)  AS max_ms,
                ROUND(MIN(latency_ms), 0)  AS min_ms,
                COUNT(*)                   AS sample_count
            FROM iot_events
            WHERE latency_ms IS NOT NULL
            AND created_at >= NOW() - INTERVAL 10 MINUTE
        ");

        $latency = [
            'avg_ms'       => $latencyRow->avg_ms ?? null,
            'max_ms'       => $latencyRow->max_ms ?? null,
            'min_ms'       => $latencyRow->min_ms ?? null,
            'sample_count' => (int) ($latencyRow->sample_count ?? 0),
        ];

        return response()->json([
            'cards' => [
                'total_events'     => $totalEvents,
                'total_devices'    => $totalDevices,
                'events_today'     => $eventsToday,
                'events_last_hour' => $eventsLastHour,
                'ingestion_rate'   => $ingestionRate,
                'peak_rate'        => $peakRate,
            ],
            'events_per_minute' => $eventsPerMinute,
            'events_per_hour'   => $eventsPerHour,
            'by_entity_type'    => $byEntityType,
            'top_devices'       => $topDevices,
            'latency'           => $latency,
        ]);
    }
}

