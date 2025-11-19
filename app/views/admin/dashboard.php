<div x-data="dashboardComponent()" x-init="init()">

    <form x-ref="pdfForm" action="<?php echo BASE_URL ?>/admin/export_pdf" method="POST" target="_blank" class="hidden">
        <input type="hidden" name="chart_image" x-model="chartImageBase64">
    </form>

    <div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
        <h2 class="text-3xl font-bold text-gray-800">Admin Dashboard</h2>
        <button @click="handleExportPDF()" type="button" class="px-5 py-2.5 bg-red-600 text-white rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            <span>Export Laporan PDF</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 flex items-center gap-6 transform hover:scale-105 transition-transform duration-300">
            <div class="p-4 bg-blue-500 rounded-2xl text-white shadow-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Lokasi Parkir</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_locations ?? 0 ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 flex items-center gap-6 transform hover:scale-105 transition-transform duration-300">
            <div class="p-4 bg-purple-500 rounded-2xl text-white shadow-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-4 0 4 4 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Koordinator</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_coordinators ?? 0 ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 flex items-center gap-6 transform hover:scale-105 transition-transform duration-300">
            <div class="p-4 bg-green-500 rounded-2xl text-white shadow-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Estimasi Pendapatan</p>
                <p class="text-2xl font-bold text-green-600 mt-1">
                    <?php echo 'Rp ' . number_format($grand_total_deposits ?? 0, 0, ',', '.') ?>
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 transform hover:scale-105 transition-transform duration-300">
            <div class="flex items-center gap-6">
                <div class="p-4 bg-green-500 rounded-2xl text-white shadow-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Lokasi Disurvey</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_surveyed_locations ?? 0 ?></p>
                </div>
            </div>
            <?php
                $percentage = ($total_locations > 0) ? ($total_surveyed_locations / $total_locations) * 100 : 0;
            ?>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                <div class="bg-green-500 h-2.5 rounded-full" style="width:                                                                                                                                                                                                                               <?php echo round($percentage) ?>%"></div>
            </div>
            <p class="text-xs text-right text-gray-500 mt-1"><?php echo round($percentage) ?>% Selesai</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl border border-gray-100 p-6 space-y-6">
            <h3 class="font-semibold text-xl text-gray-800 border-b pb-3">Pencarian Cepat Informasi Lokasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="location_search" class="block text-sm font-medium text-gray-700 mb-1">1. Cari Berdasarkan Nama Titik Lokasi</label>
                    <select id="location_search" placeholder="Ketik nama titik lokasi..."></select>
                </div>
                <div>
                    <label for="street_search" class="block text-sm font-medium text-gray-700 mb-1">2. Cari Berdasarkan Nama Jalan / Alamat</label>
                    <select id="street_search" placeholder="Ketik nama jalan..."></select>
                </div>
                <div>
                    <label for="coordinator_search" class="block text-sm font-medium text-gray-700 mb-1">3. Cari Berdasarkan Nama Koordinator</label>
                    <select id="coordinator_search" placeholder="Ketik nama koordinator..."></select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Perbandingan Survey</h3>
            <div class="mx-auto" style="max-height: 250px; display: flex; justify-content: center;">
                <canvas id="surveyComparisonChart"></canvas>
            </div>
            <div class="mt-4 flex justify-center gap-4">
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                    <span class="text-sm text-gray-600">Sudah Disurvey (<?php echo $chart_data['surveyed'] ?? 0 ?>)</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-gray-200 rounded-full mr-2"></span>
                    <span class="text-sm text-gray-600">Belum Disurvey (<?php echo $chart_data['not_surveyed'] ?? 0 ?>)</span>
                </div>
            </div>
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
                <div class="p-4 border rounded-md bg-green-50" x-show="details.deposits">
                    <h4 class="font-bold text-md mb-2 text-green-800">Informasi Setoran</h4>
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2">
                        <dt class="text-sm font-medium text-gray-500 col-span-1">Setoran Harian</dt>
                        <dd class="text-sm text-gray-900 col-span-2" x-text="formatCurrency(details.deposits.daily_deposits)"></dd>
                        <dt class="text-sm font-medium text-gray-500 col-span-1">Setoran Sabtu/Minggu</dt>
                        <dd class="text-sm text-gray-900 col-span-2" x-text="formatCurrency(details.deposits.weekend_deposits)"></dd>
                        <dt class="text-sm font-medium text-gray-500 col-span-1">Setoran Bulanan</dt>
                        <dd class="text-sm text-gray-900 col-span-2" x-text="formatCurrency(details.deposits.monthly_deposits)"></dd>
                    </dl>
                </div>
                <div class="p-4 border rounded-md bg-blue-50" x-show="details.deposits">
                    <h4 class="font-bold text-md mb-2 text-blue-800">Informasi Survey</h4>
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2">
                        <dt class="text-sm font-medium text-gray-500 col-span-1">Surveyor 1</dt>
                        <dd class="text-sm text-gray-900 col-span-2" x-text="details.deposits.surveyor_1 || '-'"></dd>
                        <dt class="text-sm font-medium text-gray-500 col-span-1">Surveyor 2</dt>
                        <dd class="text-sm text-gray-900 col-span-2" x-text="details.deposits.surveyor_2 || '-'"></dd>
                        <dt class="text-sm font-medium text-gray-500 col-span-1">Keterangan</dt>
                        <dd class="text-sm text-gray-900 col-span-2" x-text="details.deposits.information || '-'"></dd>
                        <dt class="text-sm font-medium text-gray-500 col-span-1" x-show="details.deposits.document_survey">Dokumen</dt>
                        <dd class="text-sm text-gray-900 col-span-2" x-show="details.deposits.document_survey">
                            <a :href="'<?php echo BASE_URL ?>/' + details.deposits.document_survey" target="_blank" class="text-blue-600 hover:underline">Lihat PDF</a>
                        </dd>
                    </dl>
                </div>
                <div class="p-4 border-l-4 border-yellow-400 bg-yellow-50 text-center" x-show="!details.deposits">
                    <p class="text-sm text-yellow-700">Data setoran untuk lokasi ini belum diinput.</p>
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

    <div x-show="showCoordinatorResultsModal" x-cloak @keydown.escape.window="showCoordinatorResultsModal = false" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
    <div @click.away="showCoordinatorResultsModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
         <div class="flex justify-between items-center border-b p-4 sticky top-0 bg-white z-10">
            <div>
                <h3 class="text-xl font-semibold">Detail Lokasi Koordinator</h3>
                <p class="text-sm text-gray-600 font-bold" x-text="selectedCoordinatorName"></p>
            </div>
            <button @click="showCoordinatorResultsModal = false" class="text-gray-400 hover:text-gray-900">&times;</button>
        </div>

        <div class="p-4 bg-gray-50 border-b grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Total Titik Lokasi</p>
                <p class="text-2xl font-bold" x-text="coordinatorResults.length"></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Estimasi Setoran Harian</p>
                <p class="text-2xl font-bold" x-html="formatCurrency(totalSetoran.daily)"></p>
            </div>
        </div>

        <div class="p-4 overflow-y-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">No.</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harian (Rp)</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Weekend (Rp)</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bulanan (Rp)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-if="coordinatorResults.length === 0">
                        <tr><td colspan="6" class="text-center text-gray-500 py-6">Tidak ada data lokasi untuk koordinator ini.</td></tr>
                    </template>
                    <template x-for="(location, index) in coordinatorResults" :key="location.id">
                        <tr>
                            <td class="px-2 py-2 text-sm text-gray-500" x-text="index + 1"></td>
                            <td class="px-2 py-2 font-medium" x-text="location.parking_location"></td>
                            <td class="px-2 py-2 text-sm text-gray-600" x-text="location.address"></td>
                            <td class="px-2 py-2 text-sm" x-html="formatCurrency(location.daily_deposits)"></td>
                            <td class="px-2 py-2 text-sm" x-html="formatCurrency(location.weekend_deposits)"></td>
                            <td class="px-2 py-2 text-sm" x-html="formatCurrency(location.monthly_deposits)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function dashboardComponent() {
        return {
            showDetailsModal: false,
            details: {},
            showStreetResultsModal: false,
            streetResults: [],
            selectedStreet: '',
            showCoordinatorResultsModal: false,
            coordinatorResults: [],
            selectedCoordinatorName: '',
            totalSetoran: { daily: 0, weekend: 0, monthly: 0 },
            isAdmin:                                                             <?php echo($_SESSION['user_role'] === 'admin') ? 'true' : 'false' ?>,
            chartImageBase64: '',

            init() {
                const ctx = document.getElementById('surveyComparisonChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Sudah Disurvey', 'Belum Disurvey'],
                        datasets: [{
                            data: [<?php echo $chart_data['surveyed'] ?? 0 ?>,<?php echo $chart_data['not_surveyed'] ?? 0 ?>],
                            backgroundColor: [
                                'rgb(34, 197, 94)',  // HIJAU (Sama seperti kartu)
                                'rgb(229, 231, 235)' // Abu-abu
                            ],
                            hoverOffset: 4,
                            borderColor: 'rgb(255, 255, 255)',
                            borderWidth: 4,
                            cutout: '75%',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        animation: { duration: 0 }
                    }
                });

                // Inisialisasi TomSelect (Kode Anda tidak berubah)
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
                let coordinatorTomSelect = new TomSelect('#coordinator_search', {
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    load: (query, callback) => {
                        if (query.length < 2) return callback();
                        fetch(`<?php echo BASE_URL ?>/parkinglocations/searchCoordinatorsJson?q=${encodeURIComponent(query)}`)
                            .then(response => response.json()).then(json => callback(json)).catch(() => callback());
                    },
                    onChange: (value) => {
                        if (!value) return;
                        this.selectedCoordinatorName = coordinatorTomSelect.options[value].text;
                        this.fetchCoordinatorLocations(value);
                        coordinatorTomSelect.clear();
                        coordinatorTomSelect.blur();
                    }
                });
            },

            // Semua fungsi lain (handleDetailClick, dll) tidak berubah
            handleDetailClick(locationId) {
                fetch(`<?php echo BASE_URL ?>/parkinglocations/getLocationDetailsJson/${locationId}`)
                    .then(res => res.json())
                    .then(data => {
                        this.details = data;
                        this.showDetailsModal = true;
                    });
            },
            fetchCoordinatorLocations(coordinatorId) {
                this.coordinatorResults = [];
                this.showCoordinatorResultsModal = true;
                this.totalSetoran = { daily: 0 };
                fetch(`<?php echo BASE_URL ?>/parkinglocations/getLocationsByCoordinatorJson/${coordinatorId}`)
                    .then(res => res.json())
                    .then(data => {
                        this.coordinatorResults = data;
                        if (this.isAdmin) {
                            let totalDaily = 0;
                            data.forEach(loc => {
                                totalDaily += parseFloat(loc.daily_deposits) || 0;
                            });
                            this.totalSetoran.daily = totalDaily;
                        }
                    });
            },
            formatCurrency(value) {
                const numberValue = parseFloat(value);
                if (isNaN(numberValue)) {
                    return '<span class="text-gray-400 italic">Belum Survey</span>';
                }
                const formattedValue = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(numberValue);
                return `<strong class="text-gray-900">${formattedValue}</strong>`;
            },
            handleExportPDF() {
                const canvas = document.getElementById('surveyComparisonChart');
                this.chartImageBase64 = canvas.toDataURL('image/png');
                this.$nextTick(() => {
                    this.$refs.pdfForm.submit();
                });
            }
        }
    }
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardComponent', dashboardComponent);
    });
</script>