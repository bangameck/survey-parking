<div class="space-y-6">
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-3xl font-bold">Halo, Anggota                                                         <?php echo htmlspecialchars($team_name) ?>! 👋</h2>
            <p class="mt-2 opacity-90">Berikut adalah performa pengambilalihan PKS tim Anda hari ini.</p>
        </div>
        <div class="absolute right-0 top-0 h-full w-1/3 bg-white opacity-10 transform skew-x-12"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500 transform hover:scale-105 transition-transform">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm font-bold tracking-wider">LOKASI DIKELOLA</p>
                    <p class="text-4xl font-bold text-gray-800 mt-2"><?php echo number_format($total_locations) ?></p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Titik parkir aktif</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-green-500 transform hover:scale-105 transition-transform">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm font-bold tracking-wider">SETORAN HARI INI</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">Rp                                                                        <?php echo number_format($today_income, 0, ',', '.') ?></p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"></path></svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Diinput pada                                                               <?php echo date('d M Y') ?></p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-purple-500 transform hover:scale-105 transition-transform">
             <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm font-bold tracking-wider">CAPAIAN BULANAN</p>
                    <p class="text-4xl font-bold text-gray-800 mt-2"><?php echo $monthly_achievement ?>%</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-4">
                <div class="bg-purple-500 h-1.5 rounded-full" style="width:                                                                            <?php echo min($monthly_achievement, 100) ?>%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">Dari target survey s.d hari ini</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-800 text-lg">Performa Setoran (7 Hari Terakhir)</h3>
                <div class="flex gap-4 text-xs font-semibold">
                    <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Realisasi</div>
                    <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-gray-300"></span> Target Survey</div>
                </div>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="teamPerformanceChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col justify-center items-center text-center border border-gray-100">
            <div class="bg-blue-50 p-4 rounded-full mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/2921/2921222.png" alt="Input" class="w-16 h-16 opacity-80">
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Input Setoran Baru</h3>
            <p class="text-gray-500 mb-6 text-sm">Jangan lupa input setoran harian Anda sebelum jam operasional berakhir.</p>
            <a href="<?php echo BASE_URL ?>/teaminput" class="w-full block px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg transition transform hover:scale-105">
                Mulai Input Data
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('teamPerformanceChart').getContext('2d');

    // Data dari PHP
    const labels =                   <?php echo json_encode($chart_labels) ?>;
    const dataReal =                     <?php echo json_encode($chart_real) ?>;
    const dataTarget =                       <?php echo json_encode($chart_target) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Target Survey',
                    data: dataTarget,
                    backgroundColor: 'rgba(229, 231, 235, 0.5)', // Abu-abu transparan
                    borderColor: 'rgba(209, 213, 219, 1)',
                    borderWidth: 2,
                    borderRadius: 4,
                    borderSkipped: false,
                    type: 'line', // Garis Target
                    pointRadius: 0,
                    tension: 0.4
                },
                {
                    label: 'Realisasi Setoran',
                    data: dataReal,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)', // Biru Solid
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 0,
                    borderRadius: 6,
                    barThickness: 20,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }, // Kita pakai legenda custom di HTML
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
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
                            return 'Rp ' + (value / 1000) + 'k'; // Singkat angka
                        },
                        font: { size: 10 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
});
</script>