<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\UpdatingDevice;
use App\Services\MqttConnectionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use PhpMqtt\Client\MqttClient;
use function Illuminate\Events\queueable;
use Illuminate\Support\Facades\Http;
class FileController extends Controller
{
    public function upload(Request $request)
    {
        $file = $request->file('file');
        if(!empty($file)) {
            $filename = $file->getClientOriginalName();
            $pattern = '/ver([\d.]+)/';

            if (preg_match($pattern, $filename, $matches)) {
                $versionNumber = str_replace('.', '', $matches[1]);
                if(!empty(File::where('filename',$filename)->first())) {
                    return response()->json(['message'=>'file with this name already exists'],422);
                }
                $file->storeAs('public/uploads', $filename);


                $file_path = storage_path('app/public/uploads/'.$filename); // Adjust the path accordingly
                $file = fopen($file_path, 'r');

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                ])->attach(
                    'file', $file, $filename
                )->post('http://167.235.25.45/api/uploadForHttp');
                fclose($file);  // Don't forget to close the resource

                $fileEntry = new File();
                $fileEntry->filename = $filename;
                $fileEntry->version = $versionNumber;
                $fileEntry->save();
                // Create a new FormData instance

                return response()->json(['filename' => $filename]);
            } else {
                return response()->json(['message' => 'incorrect data',422]);
            }

        }
    }

    public function uploadForHttp(Request $request)
    {
        $file = $request->file('file');
        if ($request->hasFile('file')) {
            $filename = $file->getClientOriginalName();
            $pattern = '/ver([\d.]+)/';

            if (preg_match($pattern, $filename, $matches)) {
                $versionNumber = str_replace('.', '', $matches[1]);

                if (Storage::disk('public')->exists('uploads/' . $filename)) {
                    return response()->json(['message' => 'File with this name already exists'], 422);
                }

                // Store the file in the 'public/uploads' directory
                $file->storeAs('uploads', $filename, 'public');

                // You can optionally return the path or any other related information
                $filePath = Storage::disk('public')->url('uploads/' . $filename);

                return response()->json([
                    'message' => 'File uploaded successfully',
                    'filename' => $filename,
                    'path' => $filePath,
                    'versionNumber' => $versionNumber
                ], 200);
            } else {
                return response()->json(['message' => 'Incorrect data'], 422);
            }
        }

        return response()->json(['message' => 'No file was uploaded'], 400);
    }
    public function index()
    {
        $files = File::all();
        return response()->json($files);
    }
    public function delete($id)
    {
        // Retrieve the file from the database
        $file = File::find($id);

        if ($file) {
            $response = Http::delete('http://167.235.25.45/api/filesForFileServer/'.$file->filename);

            // Construct the file path
            $file_path = storage_path('app/public/uploads/' . $file->filename);

            // Check if the file exists in the storage
            if (file_exists($file_path)) {
                // Delete the file from the storage
                unlink($file_path);
            }

            // Delete the record from the database
            $file->delete();

            return response()->json(['message' => 'File and record deleted successfully'], 200);
        }

        return response()->json(['error' => 'File not found'], 404);
    }
    public function deleteForFileServer($filename)
    {
        // Retrieve the file from the database

        // Construct the file path
        $file_path = storage_path('app/public/uploads/' . $filename);

        // Check if the file exists in the storage
        if (file_exists($file_path)) {
            // Delete the file from the storage
            Storage::delete($file_path);
        }

        // Delete the record from the database

        return response()->json(['message' => 'File and record deleted successfully'], 200);
    }

    public function download($file)
    {
        $file_path = storage_path('app/public/uploads/'.$file); // Adjust the path accordingly

        if (file_exists($file_path)) {
            return response()->download($file_path, $file);
        }

        return response()->json(['error' => 'File not found'], 404);
    }
    public function test() {
        $filePath = 'files/Lift_gateway_factory_ver1.0.0.bin'; // Adjust the path accordingly
        $fileContent = Storage::get($filePath);
        $crc = crc32($fileContent);
        $string = pack('V','~'.$crc);
    }
    public function deviceUpdateByArray(Request $request) {
     $file = File::where('version', $request->version)->first();
        $name = $file->filename;
        $version = $file->version;
        $payloadMain =  pack('VC', time(), 250);

        $payload = 'http://167.235.25.45/api/download/'.$name;
        $payload .= pack('C', 0);
        $payload .= $version;
        $payload .= pack('C', 0);
        $filePath = 'public/uploads/'.$name; // Adjust the path accordingly
        $fileContent = Storage::get($filePath);
        $fileSize = strlen($fileContent);
        $payload .= pack('V',$fileSize);

        $crc = $this->crc32_custom(0xFFFFFFFF,$fileContent);

        $payload .=  pack('V',$crc);
        $payloadMain  .= pack('C', strlen($payload)) . $payload;
        foreach ($request->dev_ids as $key => $dev_id) {
            $this->publishMessage( $dev_id, $this->generateHexPayload(250,[
                [
                    'type' => 'string',
                    'value' => 'http://167.235.25.45/api/download/'.$name
                ],
                [
                    'type' => 'number',
                    'value' => 0
                ],
                [
                    'type' => 'string',
                    'value' => $version,
                ],
                [
                    'type' => 'number',
                    'value' => 0
                ],
                [
                    'type' => 'timestamp',
                    'value' => $fileSize
                ],
                [
                    'type' => 'timestamp',
                    'value' => $crc
                ]
            ])
            );
        }
        UpdatingDevice::whereIn('dev_id',$request->dev_ids)->update( ['status' => 4]);
        return response()->json(['files send successfully']);
    }
    public function deviceUpdate($name,$version) {
        $devices = Device::where('soft_Version','!=',$version)->orWhere('soft_Version',null)->get();
        $insertions = [];
        foreach ($devices as $key => $value) {
            if(Carbon::parse($value->lastBeat)->timestamp > time()) {
                $insertions[] = [
                    'dev_id' => $value->dev_id,
                    'device_id' => $value->id,
                    'previous_version' => $value->soft_version,
                    'new_version' => $version,
                    'status' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'is_checked' => false,
                ];
            }
        }

        UpdatingDevice::insert($insertions);

        return response()->json(['files send successfully']);
    }
    private function crc32_custom($crc, $data) {
        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {
            $byte = ord($data[$i]);
            $crc ^= $byte;
            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc >> 1) ^ (($crc & 1) ? 0xEDB88320 : 0);
            }
        }
        return $crc;
    }
    public function generateHexPayload($command, $payload)
    {
        return [
            'command' => $command,
            'payload' => $payload
        ];
    }
    public function publishMessage($device_id,$payload)
    {
        $data = [
            'device_id' => $device_id,
            'payload' => $payload
        ];
        $queryParams = http_build_query($data);
        $response = Http::get('http://localhost:3000/mqtt/general?' . $queryParams);
        return $response->json(['data' => ['dasd']]);

    }
}
