<div x-data="teamReport()" x-init="init()" class="space-y-6">

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Laporan Kinerja Tim</h2>
                <p class="text-gray-500 text-sm mt-1">Rekapitulasi & Evaluasi Target Setoran.</p>
            </div>

            <div class="flex bg-gray-100 p-1 rounded-lg">
                <a href="?type=harian" class="px-4 py-2 rounded-md text-sm font-bold transition <?php echo $report_type == 'harian' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'?>">
                    Laporan Harian
                </a>
                <a href="?type=bulanan" class="px-4 py-2 rounded-md text-sm font-bold transition <?php echo $report_type == 'bulanan' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'?>">
                    Laporan Bulanan
                </a>
            </div>
        </div>

        <div class="p-6 bg-gray-50 relative z-10">
            <form id="reportFilterForm" action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <input type="hidden" name="type" value="<?php echo $report_type?>">

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 mb-1">FILTER KOORDINATOR</label>
                    <select id="filter-coord" name="coord_id" placeholder="Semua Koordinator...">
                        <option value="">Semua Koordinator</option>
                        <?php foreach ($assigned_coordinators as $ac): ?>
                            <option value="<?php echo $ac->field_coordinator_id?>"
                                    data-start="<?php echo $ac->start_date?>"
                                    <?php echo $selected_coord == $ac->field_coordinator_id ? 'selected' : ''?>>
                                <?php echo htmlspecialchars($ac->coordinator_name)?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($report_type == 'harian'): ?>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">TANGGAL</label>
                        <input type="date" name="date" x-model="selectedDate" :min="minDate" class="w-full border-gray-300 rounded-lg text-sm p-2" @change="validateDate">
                        <p x-show="minDate" class="text-[10px] text-blue-600 mt-1" x-text="'Mulai Takeover: ' + formatDateID(minDate)"></p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">BULAN</label>
                            <select name="month" x-model="selectedMonth" class="w-full border-gray-300 rounded-lg text-sm p-2">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m?>" <?php echo $month == $m ? 'selected' : ''?>><?php echo date("F", mktime(0, 0, 0, $m, 10))?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">TAHUN</label>
                            <select name="year" class="w-full border-gray-300 rounded-lg text-sm p-2">
                                <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                                    <option value="<?php echo $y?>" <?php echo $year == $y ? 'selected' : ''?>><?php echo $y?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition h-[38px]">
                    Tampilkan Data
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="font-bold text-gray-700">Hasil: <?php echo count($report_data)?> Lokasi</h3>
            <button type="button" @click="startExport()" class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 font-bold text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Export PDF
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-1/3">Lokasi</th>
                        <?php if ($report_type == 'harian'): ?>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status & Target</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Catatan</th>
                        <?php else: ?>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Periode</th>
                        <?php endif; ?>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Realisasi (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php if (empty($report_data)): ?>
                        <tr><td colspan="4" class="text-center py-8 text-gray-500">Tidak ada data ditemukan.</td></tr>
                    <?php else: ?>
                        <?php
                            $grandTotal = 0;
                            foreach ($report_data as $row):
                                $amount = $report_type == 'harian' ? ($row->amount ?? 0) : $row->total_amount;
                                $grandTotal += $amount;

                                // --- LOGIKA TARGET (PHP) ---
                                $target     = 0;
                                $tipeTarget = 'Belum Survey';
                                if ($row->monthly_deposits > 0) {$target = $row->monthly_deposits;
                                    $tipeTarget                       = 'Bulanan';} elseif ($row->daily_deposits > 0) {$target = $row->daily_deposits;
                                $tipeTarget                        = 'Harian';} elseif ($row->weekend_deposits > 0) {$target = $row->weekend_deposits;
                                $tipeTarget                        = 'Weekend';}

                            // --- LOGIKA STATUS CERDAS ---
                            $statusHTML = '<span class="px-2 py-1 rounded-full text-xs font-bold uppercase bg-red-100 text-red-800">Belum Setor</span>';

                            if ($report_type == 'harian') {
                                if ($amount > 0) {
                                    $statusHTML = '<span class="px-2 py-1 rounded-full text-xs font-bold uppercase bg-green-100 text-green-800">Sudah Setor</span>';
                                } elseif ($tipeTarget == 'Bulanan' && ($row->is_paid_monthly > 0)) {
                                    // LUNAS BULANAN (Walau hari ini 0)
                                    $statusHTML = '<span class="px-2 py-1 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-800">Lunas Bulanan</span>';
                                } elseif ($tipeTarget == 'Weekend' && ($row->is_paid_weekly > 0)) {
                                    // LUNAS WEEKEND (Walau hari ini 0)
                                    $statusHTML = '<span class="px-2 py-1 rounded-full text-xs font-bold uppercase bg-yellow-100 text-yellow-800">Lunas Mingguan</span>';
                                }
                            }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($row->parking_location)?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($row->address)?></div>
                            </td>

                            <?php if ($report_type == 'harian'): ?>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <?php echo $statusHTML?>

                                        <?php if ($target > 0): ?>
                                            <span class="text-[10px] text-gray-500">
                                                Target: Rp <?php echo number_format($target, 0, ',', '.')?> (<?php echo $tipeTarget?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 italic"><?php echo htmlspecialchars($row->notes ?? '-')?></td>
                            <?php else: ?>
                                <td class="px-6 py-4 text-center text-sm font-medium text-gray-600">
                                    <?php
                                        if ($tipeTarget == 'Bulanan') {
                                            echo "1 Bulan";
                                        } elseif ($tipeTarget == 'Weekend') {
                                            echo $row->total_trx . " Minggu";
                                        } else {
                                            echo $row->total_trx . " Hari";
                                        }

                                    ?>
                                </td>
                            <?php endif; ?>

                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <?php
                                    // Logika Warna Angka
                                    $textClass = 'text-gray-400';
                                    if ($amount > 0) {
                                        if ($amount >= $target && $target > 0) {
                                            $textClass = 'text-green-600';
                                        } elseif ($amount < $target && $target > 0) {
                                            $textClass = 'text-orange-500';
                                        } else {
                                            $textClass = 'text-gray-900';
                                        }

                                    }
                                ?>
                                <span class="text-sm font-bold <?php echo $textClass?>">
                                    Rp <?php echo number_format($amount, 0, ',', '.')?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <tr class="bg-gray-100 font-bold">
                            <td colspan="<?php echo $report_type == 'harian' ? '3' : '2'?>" class="px-6 py-4 text-right text-gray-700 uppercase">Total Pendapatan:</td>
                            <td class="px-6 py-4 text-right text-blue-700 text-lg">Rp <?php echo number_format($grandTotal, 0, ',', '.')?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="isLoading" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 border border-gray-200 text-center">
            <h3 class="text-xl font-bold text-gray-800 mb-2" x-text="statusTitle"></h3>
            <p class="text-gray-500 text-sm mb-6" x-text="statusMessage"></p>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden">
                <div class="bg-blue-600 h-4 rounded-full transition-all duration-300 ease-out" :style="`width: ${progress}%`"></div>
            </div>
            <span class="text-xs font-bold" x-text="Math.round(progress) + '%'"></span>
        </div>
    </div>

</div>

<script>
function teamReport() {
    return {
        // State
        selectedCoord: '<?php echo $selected_coord ?>',
        selectedDate: '<?php echo $date ?? date('Y-m-d') ?>',
        selectedMonth: '<?php echo $month ?? date('m') ?>',
        selectedYear: '<?php echo $year ?? date('Y') ?>',
        reportType: '<?php echo $report_type ?>',
        minDate: '',

        // Loading State
        isLoading: false,
        progress: 0,
        statusTitle: '',
        statusMessage: '',
        timer: null,
        tomSelect: null,

        init() {
            // Init TomSelect
            this.tomSelect = new TomSelect('#filter-coord', {
                create: false,
                sortField: { field: "text", direction: "asc" },
                onChange: (value) => {
                    this.selectedCoord = value;
                    this.updateMinDate(value);
                }
            });

            // Set min date awal jika ada koordinator terpilih
            if (this.selectedCoord) {
                this.updateMinDate(this.selectedCoord);
            }
        },

        // Update Min Date berdasarkan koordinator
        updateMinDate(coordId) {
            if (!coordId) {
                this.minDate = '';
                return;
            }

            // Ambil atribut data-start dari option asli
            const select = document.getElementById('filter-coord');
            const option = select.querySelector(`option[value="${coordId}"]`);

            if (option) {
                const startDate = option.getAttribute('data-start');
                this.minDate = startDate;

                // Validasi tanggal saat ini
                this.validateDate();
            }
        },

        // Cek apakah tanggal valid
        validateDate() {
            if (this.minDate && this.selectedDate < this.minDate) {
                alert(`Tanggal disesuaikan! Data koordinator ini baru mulai tersedia dari tanggal ${this.formatDateID(this.minDate)}.`);
                this.selectedDate = this.minDate;
            }
        },

        // Helper Format Tanggal Indo
        formatDateID(dateString) {
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        },

        // Fungsi Export PDF dengan Progress Bar
        startExport() {
            this.isLoading = true;
            this.progress = 0;
            this.statusTitle = 'Menyusun Laporan PDF';
            this.statusMessage = 'Menghubungkan ke server...';

            // Simulasi Progress
            this.timer = setInterval(() => {
                if (this.progress < 30) {
                    this.progress += 2;
                    this.statusMessage = 'Mengambil data database...';
                } else if (this.progress < 70) {
                    this.progress += 1;
                    this.statusMessage = 'Melakukan kalkulasi setoran...';
                } else if (this.progress < 90) {
                    this.progress += 0.5;
                    this.statusMessage = 'Finalisasi dokumen PDF...';
                }
            }, 100);

            // Bangun URL dengan Parameter
            const params = new URLSearchParams({
                type: this.reportType,
                coord_id: this.selectedCoord,
                date: this.selectedDate,
                month: this.selectedMonth,
                year: this.selectedYear
            });

            // Fetch Blob (Download File)
            fetch(`<?php echo BASE_URL ?>/team/export_report?${params.toString()}`)
                .then(response => {
                    if (!response.ok) throw new Error('Gagal download');
                    return response.blob();
                })
                .then(blob => {
                    clearInterval(this.timer);
                    this.progress = 100;
                    this.statusMessage = 'Selesai! Mengunduh file...';

                    // Trigger Download
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    const filename = `Laporan_Tim_${this.reportType}_${new Date().toISOString().slice(0,10)}.pdf`;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                    // Tutup Modal
                    setTimeout(() => {
                        this.isLoading = false;
                        this.progress = 0;
                    }, 1500);
                })
                .catch(error => {
                    console.error(error);
                    clearInterval(this.timer);
                    this.isLoading = false;
                    alert('Terjadi kesalahan saat mengunduh PDF.');
                });
        }
    }
}
document.addEventListener('alpine:init', () => {
    Alpine.data('teamReport', teamReport);
});
</script>