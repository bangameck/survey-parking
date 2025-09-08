<div x-data="parkingLocationsComponent()" x-init="initTomSelect()" class="space-y-6">

    <div class="bg-white rounded-lg shadow-md p-6 print:hidden">
        <form action="<?php echo BASE_URL ?>/parkinglocations" method="GET">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Filter & Pencarian</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="search_term" class="block text-sm font-medium text-gray-700 mb-1">Cari Nama
                        Lokasi</label>
                    <input type="text" name="q" id="search_term"
                        value="<?php echo htmlspecialchars($searchTerm ?? '') ?>" placeholder="Ketik nama lokasi..."
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="coordinator_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter
                        Koordinator</label>
                    <select id="coordinator_filter" name="coordinator_id">
                        <option value="">Tampilkan Semua</option>
                        <?php foreach ($coordinators as $coord): ?>
                        <option value="<?php echo $coord->id ?>"
                            <?php echo(isset($selected_coordinator) && $selected_coordinator == $coord->id) ? 'selected' : '' ?>>
                            <?php echo htmlspecialchars($coord->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="submit"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Cari</span>
                </button>
                <a href="<?php echo BASE_URL ?>/parkinglocations"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-center">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-6 gap-4 print:hidden">
            <h3 class="font-semibold text-lg text-gray-800">Daftar Lokasi Parkir</h3>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <div class="flex gap-2 flex-wrap justify-end">
                <?php
                    // Siapkan parameter query yang bersih, hanya untuk filter
                    $exportParams = [];
                    if (! empty($searchTerm)) {
                        $exportParams['q'] = $searchTerm;
                    }
                    if (! empty($selected_coordinator)) {
                        $exportParams['coordinator_id'] = $selected_coordinator;
                    }
                    // Buat query string dari parameter yang bersih
                    $exportQueryString = http_build_query($exportParams);
                ?>
                <a href="<?php echo BASE_URL ?>/parkinglocations/export_pdf?<?php echo $exportQueryString ?>"
                    target="_blank"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span>Export PDF</span>
                </a>

                <button @click="showImportModal = true"
                    class="px-4 py-2 bg-teal-500 text-white rounded-md hover:bg-teal-600 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import File
                </button>
                <button @click="showCreateModal = true"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                    + Tambah Baru
                </button>
            </div>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Koordinator</th>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                        </th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($locations)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-500">
                            Tidak ada data yang ditemukan.
                        </td>
                    </tr>
                    <?php else: ?>
<?php $nomor = ($page - 1) * 15 + 1; ?>
<?php foreach ($locations as $loc): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $nomor++ ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($loc->parking_location) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($loc->address) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($loc->coordinator_name) ?></td>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button @click="handleEditClick(<?php echo $loc->id ?>)"
                                class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
                            <form id="delete-form-<?php echo $loc->id ?>"
                                action="<?php echo BASE_URL ?>/parkinglocations/destroy/<?php echo $loc->id ?>"
                                method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
                                <button type="button" @click="confirmDelete('delete-form-<?php echo $loc->id ?>')"
                                    class="text-red-600 hover:text-red-900">
                                    Hapus
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
<?php endif; ?>
                </tbody>
            </table>
        </div>

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

    <div x-show="showCreateModal" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40"
        @keydown.escape.window="showCreateModal = false">
        <div @click.away="showCreateModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 mx-4">
            <h3 class="text-xl font-semibold border-b pb-3 mb-4">Tambah Lokasi Baru</h3>
            <form action="<?php echo BASE_URL ?>/parkinglocations/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
                <div class="space-y-4">
                    <div>
                        <label for="create_coord" class="block text-sm font-medium text-gray-700">Koordinator</label>
                        <select name="field_coordinator_id" id="create_coord" required class="mt-1 block w-full">
                            <option value="">Pilih Koordinator...</option>
                            <?php foreach ($coordinators as $coord): ?>
                            <option value="<?php echo $coord->id ?>"><?php echo htmlspecialchars($coord->name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="create_loc" class="block text-sm font-medium text-gray-700">Nama Lokasi</label>
                        <input type="text" name="parking_location" id="create_loc" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="create_addr" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea name="address" id="create_addr" rows="3" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-4 mt-6 pt-4 border-t">
                    <button type="button" @click="showCreateModal = false"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40"
        @keydown.escape.window="showEditModal = false">
        <div @click.away="showEditModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 mx-4">
            <h3 class="text-xl font-semibold border-b pb-3 mb-4">Edit Lokasi</h3>
            <form :action="formAction" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
                <div class="space-y-4">
                    <div>
                        <label for="edit_coord" class="block text-sm font-medium text-gray-700">Koordinator</label>
                        <select name="field_coordinator_id" id="edit_coord" required class="mt-1 block w-full">
                            <option value="">Pilih Koordinator...</option>
                            <?php foreach ($coordinators as $coord): ?>
                            <option :selected="editData.field_coordinator_id ==<?php echo $coord->id ?>"
                                value="<?php echo $coord->id ?>"><?php echo htmlspecialchars($coord->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="edit_loc" class="block text-sm font-medium text-gray-700">Nama Lokasi</label>
                        <input type="text" name="parking_location" id="edit_loc" :value="editData.parking_location"
                            required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="edit_addr" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea name="address" id="edit_addr" rows="3" x-text="editData.address" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-4 mt-6 pt-4 border-t">
                    <button type="button" @click="showEditModal = false"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showImportModal" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40"
        @keydown.escape.window="showImportModal = false">
        <div @click.away="showImportModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 mx-4">
            <h3 class="text-xl font-semibold border-b pb-3 mb-4">Import Lokasi dari File</h3>
            <form action="<?php echo BASE_URL ?>/parkinglocations/import" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
                <div class="space-y-4">
                    <div>
                        <label for="import_coord" class="block text-sm font-medium text-gray-700">Pilih
                            Koordinator</label>
                        <select name="field_coordinator_id" id="import_coord" required class="mt-1 block w-full">
                            <option value="">Pilih Koordinator...</option>
                            <?php foreach ($coordinators as $coord): ?>
                            <option value="<?php echo $coord->id ?>"><?php echo htmlspecialchars($coord->name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="import_file" class="block text-sm font-medium text-gray-700">Pilih File (.xlsx atau
                            .csv)</label>
                        <input type="file" name="import_file" id="import_file" required
                            accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        <p class="text-xs text-gray-500 mt-2">Format file: Kolom A untuk Nama Lokasi, Kolom B untuk
                            Alamat. Baris pertama adalah header.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-4 mt-6 pt-4 border-t">
                    <button type="button" @click="showImportModal = false"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-teal-500 text-white rounded-md hover:bg-teal-600">Proses
                        Import</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function parkingLocationsComponent() {
        return {
            showCreateModal: false,
            showEditModal: false,
            showImportModal: false,
            editData: {},
            formAction: '',
            tomSelectEditInstance: null,
            initTomSelect() {
                new TomSelect('#coordinator_filter', {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
                new TomSelect('#create_coord', {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
                new TomSelect('#import_coord', {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
                this.tomSelectEditInstance = new TomSelect('#edit_coord', {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
            },
            handleEditClick(locationId) {
                fetch(`<?php echo BASE_URL ?>/parkinglocations/getParkingLocationJson/${locationId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        this.editData = data;
                        this.formAction = `<?php echo BASE_URL ?>/parkinglocations/update/${data.id}`;
                        if (this.tomSelectEditInstance) {
                            this.tomSelectEditInstance.setValue(data.field_coordinator_id, true);
                        }
                        this.showEditModal = true;
                    });
            },
            confirmDelete(formId) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data lokasi ini akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(formId).submit();
                    }
                })
            }
        };
    }
    document.addEventListener('alpine:init', () => {
        Alpine.data('parkingLocationsComponent', parkingLocationsComponent);
    });
</script>