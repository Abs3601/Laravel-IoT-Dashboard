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
        $totalEvents     = IoTEvent::count();
        $totalDevices    = Device::count();
        $eventsToday     = IoTEvent::whereDate('created_at', today())->count();
        $eventsLastHour  = IoTEvent::where('created_at', '>=', now()->subHour())->count();

        // Average events per minute over the last 5 minutes
        $eventsLast5Min  = IoTEvent::where('created_at', '>=', now()->subMinutes(5))->count();
        $ingestionRate   = round($eventsLast5Min / 5, 1);

        // Events grouped by minute — last 60 minutes (line chart)
        $eventsPerMinute = DB::select("
            SELECT DATE_FORMAT(created_at, '%H:%i') AS label, COUNT(*) AS count
            FROM iot_events
            WHERE created_at >= NOW() - INTERVAL 60 MINUTE
            GROUP BY DATE_FORMAT(created_at, '%H:%i')
            ORDER BY label ASC
        ");

        // Events grouped by hour — last 24 hours (bar chart)
        $eventsPerHour = DB::select("
            SELECT DATE_FORMAT(created_at, '%H:00') AS label, COUNT(*) AS count
            FROM iot_events
            WHERE created_at >= NOW() - INTERVAL 24 HOUR
            GROUP BY DATE_FORMAT(created_at, '%H:00')
            ORDER BY label ASC
        ");

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

        return response()->json([
            'cards' => [
                'total_events'      => $totalEvents,
                'total_devices'     => $totalDevices,
                'events_today'      => $eventsToday,
                'events_last_hour'  => $eventsLastHour,
                'ingestion_rate'    => $ingestionRate,
            ],
            'events_per_minute' => $eventsPerMinute,
            'events_per_hour'   => $eventsPerHour,
            'by_entity_type'    => $byEntityType,
            'top_devices'       => $topDevices,
        ]);
    }
}
