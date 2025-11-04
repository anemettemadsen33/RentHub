<?php

namespace App\Services;

use App\Models\IoTDevice;
use App\Models\IoTDeviceCommand;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class IoTDeviceService
{
    public function sendCommand(IoTDevice $device, User $user, string $commandType, array $params = []): IoTDeviceCommand
    {
        // Check if user has permission
        if (!$this->canControlDevice($device, $user)) {
            throw new \Exception('User does not have permission to control this device');
        }

        // Validate command based on device type
        $this->validateCommand($device, $commandType, $params);

        // Create command
        $command = $device->commands()->create([
            'user_id' => $user->id,
            'command_type' => $commandType,
            'command_params' => $params,
            'status' => 'pending',
        ]);

        // Send to device (this would integrate with actual IoT platform)
        $this->dispatchToDevice($device, $command);

        return $command;
    }

    public function getThermostatState(IoTDevice $device): array
    {
        if ($device->deviceType->slug !== 'thermostat') {
            throw new \Exception('Device is not a thermostat');
        }

        return [
            'current_temperature' => $device->current_state['temperature'] ?? null,
            'target_temperature' => $device->current_state['target_temperature'] ?? null,
            'mode' => $device->current_state['mode'] ?? 'off', // heat, cool, auto, off
            'fan_mode' => $device->current_state['fan_mode'] ?? 'auto',
            'humidity' => $device->current_state['humidity'] ?? null,
        ];
    }

    public function setThermostat(IoTDevice $device, User $user, float $temperature, string $mode = 'auto'): IoTDeviceCommand
    {
        return $this->sendCommand($device, $user, 'set_thermostat', [
            'target_temperature' => $temperature,
            'mode' => $mode,
        ]);
    }

    public function getLightState(IoTDevice $device): array
    {
        if ($device->deviceType->slug !== 'light') {
            throw new \Exception('Device is not a light');
        }

        return [
            'is_on' => $device->current_state['is_on'] ?? false,
            'brightness' => $device->current_state['brightness'] ?? 100,
            'color' => $device->current_state['color'] ?? null,
        ];
    }

    public function controlLight(IoTDevice $device, User $user, bool $turnOn, ?int $brightness = null, ?string $color = null): IoTDeviceCommand
    {
        $params = ['is_on' => $turnOn];
        
        if ($brightness !== null) {
            $params['brightness'] = $brightness;
        }
        
        if ($color !== null) {
            $params['color'] = $color;
        }

        return $this->sendCommand($device, $user, $turnOn ? 'turn_on' : 'turn_off', $params);
    }

    public function getCameraStream(IoTDevice $device, User $user): ?string
    {
        if ($device->deviceType->slug !== 'camera') {
            throw new \Exception('Device is not a camera');
        }

        if (!$this->canViewCamera($device, $user)) {
            throw new \Exception('User does not have permission to view this camera');
        }

        return $device->current_state['stream_url'] ?? null;
    }

    public function getApplianceState(IoTDevice $device): array
    {
        if ($device->deviceType->slug !== 'appliance') {
            throw new \Exception('Device is not an appliance');
        }

        return [
            'is_on' => $device->current_state['is_on'] ?? false,
            'mode' => $device->current_state['mode'] ?? null,
            'power_consumption' => $device->current_state['power_consumption'] ?? 0,
        ];
    }

    protected function canControlDevice(IoTDevice $device, User $user): bool
    {
        // Owner can always control
        if ($device->property->user_id === $user->id) {
            return true;
        }

        // Check if user is a current guest and device is guest accessible
        if ($device->guest_accessible) {
            $activeBooking = $device->property->bookings()
                ->where('user_id', $user->id)
                ->where('status', 'confirmed')
                ->where('check_in', '<=', now())
                ->where('check_out', '>=', now())
                ->exists();

            return $activeBooking;
        }

        return false;
    }

    protected function canViewCamera(IoTDevice $device, User $user): bool
    {
        // Cameras have more restricted access
        return $device->property->user_id === $user->id;
    }

    protected function validateCommand(IoTDevice $device, string $commandType, array $params): void
    {
        $capabilities = $device->deviceType->capabilities ?? [];

        if (!in_array($commandType, $capabilities)) {
            throw new \Exception("Command {$commandType} is not supported by this device");
        }

        // Add specific validation based on command type
        switch ($commandType) {
            case 'set_thermostat':
                if (isset($params['target_temperature'])) {
                    $temp = $params['target_temperature'];
                    if ($temp < 10 || $temp > 35) {
                        throw new \Exception('Temperature must be between 10°C and 35°C');
                    }
                }
                break;
            case 'turn_on':
            case 'turn_off':
                if (isset($params['brightness'])) {
                    $brightness = $params['brightness'];
                    if ($brightness < 0 || $brightness > 100) {
                        throw new \Exception('Brightness must be between 0 and 100');
                    }
                }
                break;
        }
    }

    protected function dispatchToDevice(IoTDevice $device, IoTDeviceCommand $command): void
    {
        // This would integrate with actual IoT platform (AWS IoT, Google IoT, etc.)
        // For now, we'll simulate the command being sent
        
        try {
            $command->markAsSent();

            // Simulate device response (in real implementation, this would be async)
            // You would integrate with:
            // - AWS IoT Core
            // - Google Cloud IoT
            // - Azure IoT Hub
            // - MQTT broker
            // - Device manufacturer's API

            Log::info("IoT Command sent", [
                'device_id' => $device->device_id,
                'command' => $command->command_type,
                'params' => $command->command_params,
            ]);

            // Simulate success response
            $command->markAsExecuted('Command executed successfully');

            // Update device state
            if ($command->command_type === 'set_thermostat') {
                $device->updateState([
                    'target_temperature' => $command->command_params['target_temperature'],
                    'mode' => $command->command_params['mode'] ?? 'auto',
                ]);
            } elseif (in_array($command->command_type, ['turn_on', 'turn_off'])) {
                $device->updateState([
                    'is_on' => $command->command_type === 'turn_on',
                    'brightness' => $command->command_params['brightness'] ?? 100,
                    'color' => $command->command_params['color'] ?? null,
                ]);
            }

        } catch (\Exception $e) {
            $command->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    public function getDeviceHistory(IoTDevice $device, int $days = 7): array
    {
        $logs = $device->logs()
            ->where('event_timestamp', '>=', now()->subDays($days))
            ->orderBy('event_timestamp', 'desc')
            ->get();

        return $logs->map(function ($log) {
            return [
                'timestamp' => $log->event_timestamp,
                'event_type' => $log->event_type,
                'data' => $log->event_data,
                'description' => $log->description,
            ];
        })->toArray();
    }
}
