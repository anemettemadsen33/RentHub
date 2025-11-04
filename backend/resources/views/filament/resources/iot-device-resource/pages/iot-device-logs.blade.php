<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Device Information</h3>
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Device Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $record->device_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $record->deviceType->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Location</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $record->location_in_property }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($record->status === 'online') bg-green-100 text-green-800
                            @elseif($record->status === 'offline') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800
                            @endif">
                            {{ ucfirst($record->status) }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Device Logs</h3>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
