<div class="space-y-6" x-data="bendaharaDashboard()">

    <div class="bg-gradient-to-r from-teal-600 to-teal-800 rounded-2xl shadow-xl p-8 text-white flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold">Halo, Bendahara! 👋</h2>
            <p class="mt-2 opacity-90">Laporan Keuangan Real-time dari Tim Lapangan (Takeover).</p>
        </div>
        <div class="hidden md:block text-right">
            <p class="text-sm font-medium opacity-75">Tanggal Hari Ini</p>
            <p class="text-2xl font-bold"><?php echo date('d F Y') ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-blue-500 transform hover:scale-105 transition-transform">
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Setoran Hari Ini</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">Rp                                                                                                                               <?php echo number_format($stats->today, 0, ',', '.') ?></p>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-green-500 transform hover:scale-105 transition-transform">
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Bulan Ini (<?php echo date('F') ?>)</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">Rp                                                                                                                               <?php echo number_format($stats->this_month, 0, ',', '.') ?></p>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-purple-500 transform hover:scale-105 transition-transform">
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Tahun Ini (<?php echo date('Y') ?>)</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">Rp                                                                                                                               <?php echo number_format($stats->this_year, 0, ',', '.') ?></p>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-teal-500 transform hover:scale-105 transition-transform">
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Total Saldo Masuk</p>
            <p class="text-2xl font-bold text-teal-600 mt-2">Rp                                                                                                                               <?php echo number_format($stats->total_all_time, 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-800">Tren Pendapatan (7 Hari Terakhir)</h3>
            </div>
            <div class="relative h-80 w-full">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Top Tim Bulan Ini</h3>
            <div class="overflow-y-auto max-h-80">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Tim</th>
                            <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Setoran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($team_performance)): ?>
                            <tr><td colspan="2" class="text-center py-4 text-gray-500 text-sm">Belum ada data bulan ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($team_performance as $index => $team): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold
                                            <?php echo $index == 0 ? 'bg-yellow-100 text-yellow-700' : ($index == 1 ? 'bg-gray-200 text-gray-700' : 'bg-orange-50 text-orange-700') ?>">
                                            <?php echo $index + 1 ?>
                                        </span>
                                        <div>
                                            <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($team->team_name) ?></p>
                                            <p class="text-xs text-gray-500"><?php echo $team->active_locations ?> Lokasi Aktif</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold text-green-600">Rp                                                                                                                                                                           <?php echo number_format($team->total_revenue, 0, ',', '.') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                <a href="<?php echo BASE_URL ?>/reports" class="text-sm text-blue-600 hover:underline font-medium">Lihat Laporan Lengkap &rarr;</a>
            </div>
        </div>
    </div>
</div>

<script>
function bendaharaDashboard() {
    return {
        init() {
            const ctx = document.getElementById('incomeChart').getContext('2d');

            // Data dari PHP
            const labels =                                                     <?php echo json_encode($chart_labels) ?>;
            const dataValues =                                                             <?php echo json_encode($chart_data) ?>;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pendapatan Harian',
                        data: dataValues,
                        borderColor: '#0d9488', // Teal
                        backgroundColor: 'rgba(13, 148, 136, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0d9488',
                        pointBorderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.4 // Kurva halus
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [2, 4], color: '#f3f4f6' },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000) + 'k';
                                },
                                font: { size: 11 }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        }
    }
}
document.addEventListener('alpine:init', () => {
    Alpine.data('bendaharaDashboard', bendaharaDashboard);
});
</script>