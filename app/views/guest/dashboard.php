<div x-data="dashboardComponent()" x-init="init()">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-sm font-medium text-gray-500">Total Lokasi Parkir</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_locations ?? 0 ?></p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-sm font-medium text-gray-500">Total Koordinator</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_coordinators ?? 0 ?></p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6 md:col-span-2">
                    <p class="text-sm font-medium text-gray-500">Lokasi Sudah Disurvey</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_surveyed_locations ?? 0 ?></p>
                    <?php
                        $percentage = ($total_locations > 0) ? ($total_surveyed_locations / $total_locations) * 100 : 0;
                    ?>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-green-500 h-2.5 rounded-full" style="width:                                                                                                                                                                                                                                                       <?php echo round($percentage) ?>%"></div>
                    </div>
                    <p class="text-xs text-right text-gray-500 mt-1"><?php echo round($percentage) ?>% Selesai</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="font-semibold text-lg text-gray-800 mb-2">Perbandingan Survey</h3>
            <div class="mx-auto" style="max-height: 220px; display: flex; justify-content: center;">
                <canvas id="surveyComparisonChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
        <h3 class="font-semibold text-xl text-gray-800 border-b pb-3">Pencarian Cepat Informasi Lokasi</h3>
        <div>
            <label for="location_search" class="block text-sm font-medium text-gray-700 mb-1">1. Cari Berdasarkan Nama Titik Lokasi</label>
            <select id="location_search" placeholder="Ketik nama titik lokasi..."></select>
        </div>
        <div>
            <label for="street_search" class="block text-sm font-medium text-gray-700 mb-1">2. Cari Berdasarkan Nama Jalan / Alamat</label>
            <select id="street_search" placeholder="Ketik nama jalan..."></select>
        </div>
    </div>

    <div x-show="showDetailsModal" x-cloak @keydown.escape.window="showDetailsModal = false" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div @click.away="showDetailsModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b p-4 sticky top-0 bg-white z-10">
                <h3 class="text-xl font-semibold text-gray-900" x-text="details.location ? details.location.parking_location : 'Memuat...'"></h3>
                <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-900">&times;</button>
            </div>
            <div class="p-4 space-y-4" x-if="details.location">
                <div class="p-4 border rounded-md">
                    <h4 class="font-bold text-md mb-2 text-gray-700">Informasi Umum</h4>
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2">
                        <dt class="text-sm font-medium text-gray-500 col-span-1">Alamat</dt>
                        <dd class="text-sm text-gray-900 col-span-2" x-text="details.location.address"></dd>
                        <dt class="text-sm font-medium text-gray-500 col-span-1">Koordinator</dt>
                        <dd class="text-sm text-gray-900 col-span-2" x-text="details.location.coordinator_name"></dd>
                    </dl>
                </div>
                <div class="p-4 border-l-4 border-yellow-400 bg-yellow-50 text-center" x-show="!details.deposits">
                    <p class="text-sm text-yellow-700">Informasi detail mengenai setoran dan data survey hanya dapat diakses oleh Admin.</p>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showStreetResultsModal" x-cloak @keydown.escape.window="showStreetResultsModal = false" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div @click.away="showStreetResultsModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
             <div class="flex justify-between items-center border-b p-4 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-xl font-semibold">Hasil Pencarian Jalan</h3>
                    <p class="text-sm text-gray-600" x-text="selectedStreet"></p>
                </div>
                <button @click="showStreetResultsModal = false" class="text-gray-400 hover:text-gray-900">&times;</button>
            </div>
            <div class="p-4">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Koordinator</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="location in streetResults" :key="location.id">
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900" x-text="location.parking_location"></td>
                                <td class="px-4 py-2 text-sm text-gray-600" x-text="location.coordinator_name"></td>
                                <td class="px-4 py-2">
                                    <button @click="handleDetailClick(location.id); showStreetResultsModal = false" class="text-sm text-blue-600 hover:underline">Lihat Detail</button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="streetResults.length === 0">
                            <tr>
                                <td colspan="3" class="text-center py-10 text-gray-500">Tidak ada lokasi yang ditemukan di alamat ini.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function dashboardComponent() {
        return {
            // State untuk modal
            showDetailsModal: false,
            details: {},
            showStreetResultsModal: false,
            streetResults: [],
            selectedStreet: '',

            // Fungsi yang dijalankan saat komponen dimuat
            init() {
                // Inisialisasi Chart
                const ctx = document.getElementById('surveyComparisonChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Sudah Disurvey', 'Belum Disurvey'],
                        datasets: [{
                            data: [<?php echo $chart_data['surveyed'] ?? 0 ?>,<?php echo $chart_data['not_surveyed'] ?? 0 ?>],
                            backgroundColor: ['rgb(34, 197, 94)', 'rgb(229, 231, 235)'],
                            hoverOffset: 4,
                            borderColor: 'rgb(255, 255, 255)',
                            borderWidth: 2,
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'top' } } }
                });

                // Inisialisasi TomSelect untuk pencarian NAMA LOKASI
                let locationTomSelect = new TomSelect('#location_search', {
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    load: (query, callback) => {
                        if (query.length < 3) return callback();
                        fetch(`<?php echo BASE_URL ?>/parkinglocations/searchJson?q=${encodeURIComponent(query)}`)
                            .then(response => response.json()).then(json => callback(json)).catch(() => callback());
                    },
                    onChange: (value) => {
                        if (!value) return;
                        this.handleDetailClick(value);
                        locationTomSelect.clear();
                        locationTomSelect.blur();
                    }
                });

                // Inisialisasi TomSelect untuk pencarian NAMA JALAN
                let streetTomSelect = new TomSelect('#street_search', {
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    load: (query, callback) => {
                        if (query.length < 3) return callback();
                        fetch(`<?php echo BASE_URL ?>/parkinglocations/searchAddressJson?q=${encodeURIComponent(query)}`)
                            .then(response => response.json()).then(json => callback(json)).catch(() => callback());
                    },
                    onChange: (value) => {
                        if (!value) return;
                        this.selectedStreet = value;
                        fetch(`<?php echo BASE_URL ?>/parkinglocations/getLocationsByAddressJson?address=${encodeURIComponent(value)}`)
                            .then(res => res.json())
                            .then(data => {
                                this.streetResults = data;
                                this.showStreetResultsModal = true;
                                streetTomSelect.clear();
                                streetTomSelect.blur();
                            });
                    }
                });
            },

            // Fungsi untuk mengambil dan menampilkan detail satu lokasi
            handleDetailClick(locationId) {
                fetch(`<?php echo BASE_URL ?>/parkinglocations/getLocationDetailsJson/${locationId}`)
                    .then(res => res.json())
                    .then(data => {
                        this.details = data;
                        this.showDetailsModal = true;
                    });
            },
        }
    }
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardComponent', dashboardComponent);
    });
</script>