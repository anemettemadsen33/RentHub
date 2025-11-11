<x-filament-widgets::widget>
    <x-filament::section>
        <div class="relative" x-data="mapWidget(@js($this->getProperties()))">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold">Properties Map View</h3>
                <div class="flex gap-2">
                    <button 
                        @click="zoomIn()" 
                        class="px-3 py-1 bg-primary-600 text-white rounded hover:bg-primary-700"
                    >
                        Zoom In
                    </button>
                    <button 
                        @click="zoomOut()" 
                        class="px-3 py-1 bg-primary-600 text-white rounded hover:bg-primary-700"
                    >
                        Zoom Out
                    </button>
                    <button 
                        @click="resetView()" 
                        class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700"
                    >
                        Reset View
                    </button>
                </div>
            </div>
            
            <div id="properties-map" class="w-full h-[600px] rounded-lg shadow-sm border border-gray-200"></div>
            
            <div class="mt-4 text-sm text-gray-600">
                Total Properties on Map: <span x-text="properties.length" class="font-bold"></span>
            </div>
        </div>
    </x-filament::section>

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <script>
        function mapWidget(properties) {
            return {
                properties: properties,
                map: null,
                markers: null,

                init() {
                    // Initialize map
                    this.map = L.map('properties-map').setView([45.9432, 24.9668], 7); // Romania center

                    // Add OpenStreetMap tiles
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(this.map);

                    // Initialize marker cluster group
                    this.markers = L.markerClusterGroup({
                        chunkedLoading: true,
                        spiderfyOnMaxZoom: true,
                        showCoverageOnHover: true,
                        zoomToBoundsOnClick: true
                    });

                    // Add markers
                    this.properties.forEach(property => {
                        const marker = L.marker([property.latitude, property.longitude]);
                        
                        const popupContent = `
                            <div class="p-2 min-w-[250px]">
                                <h4 class="font-bold text-lg mb-2">${property.title}</h4>
                                ${property.image ? `<img src="${property.image}" alt="${property.title}" class="w-full h-32 object-cover rounded mb-2" />` : ''}
                                <p class="text-sm text-gray-600 mb-1">${property.city}</p>
                                <p class="text-sm mb-1"><strong>Type:</strong> ${property.type}</p>
                                <p class="text-sm mb-1"><strong>Bedrooms:</strong> ${property.bedrooms}</p>
                                <p class="text-lg font-bold text-primary-600 mb-2">€${property.price}/night</p>
                                <p class="text-xs text-gray-500">Owner: ${property.owner}</p>
                                <a href="/admin/properties/${property.id}" class="inline-block mt-2 px-3 py-1 bg-primary-600 text-white text-sm rounded hover:bg-primary-700">
                                    View Property
                                </a>
                            </div>
                        `;
                        
                        marker.bindPopup(popupContent);
                        this.markers.addLayer(marker);
                    });

                    this.map.addLayer(this.markers);

                    // Fit bounds to show all markers
                    if (this.properties.length > 0) {
                        const bounds = this.markers.getBounds();
                        this.map.fitBounds(bounds, { padding: [50, 50] });
                    }
                },

                zoomIn() {
                    this.map.zoomIn();
                },

                zoomOut() {
                    this.map.zoomOut();
                },

                resetView() {
                    if (this.properties.length > 0) {
                        const bounds = this.markers.getBounds();
                        this.map.fitBounds(bounds, { padding: [50, 50] });
                    } else {
                        this.map.setView([45.9432, 24.9668], 7);
                    }
                }
            }
        }
    </script>
    @endpush
</x-filament-widgets::widget>
