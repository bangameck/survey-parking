<div x-data="coordinatorsComponent()" x-init="init()">

    <!-- FORM PENCARIAN -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form action="<?php echo BASE_URL ?>/fieldcoordinators" method="GET">
            <label for="search_term" class="block text-sm font-medium text-gray-700 mb-1">Cari Nama Koordinator</label>
            <div class="flex gap-2">
                <input type="text" name="q" id="search_term" value="<?php echo htmlspecialchars($searchTerm ?? '') ?>"
                    placeholder="Ketik nama koordinator..."
                    class="flex-grow block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <button type="submit"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Cari</button>
                <a href="<?php echo BASE_URL ?>/fieldcoordinators"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- TABEL UTAMA DAN TOMBOL AKSI -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="flex justify-between items-center p-6">
            <h3 class="font-semibold text-lg text-gray-800">Manajemen Koordinator Lapangan</h3>
            <button @click="showCreateModal = true"
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                + Tambah Baru
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                            Koordinator</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($coordinators)): ?>
                    <tr>
                        <td colspan="3" class="text-center py-10 text-gray-500">Tidak ada data koordinator ditemukan.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $nomor = ($page - 1) * 15 + 1; ?>
                    <?php foreach ($coordinators as $coord): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $nomor++ ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <!-- Nama sekarang menjadi tombol untuk membuka modal lokasi -->
                            <button
                                @click="showLocations(<?php echo $coord->id ?>, '<?php echo htmlspecialchars(addslashes($coord->name)) ?>')"
                                class="text-blue-600 hover:text-blue-800 hover:underline text-left">
                                <?php echo htmlspecialchars($coord->name) ?>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button @click="handleEditClick(<?php echo $coord->id ?>)"
                                class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($total_pages > 1): ?>
        <div class="p-6 border-t">
            <nav class="flex items-center justify-between">
                <div class="text-sm text-gray-600 hidden sm:block">
                    Halaman <span class="font-bold"><?php echo $page ?></span> dari <span
                        class="font-bold"><?php echo $total_pages ?></span>
                </div>
                <ul class="flex items-center space-x-1">
                    <?php if ($page > 1): ?>
                    <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                            class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-blue-100 hover:text-blue-700">&laquo;
                            Prev</a></li>
                    <?php endif; ?>

                    <?php
                        $range = 1;
                        for ($i = 1; $i <= $total_pages; $i++):
                            if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)):
                        ?>
                    <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                            class="px-3 py-2 leading-tight border rounded-md<?php echo($i == $page) ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-500 border-gray-300 hover:bg-blue-100 hover:text-blue-700' ?>"><?php echo $i ?></a>
                    </li>
                    <?php
                                                    elseif ($i == $page - $range - 1 || $i == $page + $range + 1):
                                                ?>
                    <li><span class="px-3 py-2 text-gray-500">...</span></li>
                    <?php
                        endif;
                        endfor;
                    ?>

                    <?php if ($page < $total_pages): ?>
                    <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
                            class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-blue-100 hover:text-blue-700">Next
                            &raquo;</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- ====================================================================== -->
    <!-- SEMUA MODAL DITEMPATKAN DI SINI -->
    <!-- ====================================================================== -->

    <!-- MODAL UNTUK TAMBAH KOORDINATOR BARU -->
    <div x-show="showCreateModal" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40"
        @keydown.escape.window="showCreateModal = false">
        <div @click.away="showCreateModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 mx-4">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold">Tambah Koordinator Baru</h3>
                <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-800">&times;</button>
            </div>
            <form action="<?php echo BASE_URL ?>/fieldcoordinators/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
                <div class="mb-4">
                    <label for="create_name" class="block text-gray-700 text-sm font-bold mb-2">Nama
                        Koordinator:</label>
                    <input type="text" id="create_name" name="name"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" @click="showCreateModal = false"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL UNTUK EDIT KOORDINATOR -->
    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40"
        @keydown.escape.window="showEditModal = false">
        <div @click.away="showEditModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 mx-4">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold">Edit Koordinator</h3>
                <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-800">&times;</button>
            </div>
            <form :action="formAction" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
                <div class="mb-4">
                    <label for="edit_name" class="block text-gray-700 text-sm font-bold mb-2">Nama Koordinator:</label>
                    <input type="text" id="edit_name" name="name" x-model="editData.name"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" @click="showEditModal = false"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL BARU: DAFTAR LOKASI PARKIR MILIK KOORDINATOR -->
    <div x-show="showLocationsModal" x-cloak @keydown.escape.window="showLocationsModal = false"
        class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div @click.away="showLocationsModal = false"
            class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center border-b p-4 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-xl font-semibold">Detail Lokasi Koordinator</h3>
                    <p class="text-sm text-gray-600 font-bold" x-text="selectedCoordinatorName"></p>
                </div>
                <button @click="showLocationsModal = false" class="text-gray-400 hover:text-gray-900">&times;</button>
            </div>

            <div class="p-4 bg-gray-50 border-b grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Total Titik Lokasi</p>
                    <p class="text-2xl font-bold" x-text="locationsList.length"></p>
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
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Weekend (Rp)
                            </th>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bulanan (Rp)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-if="locationsList.length === 0">
                            <tr>
                                <td colspan="6" class="text-center text-gray-500 py-6">Tidak ada data lokasi untuk
                                    koordinator ini.</td>
                            </tr>
                        </template>
                        <template x-for="(location, index) in locationsList" :key="location.id">
                            <tr>
                                <td class="px-2 py-2 text-sm text-gray-500" x-html="index + 1"></td>
                                <td class="px-2 py-2 font-medium" x-html="location.parking_location"></td>
                                <td class="px-2 py-2 text-sm text-gray-600" x-html="location.address"></td>
                                <td class="px-2 py-2 text-sm text-gray-600"
                                    x-html="formatCurrency(location.daily_deposits)"></td>
                                <td class="px-2 py-2 text-sm text-gray-600"
                                    x-html="formatCurrency(location.weekend_deposits)"></td>
                                <td class="px-2 py-2 text-sm text-gray-600"
                                    x-html="formatCurrency(location.monthly_deposits)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Script Lengkap untuk Interaktivitas Halaman -->
<script>
    function coordinatorsComponent() {
        return {
            // State untuk modal
            showCreateModal: false,
            showEditModal: false,
            showLocationsModal: false,
            editData: {},
            formAction: '',
            locationsList: [],
            selectedCoordinatorName: '',
            // STATE BARU untuk total
            totalSetoran: {
                daily: 0,
                weekend: 0,
                monthly: 0
            },

            init() {
                /* Tidak ada inisialisasi khusus saat ini */
            },

            handleEditClick(coordinatorId) {
                fetch(`<?php echo BASE_URL ?>/fieldcoordinators/getCoordinatorJson/${coordinatorId}`)
                    .then(res => res.json())
                    .then(data => {
                        this.editData = data;
                        this.formAction = `<?php echo BASE_URL ?>/fieldcoordinators/update/${data.id}`;
                        this.showEditModal = true;
                    });
            },

            // FUNGSI showLocations YANG DIPERBARUI
            showLocations(coordinatorId, coordinatorName) {
                this.selectedCoordinatorName = coordinatorName;
                this.locationsList = [];
                this.showLocationsModal = true;

                // Reset total
                this.totalSetoran = {
                    daily: 0,
                    weekend: 0,
                    monthly: 0
                };

                fetch(`<?php echo BASE_URL ?>/parkinglocations/getLocationsByCoordinatorJson/${coordinatorId}`)
                    .then(res => res.json())
                    .then(data => {
                        this.locationsList = data;
                        // Hitung total setelah data diterima
                        let totalDaily = 0;
                        data.forEach(loc => {
                            totalDaily += parseFloat(loc.daily_deposits) || 0;
                        });
                        this.totalSetoran.daily = totalDaily;
                    });
            },

            // FUNGSI HELPER BARU untuk format mata uang
            formatCurrency(value) {
                // Cek dulu apakah value bisa diubah menjadi angka yang valid
                const numberValue = parseFloat(value);

                // Jika hasilnya bukan angka (NaN), tampilkan 'Belum Survey'
                if (isNaN(numberValue)) {
                    return '<span class="text-gray-400 italic">Belum Survey</span>';
                }

                // Jika valid, format sebagai mata uang dan bungkus dengan tag <strong>
                const formattedValue = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(numberValue);

                return `<strong class="text-gray-900">${formattedValue}</strong>`;
            }
        };
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('coordinatorsComponent', coordinatorsComponent);
    });
</script>