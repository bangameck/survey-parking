<div class="space-y-8" x-data="pimpinanDashboard()">

    <div class="hidden">
        <input type="hidden" id="incomeChartInput">
        <input type="hidden" id="surveyChartInput">
    </div>

    <div class="bg-gradient-to-r from-indigo-800 to-blue-900 rounded-2xl shadow-2xl p-8 text-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Executive Dashboard</h2>
                <p class="mt-2 text-indigo-100 text-lg">Ringkasan Operasional & Keuangan UPT Perparkiran.</p>
            </div>

            <div class="flex flex-col gap-3 items-end">
                <div class="bg-white/10 p-3 rounded-xl backdrop-blur-sm border border-white/20 text-right">
                    <p class="text-xs font-medium uppercase tracking-wider text-indigo-200">Tanggal Laporan</p>
                    <p class="text-2xl font-bold"><?php echo date('d F Y') ?></p>
                </div>

                <button @click="handleExport()" type="button" class="px-6 py-2 bg-white text-indigo-900 font-bold rounded-lg shadow-lg hover:bg-indigo-50 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Report (PDF)
                </button>
            </div>
        </div>
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-indigo-500 opacity-20 rounded-full blur-2xl"></div>
    </div>

    <div>
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Data Operasional & Potensi
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-md border-l-4 border-indigo-500 p-6">
                <div class="flex justify-between items-start">
                    <div><p class="text-xs font-bold text-gray-500 uppercase">Total Titik Lokasi</p><h4 class="text-3xl font-bold text-gray-800 mt-1"><?php echo number_format($total_locations ?? 0) ?></h4></div>
                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md border-l-4 border-purple-500 p-6">
                <div class="flex justify-between items-start">
                    <div><p class="text-xs font-bold text-gray-500 uppercase">Koordinator Aktif</p><h4 class="text-3xl font-bold text-gray-800 mt-1"><?php echo number_format($total_coordinators ?? 0) ?></h4></div>
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md border-l-4 border-yellow-500 p-6">
                 <div class="flex justify-between items-start">
                    <div><p class="text-xs font-bold text-gray-500 uppercase">Data Ter-Survey</p><h4 class="text-3xl font-bold text-gray-800 mt-1"><?php echo number_format($surveyed_locations ?? 0) ?></h4></div>
                    <div class="p-2 bg-yellow-50 rounded-lg text-yellow-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg></div>
                </div>
                <?php $percent = ($total_locations > 0) ? ($surveyed_locations / $total_locations) * 100 : 0; ?>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3"><div class="bg-yellow-500 h-1.5 rounded-full" style="width:<?php echo $percent ?>%"></div></div>
                <p class="text-xs text-gray-400 mt-1 text-right"><?php echo round($percent) ?>% Selesai</p>
            </div>
            <div class="bg-white rounded-xl shadow-md border-l-4 border-blue-400 p-6 bg-gradient-to-br from-white to-blue-50">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-blue-600 uppercase">TOTAL POTENSI (ESTIMASI)</p>
                        <h4 class="text-2xl font-bold text-gray-800 mt-1">Rp                                                                             <?php echo number_format($total_potential ?? 0, 0, ',', '.') ?></h4>
                        <p class="text-[10px] text-gray-500 mt-1">Berdasarkan data survey</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"></path></svg>
            Realisasi Keuangan Tim (Takeover)
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-green-500">
                <p class="text-xs font-bold text-gray-500 uppercase">Pemasukan Hari Ini</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">Rp                                                                    <?php echo number_format($finance->today ?? 0, 0, ',', '.') ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-green-600">
                <p class="text-xs font-bold text-gray-500 uppercase">Bulan Ini (<?php echo date('M') ?>)</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">Rp                                                                    <?php echo number_format($finance->this_month ?? 0, 0, ',', '.') ?></p>
            </div>
             <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-teal-600">
                <p class="text-xs font-bold text-gray-500 uppercase">Tahun Ini (<?php echo date('Y') ?>)</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">Rp                                                                    <?php echo number_format($finance->this_year ?? 0, 0, ',', '.') ?></p>
            </div>
            <div class="bg-green-600 rounded-xl shadow-lg p-6 text-white">
                <p class="text-xs font-bold text-green-100 uppercase">TOTAL REALISASI MASUK</p>
                <p class="text-2xl font-bold mt-2">Rp                                                      <?php echo number_format($finance->total_all_time ?? 0, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 space-y-6 border border-indigo-100">
        <h3 class="font-semibold text-xl text-gray-800 border-b pb-3 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            Pencarian Data Spesifik
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama Titik Lokasi</label>
                <select id="location_search" placeholder="Ketik nama titik lokasi..."></select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama Jalan / Alamat</label>
                <select id="street_search" placeholder="Ketik nama jalan..."></select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama Koordinator</label>
                <select id="coordinator_search" placeholder="Ketik nama koordinator..."></select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-800">Tren Pendapatan (7 Hari Terakhir)</h3>
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Live Data</span>
            </div>
            <div class="h-72 w-full">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <h3 class="font-bold text-gray-800 mb-4">Top Tim Bulan Ini</h3>
                <div class="overflow-y-auto max-h-60">
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($team_performance)): ?>
                                <tr><td class="text-center text-gray-500 text-sm py-4">Belum ada data.</td></tr>
                            <?php else: ?>
                                <?php foreach ($team_performance as $idx => $team): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold                                                                                                                                 <?php echo $idx == 0 ? 'bg-yellow-100 text-yellow-700' : ($idx == 1 ? 'bg-gray-200' : 'bg-orange-100 text-orange-700') ?>">
                                                <?php echo $idx + 1 ?>
                                            </span>
                                            <div>
                                                <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($team->team_name) ?></p>
                                                <p class="text-[10px] text-gray-500"><?php echo $team->active_locations ?> Lokasi</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-right">
                                        <span class="text-sm font-bold text-green-600">Rp                                                                                          <?php echo number_format($team->total_revenue / 1000, 0) ?>k</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 flex flex-col items-center">
                <h3 class="font-bold text-gray-800 mb-2 w-full text-left">Status Survey</h3>
                <div class="h-40 w-40 relative">
                    <canvas id="surveyChart"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center flex-col pointer-events-none">
                         <span class="text-2xl font-bold text-gray-700"><?php echo round($percent) ?>%</span>
                    </div>
                </div>
                <div class="mt-4 w-full flex justify-between text-xs">
                    <div class="flex items-center gap-1"><span class="w-2 h-2 bg-indigo-500 rounded-full"></span> Sudah (<?php echo $surveyed_locations ?? 0 ?>)</div>
                    <div class="flex items-center gap-1"><span class="w-2 h-2 bg-gray-200 rounded-full"></span> Belum (<?php echo $data['survey_chart']['not_surveyed'] ?? 0 ?>)</div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="isLoading" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 border border-gray-200 text-center">
            <h3 class="text-xl font-bold text-gray-800 mb-2" x-text="statusTitle"></h3>
            <p class="text-gray-500 text-sm mb-6" x-text="statusMessage"></p>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden">
                <div class="bg-indigo-600 h-4 rounded-full transition-all duration-300 ease-out" :style="`width: ${progress}%`"></div>
            </div>
            <span class="text-xs font-bold" x-text="Math.round(progress) + '%'"></span>
        </div>
    </div>

    <div x-show="showDetailsModal" x-cloak @keydown.escape.window="showDetailsModal = false" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div @click.away="showDetailsModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b p-4 sticky top-0 bg-white z-10">
                <h3 class="text-xl font-semibold text-gray-900" x-text="details && details.location ? details.location.parking_location : 'Memuat...'"></h3>
                <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-900">&times;</button>
            </div>
            <template x-if="details && details.location">
                <div class="p-4 space-y-4">
                    <div class="p-4 border rounded-md">
                        <h4 class="font-bold text-md mb-2 text-gray-700">Informasi Umum</h4>
                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Alamat</dt>
                            <dd class="text-sm text-gray-900 col-span-2" x-text="details.location.address"></dd>
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Koordinator</dt>
                            <dd class="text-sm text-gray-900 col-span-2" x-text="details.location.coordinator_name"></dd>
                        </dl>
                    </div>
                    <template x-if="details.deposits">
                        <div class="p-4 border rounded-md bg-green-50">
                            <h4 class="font-bold text-md mb-2 text-green-800">Potensi Setoran (Survey)</h4>
                            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2">
                                <dt class="text-sm font-medium text-gray-500 col-span-1">Harian</dt>
                                <dd class="text-sm text-gray-900 col-span-2" x-text="formatCurrency(details.deposits.daily_deposits)"></dd>
                                <dt class="text-sm font-medium text-gray-500 col-span-1">Weekend</dt>
                                <dd class="text-sm text-gray-900 col-span-2" x-text="formatCurrency(details.deposits.weekend_deposits)"></dd>
                                <dt class="text-sm font-medium text-gray-500 col-span-1">Bulanan</dt>
                                <dd class="text-sm text-gray-900 col-span-2" x-text="formatCurrency(details.deposits.monthly_deposits)"></dd>
                            </dl>
                        </div>
                    </template>
                    <template x-if="!details.deposits">
                        <div class="p-4 border-l-4 border-yellow-400 bg-yellow-50 text-center">
                            <p class="text-sm text-yellow-700">Belum ada data survey untuk lokasi ini.</p>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <div x-show="showStreetResultsModal" x-cloak @keydown.escape.window="showStreetResultsModal = false" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div @click.away="showStreetResultsModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
             <div class="flex justify-between items-center border-b p-4 sticky top-0 bg-white z-10">
                <div><h3 class="text-xl font-semibold">Hasil Pencarian Jalan</h3><p class="text-sm text-gray-600" x-text="selectedStreet"></p></div>
                <button @click="showStreetResultsModal = false" class="text-gray-400 hover:text-gray-900">&times;</button>
            </div>
            <div class="p-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Koordinator</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th></tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="location in streetResults" :key="location.id">
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900" x-text="location.parking_location"></td>
                                <td class="px-4 py-2 text-sm text-gray-600" x-text="location.coordinator_name"></td>
                                <td class="px-4 py-2"><button @click="handleDetailClick(location.id); showStreetResultsModal = false" class="text-sm text-blue-600 hover:underline">Lihat Detail</button></td>
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
                <div><h3 class="text-xl font-semibold">Detail Lokasi Koordinator</h3><p class="text-sm text-gray-600 font-bold" x-text="selectedCoordinatorName"></p></div>
                <button @click="showCoordinatorResultsModal = false" class="text-gray-400 hover:text-gray-900">&times;</button>
            </div>
            <template x-if="totalSetoran">
                <div class="p-4 bg-gray-50 border-b grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><p class="text-sm text-gray-500">Total Titik Lokasi</p><p class="text-2xl font-bold" x-text="coordinatorResults.length"></p></div>
                    <div><p class="text-sm text-gray-500">Total Potensi Harian</p><p class="text-2xl font-bold" x-html="formatCurrency(totalSetoran.daily)"></p></div>
                </div>
            </template>
            <div class="p-4 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">No.</th><th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th><th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th><th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harian (Rp)</th></tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(location, index) in coordinatorResults" :key="location.id">
                            <tr>
                                <td class="px-2 py-2 text-sm text-gray-500" x-text="index + 1"></td>
                                <td class="px-2 py-2 font-medium" x-text="location.parking_location"></td>
                                <td class="px-2 py-2 text-sm text-gray-600" x-text="location.address"></td>
                                <td class="px-2 py-2 text-sm" x-html="formatCurrency(location.daily_deposits)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function pimpinanDashboard() {
    return {
        // State Search
        showDetailsModal: false,
        details: {},
        showStreetResultsModal: false,
        streetResults: [],
        selectedStreet: '',
        showCoordinatorResultsModal: false,
        coordinatorResults: [],
        selectedCoordinatorName: '',
        totalSetoran: { daily: 0 },

        // State Export
        incomeChartImg: '',
        surveyChartImg: '',
        isLoading: false,
        progress: 0,
        statusTitle: '',
        statusMessage: '',

        init() {
            const ctxIncome = document.getElementById('incomeChart').getContext('2d');
            new Chart(ctxIncome, {
                type: 'line',
                data: {
                    labels:                            <?php echo json_encode($income_labels ?? []) ?>,
                    datasets: [{
                        label: 'Pendapatan',
                        data:                              <?php echo json_encode($income_values ?? []) ?>,
                        borderColor: '#16a34a', backgroundColor: 'rgba(22, 163, 74, 0.1)',
                        borderWidth: 3, tension: 0.4, fill: true,
                        pointRadius: 4, pointBackgroundColor: '#fff', pointBorderColor: '#16a34a', pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] }, ticks: { callback: (val) => 'Rp ' + (val/1000) + 'k' } }, x: { grid: { display: false } } },
                    animation: { onComplete: () => { this.incomeChartImg = document.getElementById('incomeChart').toDataURL('image/png'); } }
                }
            });

            const ctxSurvey = document.getElementById('surveyChart').getContext('2d');
            new Chart(ctxSurvey, {
                type: 'doughnut',
                data: {
                    labels: ['Sudah', 'Belum'],
                    datasets: [{
                        data: [<?php echo $surveyed_locations ?? 0 ?>,<?php echo $data['survey_chart']['not_surveyed'] ?? 0 ?>],
                        backgroundColor: ['#6366f1', '#e5e7eb'], borderWidth: 0, cutout: '75%'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    animation: { onComplete: () => { this.surveyChartImg = document.getElementById('surveyChart').toDataURL('image/png'); } }
                }
            });

            // INIT TOM SELECT SEARCH
            // Location Search
            let locationTomSelect = new TomSelect('#location_search', {
                valueField: 'id', labelField: 'text', searchField: 'text',
                load: (query, callback) => {
                    if (query.length < 3) return callback();
                    fetch(`<?php echo BASE_URL ?>/parkinglocations/searchJson?q=${encodeURIComponent(query)}`).then(r => r.json()).then(j => callback(j)).catch(() => callback());
                },
                onChange: (value) => {
                    if (value) {
                        this.handleDetailClick(value);
                        // Clear selection to allow re-selecting
                        locationTomSelect.clear(true);
                    }
                }
            });

            // Street Search
            let streetTomSelect = new TomSelect('#street_search', {
                valueField: 'text', // Kirim Nama Jalan
                labelField: 'text', searchField: 'text',
                load: (query, callback) => {
                    if (query.length < 3) return callback();
                    fetch(`<?php echo BASE_URL ?>/parkinglocations/searchAddressJson?q=${encodeURIComponent(query)}`).then(r => r.json()).then(j => callback(j)).catch(() => callback());
                },
                onChange: (value) => {
                    if (value) {
                        this.selectedStreet = value;
                        fetch(`<?php echo BASE_URL ?>/parkinglocations/getLocationsByAddressJson?address=${encodeURIComponent(value)}`)
                            .then(res => res.json()).then(data => {
                                this.streetResults = data;
                                this.showStreetResultsModal = true;
                                streetTomSelect.clear(true);
                            });
                    }
                }
            });

            // Coordinator Search
            let coordinatorTomSelect = new TomSelect('#coordinator_search', {
                valueField: 'id', labelField: 'text', searchField: 'text',
                load: (query, callback) => {
                    if (query.length < 2) return callback();
                    fetch(`<?php echo BASE_URL ?>/parkinglocations/searchCoordinatorsJson?q=${encodeURIComponent(query)}`).then(r => r.json()).then(j => callback(j)).catch(() => callback());
                },
                onChange: (value) => {
                    if (value) {
                        // Ambil teks nama dari option (harus akses via API tomselect internal atau DOM)
                        // Workaround sederhana: Fetch detail ulang akan dapat nama di callback
                        this.fetchCoordinatorLocations(value);

                        // Set nama setelah fetch (di dalam fungsi fetchCoordinatorLocations)
                        // atau ambil dari item tomselect
                        const item = coordinatorTomSelect.getItem(value);
                        if(item) this.selectedCoordinatorName = item.textContent;

                        coordinatorTomSelect.clear(true);
                    }
                }
            });
        },

        handleDetailClick(locationId) {
            fetch(`<?php echo BASE_URL ?>/parkinglocations/getLocationDetailsJson/${locationId}`)
                .then(res => res.json())
                .then(data => {
                    if(data.error) { alert(data.error); return; }
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
                    let totalDaily = 0;
                    if(data && data.length > 0){
                         data.forEach(loc => { totalDaily += parseFloat(loc.daily_deposits) || 0; });
                    }
                    this.totalSetoran.daily = totalDaily;
                });
        },

        formatCurrency(value) {
            const numberValue = parseFloat(value);
            if (isNaN(numberValue)) return '<span class="text-gray-400 italic">Belum Survey</span>';
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(numberValue);
        },

        // --- EXPORT PDF (AJAX FETCH) ---
        handleExport() {
            this.isLoading = true;
            this.progress = 0;
            this.statusTitle = 'Export PDF';
            this.statusMessage = 'Menghubungkan...';

            // Simulasi Progress Bar
            const timer = setInterval(() => {
                if(this.progress < 90) this.progress += 1;
            }, 50);

            // 1. Ambil Data Gambar dari Canvas Chart
            this.incomeChartImg = document.getElementById('incomeChart').toDataURL('image/png');
            this.surveyChartImg = document.getElementById('surveyChart').toDataURL('image/png');

            // 2. Buat FormData untuk dikirim via AJAX
            const formData = new FormData();
            formData.append('income_chart', this.incomeChartImg);
            formData.append('survey_chart', this.surveyChartImg);

            // 3. Kirim Request Fetch (POST)
            fetch('<?php echo BASE_URL ?>/pimpinan/export_pdf', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Gagal generate PDF');
                return response.blob(); // Ambil respon sebagai File (Blob)
            })
            .then(blob => {
                // 4. Sukses! Matikan timer dan set 100%
                clearInterval(timer);
                this.progress = 100;
                this.statusMessage = 'Selesai! Mengunduh...';

                // 5. Buat Link Download Otomatis
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Executive_Report_${new Date().toISOString().slice(0,10)}.pdf`;
                document.body.appendChild(a);
                a.click();
                a.remove();

                // 6. Tutup Loading Modal
                setTimeout(() => {
                    this.isLoading = false;
                    this.progress = 0;
                }, 1500);
            })
            .catch(error => {
                console.error('Export Error:', error);
                clearInterval(timer);
                this.isLoading = false;
                alert('Terjadi kesalahan saat export PDF.');
            });
        }
    }
}
document.addEventListener('alpine:init', () => { Alpine.data('pimpinanDashboard', pimpinanDashboard); });
</script>