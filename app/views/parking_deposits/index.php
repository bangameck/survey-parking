<?php
    // Tentukan status Admin untuk logika tampilan
    $isAdmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
    // Class tambahan untuk input yang disabled agar terlihat mati visualnya
    $disabledClass = $isAdmin ? '' : 'bg-gray-100 text-gray-500 cursor-not-allowed';
    $readonlyAttr  = $isAdmin ? '' : 'disabled';
?>

<div class="space-y-6" x-data="depositForm()">

    <div class="bg-white rounded-lg shadow p-6 print:hidden">
        <form action="<?php echo BASE_URL?>/parkingdeposits" method="GET">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Input Data Survey</h3>
            <div class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-grow">
                    <label for="coordinator_filter" class="block text-sm font-medium text-gray-700 mb-1">Pilih Koordinator</label>
                    <select name="coordinator_id" id="coordinator_filter" class="w-full">
                        <option value="">Pilih Koordinator untuk memulai...</option>
                        <?php foreach ($coordinators as $coord): ?>
                        <option value="<?php echo $coord->id?>" <?php echo ($selected_coordinator_id == $coord->id) ? 'selected' : ''?>>
                            <?php echo htmlspecialchars($coord->name)?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <button type="submit" class="w-full md:w-auto px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        Tampilkan Lokasi
                    </button>

                    <?php if ($selected_coordinator_id): ?>
                        <a href="<?php echo BASE_URL?>/parkingdeposits/export_excel?coordinator_id=<?php echo $selected_coordinator_id?>" target="_blank" class="w-full md:w-auto px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Excel
                        </a>
                        <a href="<?php echo BASE_URL?>/parkingdeposits/export_pdf?coordinator_id=<?php echo $selected_coordinator_id?>" target="_blank" class="w-full md:w-auto px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <?php if ($selected_coordinator_id && empty($locations)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <p class="text-yellow-700">Tidak ada data lokasi parkir untuk koordinator ini.</p>
        </div>
    <?php endif; ?>

    <?php if (! empty($locations)): ?>
    <form action="<?php echo BASE_URL?>/parkingdeposits/store" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
        <input type="hidden" name="coordinator_id" value="<?php echo $selected_coordinator_id?>">

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Nama Lokasi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Alamat</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harian (Rp)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weekend (Rp)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulanan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                            $no = 1;
                            foreach ($locations as $loc):
                                // Cari data deposit yang sudah ada
                                $currentDeposit = null;
                                foreach ($deposits as $dep) {
                                    if ($dep->parking_location_id == $loc->id) {
                                        $currentDeposit = $dep;
                                        break;
                                    }
                                }
                            ?>
	                        <tr>
	                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><?php echo $no++?></td>
	                            <td class="px-4 py-4 text-sm font-medium text-gray-900">
	                                <?php echo htmlspecialchars($loc->parking_location)?>
	                                <input type="hidden" name="deposits[<?php echo $loc->id?>][location_id]" value="<?php echo $loc->id?>">
	                            </td>
	                            <td class="px-4 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($loc->address)?></td>

	                            <td class="px-4 py-4 whitespace-nowrap">
	                                <input type="text" name="deposits[<?php echo $loc->id?>][daily]"
	                                       class="deposit-daily shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md text-right font-mono <?php echo $disabledClass?>"
	                                       placeholder="0"
	                                       value="<?php echo $currentDeposit ? number_format($currentDeposit->daily_deposits, 0, ',', '.') : ''?>"
	                                       @input="formatRupiahInput($event); calculateTotals()"
	                                       <?php echo $readonlyAttr?>>
	                            </td>

	                            <td class="px-4 py-4 whitespace-nowrap">
	                                <input type="text" name="deposits[<?php echo $loc->id?>][weekend]"
	                                       class="deposit-weekend shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md text-right font-mono <?php echo $disabledClass?>"
	                                       placeholder="0"
	                                       value="<?php echo $currentDeposit ? number_format($currentDeposit->weekend_deposits, 0, ',', '.') : ''?>"
	                                       @input="formatRupiahInput($event); calculateTotals()"
	                                       <?php echo $readonlyAttr?>>
	                            </td>

	                            <td class="px-4 py-4 whitespace-nowrap">
	                                <input type="text" name="deposits[<?php echo $loc->id?>][monthly]"
	                                       class="deposit-monthly shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md text-right font-mono <?php echo $disabledClass?>"
	                                       placeholder="0"
	                                       value="<?php echo $currentDeposit ? number_format($currentDeposit->monthly_deposits, 0, ',', '.') : ''?>"
	                                       @input="formatRupiahInput($event); calculateTotals()"
	                                       <?php echo $readonlyAttr?>>
	                            </td>
	                        </tr>
	                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-gray-700">TOTAL ESTIMASI:</td>
                            <td class="px-4 py-3 text-right text-gray-900" x-text="formatCurrency(totalDaily)">Rp 0</td>
                            <td class="px-4 py-3 text-right text-gray-900" x-text="formatCurrency(totalWeekend)">Rp 0</td>
                            <td class="px-4 py-3 text-right text-gray-900" x-text="formatCurrency(totalMonthly)">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <h4 class="font-bold text-gray-700 mb-3">Data Surveyor</h4>
                    <div class="space-y-3">
                        <?php
                            $existing_surveyor_1 = '';
                            $existing_surveyor_2 = '';
                            if (! empty($deposits)) {
                                $first_dep           = reset($deposits);
                                $existing_surveyor_1 = $first_dep->surveyor_1 ?? '';
                                $existing_surveyor_2 = $first_dep->surveyor_2 ?? '';
                            }
                        ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Surveyor 1</label>
                            <input type="text" name="surveyor_1"
                                   value="<?php echo htmlspecialchars($existing_surveyor_1)?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm <?php echo $disabledClass?>"
                                   <?php echo $readonlyAttr?>>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Surveyor 2</label>
                            <input type="text" name="surveyor_2"
                                   value="<?php echo htmlspecialchars($existing_surveyor_2)?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm <?php echo $disabledClass?>"
                                   <?php echo $readonlyAttr?>>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-gray-700 mb-3">Dokumen Survey</h4>

                    <?php if ($isAdmin): ?>
                        <label class="block text-sm font-medium text-gray-700">Upload Dokumen Survey (PDF)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload file baru</span>
                                        <input id="file-upload" name="document_survey" type="file" class="sr-only" accept="application/pdf">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">PDF hingga 5MB</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <?php if (! empty($existing_document)): ?>
                            <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <div class="flex items-center gap-2">
                                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 00-2 2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    <span class="text-sm text-blue-700 font-medium">Dokumen Tersedia</span>
                                </div>
                                <a href="<?php echo BASE_URL . '/' . $existing_document?>" target="_blank" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 font-bold">Download / Lihat</a>
                            </div>
                        <?php else: ?>
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-md text-center"><span class="text-sm text-gray-400 italic">Belum ada dokumen yang diupload.</span></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($isAdmin): ?>
                <div class="mt-8 flex justify-end pt-6 border-t border-gray-200">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform hover:scale-105 transition">Simpan Semua Data</button>
                </div>
            <?php endif; ?>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#coordinator_filter', { create: false, sortField: { field: "text", direction: "asc" } });
    });

    function depositForm() {
        return {
            totalDaily: 0,
            totalWeekend: 0,
            totalMonthly: 0,

            init() {
                // Hitung total saat halaman dimuat
                this.calculateTotals();
            },

            // Hitung Total Otomatis
            calculateTotals() {
                let daily = 0;
                let weekend = 0;
                let monthly = 0;

                // Fungsi helper untuk parse angka dari format rupiah (hapus titik)
                const parseRupiah = (val) => parseFloat(val.replace(/\./g, '')) || 0;

                document.querySelectorAll('.deposit-daily').forEach(el => daily += parseRupiah(el.value));
                document.querySelectorAll('.deposit-weekend').forEach(el => weekend += parseRupiah(el.value));
                document.querySelectorAll('.deposit-monthly').forEach(el => monthly += parseRupiah(el.value));

                this.totalDaily = daily;
                this.totalWeekend = weekend;
                this.totalMonthly = monthly;
            },

            // Format Rupiah saat mengetik (Visual)
            formatRupiahInput(e) {
                let val = e.target.value.replace(/[^0-9]/g, ''); // Hapus karakter non-angka
                if (val === '') {
                    e.target.value = '';
                    return;
                }

                // Tambahkan titik ribuan
                let number_string = val.toString(),
                    sisa = number_string.length % 3,
                    rupiah = number_string.substr(0, sisa),
                    ribuan = number_string.substr(sisa).match(/\d{3}/g);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                e.target.value = rupiah;
            },

            // Format Rupiah untuk tampilan Text (Label Total)
            formatCurrency(value) {
                if (isNaN(value)) return 'Rp 0';
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(value);
            }
        }
    }
</script>