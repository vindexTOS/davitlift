<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnregisteredDevice;

class UnregisteredDeviceController extends Controller
{
    public function get()
    {
        $device = UnregisteredDevice::all();
        return response()->json($device);
    }

    /**
     * Delete the device by dev_id.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // Assuming 'dev_id' is passed as a request parameter.
        $devId = $request->input('dev_id');

        $device = UnregisteredDevice::where('dev_id', $devId)->first();

        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $device->delete();

        return response()->json(['message' => 'Device deleted successfully']);
    }
}
