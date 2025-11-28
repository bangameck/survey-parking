<div x-data="teamReport()" x-init="init()" class="space-y-6">

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div><h2 class="text-2xl font-bold text-gray-800">Laporan Kinerja Tim</h2><p class="text-gray-500 text-sm mt-1">Rekapitulasi setoran & pengeluaran.</p></div>
            <div class="flex bg-gray-100 p-1 rounded-lg"><a href="?type=harian" class="px-4 py-2 rounded-md text-sm font-bold transition                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 <?php echo $report_type == 'harian' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">Laporan Harian</a><a href="?type=bulanan" class="px-4 py-2 rounded-md text-sm font-bold transition<?php echo $report_type == 'bulanan' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">Laporan Bulanan</a></div>
        </div>
        <div class="p-6 bg-gray-50 relative z-10">
             <form id="reportFilterForm" action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <input type="hidden" name="type" value="<?php echo $report_type ?>">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 mb-1">FILTER KOORDINATOR</label>
                    <select id="filter-coord" name="coord_id">
                        <option value="">Semua Koordinator</option>
                        <?php foreach ($assigned_coordinators as $ac): ?>
                            <option value="<?php echo $ac->field_coordinator_id ?>" data-start="<?php echo $ac->start_date ?>"<?php echo $selected_coord == $ac->field_coordinator_id ? 'selected' : '' ?>>
                                <?php echo htmlspecialchars($ac->coordinator_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($report_type == 'harian'): ?>
                    <div><label class="block text-xs font-bold text-gray-500 mb-1">TANGGAL</label><input type="date" name="date" x-model="selectedDate" :min="minDate" class="w-full border-gray-300 rounded-lg text-sm p-2" @change="validateDate"><p x-show="minDate" class="text-[10px] text-blue-600 mt-1" x-text="'Mulai Takeover: ' + formatDateID(minDate)"></p></div>
                <?php else: ?>
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="block text-xs font-bold text-gray-500 mb-1">BULAN</label><select name="month" x-model="selectedMonth" class="w-full border-gray-300 rounded-lg text-sm p-2"><?php for ($m = 1; $m <= 12; $m++): ?><option value="<?php echo $m ?>"<?php echo $month == $m ? 'selected' : '' ?>><?php echo date("F", mktime(0, 0, 0, $m, 10)) ?></option><?php endfor; ?></select></div>
                        <div><label class="block text-xs font-bold text-gray-500 mb-1">TAHUN</label><select name="year" class="w-full border-gray-300 rounded-lg text-sm p-2"><?php for ($y = date('Y'); $y >= 2024; $y--): ?><option value="<?php echo $y ?>"<?php echo $year == $y ? 'selected' : '' ?>><?php echo $y ?></option><?php endfor; ?></select></div>
                    </div>
                <?php endif; ?>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition h-[38px]">Tampilkan Data</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="p-4 border-b flex justify-between items-center bg-green-50">
            <h3 class="font-bold text-green-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"></path></svg>
                Pemasukan (Setoran)
            </h3>
            <button type="button" @click="startExport()" class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 font-bold text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 00-2-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>Export PDF
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr><th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-1/3">Lokasi</th><th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th><th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Realisasi (Rp)</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                     <?php if (empty($report_data)): ?><tr><td colspan="3" class="text-center py-8 text-gray-500">Tidak ada data.</td></tr><?php else: ?>
                     <?php $totalIncome = 0;foreach ($report_data as $row):
                             $amount                                = $report_type == 'harian' ? ($row->amount ?? 0) : $row->total_amount;
                         $totalIncome += $amount; ?>
							                        <tr class="hover:bg-gray-50">
							                            <td class="px-6 py-4"><div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($row->parking_location) ?></div><div class="text-xs text-gray-500"><?php echo htmlspecialchars($row->address) ?></div></td>
							                            <td class="px-6 py-4 text-center"><?php echo $amount > 0 ? '<span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">Sudah Setor</span>' : '<span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">Belum</span>' ?></td>
							                            <td class="px-6 py-4 text-right font-bold text-gray-900">Rp							                                                                                       						                                                                                       					                                                                                       				                                                                                       			                                                                                       		                                                                                        <?php echo number_format($amount, 0, ',', '.') ?></td>
							                        </tr>
							                     <?php endforeach; ?>
                     <tr class="bg-gray-100 font-bold"><td colspan="2" class="px-6 py-4 text-right text-gray-700 uppercase">Total Pemasukan:</td><td class="px-6 py-4 text-right text-green-700 text-lg">Rp<?php echo number_format($totalIncome, 0, ',', '.') ?></td></tr>
                     <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="p-4 border-b flex justify-between items-center bg-red-50">
            <h3 class="font-bold text-red-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Pengeluaran Operasional
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <?php if ($report_type != 'harian'): ?>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-32">Tanggal</th>
                        <?php endif; ?>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Koordinator / Keterangan</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase w-40">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php
                        $totalExpense = 0;
                    if (empty($expenses)): ?>
                        <tr><td colspan="<?php echo $report_type != 'harian' ? '3' : '2'?>" class="text-center py-4 text-gray-500 text-sm italic">Tidak ada pengeluaran tercatat.</td></tr>
                    <?php else:
                            // Logika Grouping Tampilan
                            $currentCoord = '';
                            foreach ($expenses as $exp):
                                $totalExpense += $exp->amount;
                            ?>
		                        <?php if ($currentCoord != $exp->coordinator_name): $currentCoord = $exp->coordinator_name; ?>
			                            <tr class="bg-red-50">
			                                <td colspan="<?php echo $report_type != 'harian' ? '3' : '2'?>" class="px-6 py-2 text-xs font-bold text-red-800 uppercase tracking-wider">
			                                    <?php echo htmlspecialchars($currentCoord)?>
			                                </td>
			                            </tr>
			                        <?php endif; ?>

		                        <tr class="hover:bg-gray-50">
		                            <?php if ($report_type != 'harian'): ?>
		                                <td class="px-6 py-3 text-sm text-gray-600 whitespace-nowrap">
		                                    <?php echo date('d/m/Y', strtotime($exp->expense_date))?>
		                                </td>
		                            <?php endif; ?>
	                            <td class="px-6 py-3 text-sm text-gray-700 pl-8">
	                                <?php echo htmlspecialchars($exp->description)?>
	                            </td>
	                            <td class="px-6 py-3 text-right text-sm font-bold text-red-600">
	                                Rp <?php echo number_format($exp->amount, 0, ',', '.')?>
	                            </td>
	                        </tr>
	                    <?php endforeach;endif; ?>

                    <tr class="bg-gray-100 font-bold">
                        <td colspan="<?php echo $report_type != 'harian' ? '2' : '1'?>" class="px-6 py-4 text-right text-gray-700 uppercase">Total Pengeluaran:</td>
                        <td class="px-6 py-4 text-right text-red-700 text-lg">Rp <?php echo number_format($totalExpense, 0, ',', '.')?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-indigo-900 text-white rounded-2xl shadow-2xl p-6 flex justify-between items-center">
        <span class="text-xl font-bold uppercase tracking-wider">Total Setoran Bersih</span>
        <?php $netto = ($totalIncome ?? 0) - ($totalExpense ?? 0); ?>
        <span class="text-3xl font-bold text-yellow-400">Rp<?php echo number_format($netto, 0, ',', '.') ?></span>
    </div>

    <div x-show="isLoading" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity"><div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 border border-gray-200 text-center"><h3 class="text-xl font-bold text-gray-800 mb-2" x-text="statusTitle"></h3><p class="text-gray-500 text-sm mb-6" x-text="statusMessage"></p><div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden"><div class="bg-blue-600 h-4 rounded-full transition-all duration-300 ease-out" :style="`width: ${progress}%`"></div></div><span class="text-xs font-bold" x-text="Math.round(progress) + '%'"></span></div></div>
</div>

<script>
function teamReport() {
        return {
            selectedCoord: '<?php echo $selected_coord ?>', selectedDate: '<?php echo $date ?? date('Y-m-d') ?>', selectedMonth: '<?php echo $month ?? date('m') ?>', selectedYear: '<?php echo $year ?? date('Y') ?>', reportType: '<?php echo $report_type ?>', minDate: '', isLoading: false, progress: 0, statusTitle: '', statusMessage: '', tomSelect: null,

            init() {
                this.tomSelect = new TomSelect('#filter-coord', { create: false, dropdownParent: 'body', sortField: { field: "text", direction: "asc" }, onChange: (value) => { this.selectedCoord = value; this.updateMinDate(value); } });
                if (this.selectedCoord) this.updateMinDate(this.selectedCoord);
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

        updateMinDate(coordId) { if (!coordId) { this.minDate = ''; return; } const select = document.getElementById('filter-coord'); const option = select.querySelector(`option[value="${coordId}"]`); if (option) { this.minDate = option.getAttribute('data-start'); this.validateDate(); } },

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
document.addEventListener('alpine:init', () => { Alpine.data('teamReport', teamReport); });
</script>