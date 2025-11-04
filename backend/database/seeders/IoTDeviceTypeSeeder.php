<?php

namespace Database\Seeders;

use App\Models\IoTDeviceType;
use Illuminate\Database\Seeder;

class IoTDeviceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $deviceTypes = [
            [
                'name' => 'Thermostat',
                'slug' => 'thermostat',
                'capabilities' => [
                    'set_thermostat',
                    'get_temperature',
                    'set_mode',
                    'set_fan_mode',
                ],
                'default_config' => [
                    'min_temperature' => 10,
                    'max_temperature' => 35,
                    'modes' => ['heat', 'cool', 'auto', 'off'],
                    'fan_modes' => ['auto', 'on', 'circulate'],
                ],
            ],
            [
                'name' => 'Smart Light',
                'slug' => 'light',
                'capabilities' => [
                    'turn_on',
                    'turn_off',
                    'set_brightness',
                    'set_color',
                ],
                'default_config' => [
                    'min_brightness' => 0,
                    'max_brightness' => 100,
                    'supports_color' => true,
                ],
            ],
            [
                'name' => 'Security Camera',
                'slug' => 'camera',
                'capabilities' => [
                    'get_stream',
                    'take_snapshot',
                    'start_recording',
                    'stop_recording',
                ],
                'default_config' => [
                    'resolution' => '1080p',
                    'night_vision' => true,
                    'motion_detection' => true,
                ],
            ],
            [
                'name' => 'Smart Appliance',
                'slug' => 'appliance',
                'capabilities' => [
                    'turn_on',
                    'turn_off',
                    'set_mode',
                    'get_status',
                ],
                'default_config' => [
                    'power_monitoring' => true,
                ],
            ],
            [
                'name' => 'Smart Lock',
                'slug' => 'lock',
                'capabilities' => [
                    'lock',
                    'unlock',
                    'get_status',
                    'create_access_code',
                    'revoke_access_code',
                ],
                'default_config' => [
                    'auto_lock' => true,
                    'auto_lock_delay' => 30,
                ],
            ],
            [
                'name' => 'Smart Plug',
                'slug' => 'plug',
                'capabilities' => [
                    'turn_on',
                    'turn_off',
                    'get_power_consumption',
                ],
                'default_config' => [
                    'power_monitoring' => true,
                    'schedule_enabled' => true,
                ],
            ],
        ];

        foreach ($deviceTypes as $type) {
            IoTDeviceType::create($type);
        }
    }
}
