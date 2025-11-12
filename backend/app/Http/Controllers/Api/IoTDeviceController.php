<?php

namespace App\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\IoTDevice;
use App\Models\Property;
use App\Services\IoTDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IoTDeviceController extends Controller
{
    protected $iotService;

    public function __construct(IoTDeviceService $iotService)
    {
        $this->iotService = $iotService;
    }

    public function propertyDevices(Property $property)
    {
        $this->authorize('view', $property);

        $devices = $property->iotDevices()
            ->with('deviceType')
            ->where('is_active', true)
            ->get()
            ->map(function ($device) {
                return [
                    'id' => $device->id,
                    'name' => $device->device_name,
                    'type' => $device->deviceType->name,
                    'type_slug' => $device->deviceType->slug,
                    'location' => $device->location_in_property,
                    'status' => $device->status,
                    'current_state' => $device->current_state,
                    'guest_accessible' => $device->guest_accessible,
                    'last_communication' => $device->last_communication,
                ];
            });

        return response()->json([
            'devices' => $devices,
        ]);
    }

    public function show(IoTDevice $device)
    {
        try {
            if (! $this->iotService->canControlDevice($device, Auth::user())) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $state = match ($device->deviceType->slug) {
                'thermostat' => $this->iotService->getThermostatState($device),
                'light' => $this->iotService->getLightState($device),
                'appliance' => $this->iotService->getApplianceState($device),
                default => $device->current_state,
            };

            return response()->json([
                'device' => [
                    'id' => $device->id,
                    'name' => $device->device_name,
                    'type' => $device->deviceType->name,
                    'location' => $device->location_in_property,
                    'status' => $device->status,
                    'state' => $state,
                    'manufacturer' => $device->manufacturer,
                    'model' => $device->model,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function controlThermostat(Request $request, IoTDevice $device)
    {
        $request->validate([
            'target_temperature' => 'required|numeric|min:10|max:35',
            'mode' => 'nullable|in:heat,cool,auto,off',
        ]);

        try {
            $command = $this->iotService->setThermostat(
                $device,
                Auth::user(),
                $request->target_temperature,
                $request->mode ?? 'auto'
            );

            return response()->json([
                'message' => 'Thermostat command sent successfully',
                'command_id' => $command->id,
                'status' => $command->status,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function controlLight(Request $request, IoTDevice $device)
    {
        $request->validate([
            'turn_on' => 'required|boolean',
            'brightness' => 'nullable|integer|min:0|max:100',
            'color' => 'nullable|string',
        ]);

        try {
            $command = $this->iotService->controlLight(
                $device,
                Auth::user(),
                $request->turn_on,
                $request->brightness,
                $request->color
            );

            return response()->json([
                'message' => 'Light command sent successfully',
                'command_id' => $command->id,
                'status' => $command->status,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getCameraStream(IoTDevice $device)
    {
        try {
            $streamUrl = $this->iotService->getCameraStream($device, Auth::user());

            if (! $streamUrl) {
                return response()->json(['error' => 'Camera stream not available'], 404);
            }

            return response()->json([
                'stream_url' => $streamUrl,
                'device_name' => $device->device_name,
                'location' => $device->location_in_property,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function sendCommand(Request $request, IoTDevice $device)
    {
        $request->validate([
            'command_type' => 'required|string',
            'params' => 'nullable|array',
        ]);

        try {
            $command = $this->iotService->sendCommand(
                $device,
                Auth::user(),
                $request->command_type,
                $request->params ?? []
            );

            return response()->json([
                'message' => 'Command sent successfully',
                'command_id' => $command->id,
                'status' => $command->status,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function deviceHistory(IoTDevice $device)
    {
        try {
            if (! $this->iotService->canControlDevice($device, Auth::user())) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $history = $this->iotService->getDeviceHistory($device, 7);

            return response()->json([
                'history' => $history,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function commandHistory(IoTDevice $device)
    {
        try {
            if (! $this->iotService->canControlDevice($device, Auth::user())) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $commands = $device->commands()
                ->with('user:id,name')
                ->latest()
                ->limit(50)
                ->get()
                ->map(function ($command) {
                    return [
                        'id' => $command->id,
                        'command_type' => $command->command_type,
                        'params' => $command->command_params,
                        'status' => $command->status,
                        'user' => $command->user->name,
                        'created_at' => $command->created_at,
                        'executed_at' => $command->executed_at,
                    ];
                });

            return response()->json([
                'commands' => $commands,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

