<?php

namespace App\Http\Controllers;

use App\Models\UpdatingDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpdatingDeviceController extends Controller
{
    public function getLastCreated()
    {
        $lastCreated = UpdatingDevice::latest('created_at')->first();

        if (!$lastCreated) {
            return response()->json([
                'devices' => [],
                'status_counts' => [],
            ]);
        }

        $devices = UpdatingDevice::with('device')->where('created_at', $lastCreated->created_at)
            ->where('is_checked', false)
            ->get();

        // Getting counts by different status values.
        $statusCounts = UpdatingDevice::where('created_at', $lastCreated->created_at)
            ->where('is_checked', false)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });

        return response()->json([
            'devices' => $devices,
            'status_counts' => $statusCounts,
        ]);
    }
    function checkSuccess(): \Illuminate\Http\JsonResponse
    {
        $lastCreated = UpdatingDevice::latest('created_at')->first();

        if (!$lastCreated) {
            return response()->json([]);
        }

        $devices = UpdatingDevice::where('created_at', $lastCreated->created_at)->where('status','1')->update(['is_checked' => true]);

        return response()->json($devices);
    }
    function checkFailed() {
        $lastCreated = UpdatingDevice::latest('created_at')->first();

        if (!$lastCreated) {
            return response()->json([]);
        }

        $devices = UpdatingDevice::where('created_at', $lastCreated->created_at)->where('status','2')->update(['is_checked' => true]);

        return response()->json($devices);
    }
}
