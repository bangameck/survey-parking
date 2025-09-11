<div class="space-y-6" x-data="depositForm()">

    <div class="bg-white rounded-lg shadow p-6 print:hidden">
        <form action="<?php echo BASE_URL ?>/parkingdeposits" method="GET">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Input Data Survey</h3>
            <div class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-grow">
                    <label for="coordinator_filter" class="block text-sm font-medium text-gray-700 mb-1">Pilih
                        Koordinator</label>
                    <select name="coordinator_id" id="coordinator_filter" class="w-full">
                        <option value="">Pilih Koordinator untuk memulai...</option>
                        <?php foreach ($coordinators as $coord): ?>
                        <option value="<?php echo $coord->id ?>"
                            <?php echo($selected_coordinator_id == $coord->id) ? 'selected' : '' ?>>
                            <?php echo htmlspecialchars($coord->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <button type="submit"
                        class="w-full md:w-auto px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Tampilkan
                        Lokasi</button>
                    <?php if ($selected_coordinator_id): ?>
                    <button type="button" onclick="window.print()"
                        class="w-full md:w-auto px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Print
                    </button>
                    <a href="<?php echo BASE_URL ?>/parkingdeposits/export_pdf?coordinator_id=<?php echo $selected_coordinator_id ?>"
                        target="_blank"
                        class="w-full md:w-auto px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-center">
                        Export PDF
                    </a>
                    <a href="<?php echo BASE_URL ?>/parkingdeposits/export_excel?coordinator_id=<?php echo $selected_coordinator_id ?>"
                        class="w-full md:w-auto px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-center">
                        Export Excel
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <?php if ($selected_coordinator_id && ! empty($locations)): ?>
    <form action="<?php echo BASE_URL ?>/parkingdeposits/store" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
        <input type="hidden" name="coordinator_id" value="<?php echo $selected_coordinator_id ?>">

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lokasi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Alamat</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                Harian (Rp)</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                Sabtu/Minggu (Rp)</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                Bulanan (Rp)</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">
                                Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($locations as $loc):
                                // Cek apakah ada data deposit yang sudah ada untuk lokasi ini
                                $deposit = $deposits[$loc->id] ?? null;
                            ?>
                        <tr>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($loc->parking_location) ?></td>
                            <td class="px-4 py-2 text-sm text-gray-500"><?php echo htmlspecialchars($loc->address) ?>
                            </td>
                            <td><input type="number" step="1000" name="deposits[<?php echo $loc->id ?>][daily_deposits]"
                                    value="<?php echo $deposit->daily_deposits ?? '' ?>" @input="calculateTotals"
                                    class="w-full border-gray-300 rounded-md shadow-sm deposit-daily"></td>
                            <td><input type="number" step="1000"
                                    name="deposits[<?php echo $loc->id ?>][weekend_deposits]"
                                    value="<?php echo $deposit->weekend_deposits ?? '' ?>" @input="calculateTotals"
                                    class="w-full border-gray-300 rounded-md shadow-sm deposit-weekend"></td>
                            <td><input type="number" step="1000"
                                    name="deposits[<?php echo $loc->id ?>][monthly_deposits]"
                                    value="<?php echo $deposit->monthly_deposits ?? '' ?>" @input="calculateTotals"
                                    class="w-full border-gray-300 rounded-md shadow-sm deposit-monthly"></td>
                            <td><input type="text" name="deposits[<?php echo $loc->id ?>][information]"
                                    value="<?php echo htmlspecialchars($deposit->information ?? '') ?>"
                                    class="w-full border-gray-300 rounded-md shadow-sm"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-right text-gray-700">TOTAL</td>
                            <td class="px-4 py-3 text-gray-900" x-text="formatCurrency(totalDaily)"></td>
                            <td class="px-4 py-3 text-gray-900" x-text="formatCurrency(totalWeekend)"></td>
                            <td class="px-4 py-3 text-gray-900" x-text="formatCurrency(totalMonthly)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mt-6 print:hidden">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Informasi Tambahan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="surveyor_1" class="block text-sm font-medium text-gray-700">Nama Surveyor 1</label>
                    <input type="text" name="surveyor_1" id="surveyor_1"
                        value="<?php echo htmlspecialchars(! empty($deposits) ? $deposits[array_key_first($deposits)]->surveyor_1 : '') ?>"
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="surveyor_2" class="block text-sm font-medium text-gray-700">Nama Surveyor 2</label>
                    <input type="text" name="surveyor_2" id="surveyor_2"
                        value="<?php echo htmlspecialchars(! empty($deposits) ? $deposits[array_key_first($deposits)]->surveyor_2 : '') ?>"
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="document_survey" class="block text-sm font-medium text-gray-700">Upload Dokumen Survey
                        (PDF)</label>
                    <input type="file" name="document_survey" id="document_survey" accept=".pdf"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    <?php if (! empty($existing_document)): ?>
                    <p class="text-xs text-gray-500 mt-2">
                        File sudah ada:
                        <a href="<?php echo BASE_URL ?>/<?php echo htmlspecialchars($existing_document) ?>"
                            target="_blank" class="text-blue-600 hover:underline">
                            Lihat Dokumen
                        </a>
                        <br>
                        <span class="italic">(Mengupload file baru akan menimpa file yang lama)</span>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end print:hidden">
            <button type="submit"
                class="px-6 py-3 bg-green-600 text-white font-bold rounded-md hover:bg-green-700 shadow-lg">Simpan Semua
                Data Survey</button>
        </div>
    </form>
    <?php elseif ($selected_coordinator_id): ?>
    <div class="bg-white rounded-lg shadow p-10 text-center">
        <p class="text-gray-500">Tidak ada lokasi parkir yang terhubung dengan koordinator ini.</p>
    </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new TomSelect('#coordinator_filter', {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    });

    function depositForm() {
        return {
            totalDaily: 0,
            totalWeekend: 0,
            totalMonthly: 0,
            init() {
                // Hitung total awal saat halaman dimuat (untuk data yang sudah ada)
                this.calculateTotals();
            },
            calculateTotals() {
                let daily = 0;
                let weekend = 0;
                let monthly = 0;
                // Mengambil semua nilai dari input dengan class yang sesuai dan menjumlahkannya
                document.querySelectorAll('.deposit-daily').forEach(el => daily += parseFloat(el.value) || 0);
                document.querySelectorAll('.deposit-weekend').forEach(el => weekend += parseFloat(el.value) || 0);
                document.querySelectorAll('.deposit-monthly').forEach(el => monthly += parseFloat(el.value) || 0);

                this.totalDaily = daily;
                this.totalWeekend = weekend;
                this.totalMonthly = monthly;
            },
            formatCurrency(value) {
                // Memformat angka menjadi format mata uang Rupiah
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