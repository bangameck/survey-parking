<div x-data="parkingLocationsComponent()" x-init="initTomSelect()" class="space-y-6">

    <div class="bg-white rounded-lg shadow-md p-6 print:hidden">
        <form action="<?php echo BASE_URL ?>/parkinglocations" method="GET">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Filter & Pencarian</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="search_term" class="block text-sm font-medium text-gray-700 mb-1">Cari Nama Lokasi</label>
                    <input type="text" name="q" id="search_term"
                        value="<?php echo htmlspecialchars($searchTerm ?? '') ?>" placeholder="Ketik nama lokasi..."
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="coordinator_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter Koordinator</label>
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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span>Cari</span>
                </button>
                <a href="<?php echo BASE_URL ?>/parkinglocations"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-center">Reset</a>
            </div>
        </form>
    </div>

    <form id="bulk-form" action="<?php echo BASE_URL ?>/parkinglocations/destroyBatch" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
        <input type="hidden" name="q_hidden" value="<?php echo htmlspecialchars($searchTerm ?? '') ?>">
        <input type="hidden" name="coordinator_id_hidden" value="<?php echo htmlspecialchars($selected_coordinator ?? '') ?>">
        <input type="hidden" name="bulk_zone" id="hidden_bulk_zone">

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center p-6 gap-4 print:hidden">
                <h3 class="font-semibold text-lg text-gray-800">Daftar Lokasi Parkir</h3>

                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <div class="flex flex-wrap gap-3 justify-end items-center w-full lg:w-auto">

                    <div class="flex items-center gap-2 bg-gray-100 p-1.5 rounded-lg border border-gray-200">
                        <select id="bulk_zone_select" class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pl-2 pr-8">
                            <option value="">-- Pilih Zona --</option>
                            <option value="Zona 1">Zona 1</option>
                            <option value="Zona 2">Zona 2</option>
                            <option value="Zona 3">Zona 3</option>
                        </select>
                        <button @click="confirmBulkEdit($event)" type="button"
                            class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Ubah
                        </button>
                    </div>

                    <div class="h-8 w-px bg-gray-300 hidden lg:block"></div>

                    <button @click="showImportModal = true" type="button"
                        class="px-4 py-2 bg-teal-500 text-white rounded-md hover:bg-teal-600 transition-colors flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Import
                    </button>

                    <button @click="showCreateModal = true" type="button"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors text-sm">
                        + Tambah
                    </button>

                    <button @click="confirmBulkDelete($event)" type="button"
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <th class="px-6 py-3 text-left w-10">
                                <input type="checkbox" @click="toggleSelectAll($event)"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </th>
                            <?php endif; ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zona</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Koordinator</th>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($locations)): ?>
                        <tr>
                            <td colspan="<?php echo($_SESSION['user_role'] === 'admin') ? '8' : '6' ?>" class="text-center py-10 text-gray-500">
                                Tidak ada data yang ditemukan.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php $nomor = ($page - 1) * 15 + 1; ?>
                        <?php foreach ($locations as $loc): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="location_ids[]" value="<?php echo $loc->id ?>"
                                    class="location-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $nomor++ ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($loc->parking_location) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($loc->address) ?></td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($loc->zone): ?>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php
                                            if ($loc->zone == 'Zona 1') {
                                                echo 'bg-purple-100 text-purple-800';
                                            } elseif ($loc->zone == 'Zona 2') {
                                                echo 'bg-blue-100 text-blue-800';
                                            } else {
                                                echo 'bg-green-100 text-green-800';
                                            }

                                        ?>">
                                        <?php echo htmlspecialchars($loc->zone) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs italic">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($loc->coordinator_name) ?></td>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click="handleEditClick(<?php echo $loc->id ?>)" type="button"
                                    class="text-indigo-600 hover:text-indigo-900 mr-4 font-bold">Edit</button>
                                <button type="button" onclick="confirmSingleDelete(<?php echo $loc->id ?>)" class="text-red-600 hover:text-red-900 font-bold">
                                    Hapus
                                </button>
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
                            <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-blue-50">&laquo; Prev</a></li>
                            <?php endif; ?>
                            <?php if ($page < $total_pages): ?>
                            <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-blue-50">Next &raquo;</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <form id="single-delete-form" method="POST" style="display:none;">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
    </form>

    <div x-show="showCreateModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40">
        <div @click.away="showCreateModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 mx-4">
            <h3 class="text-xl font-semibold border-b pb-3 mb-4">Tambah Lokasi Baru</h3>
            <form action="<?php echo BASE_URL ?>/parkinglocations/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Koordinator</label>
                        <select name="field_coordinator_id" id="create_coord" required class="mt-1 block w-full">
                            <option value="">Pilih Koordinator...</option>
                            <?php foreach ($coordinators as $coord): ?>
                            <option value="<?php echo $coord->id ?>"><?php echo htmlspecialchars($coord->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lokasi</label>
                        <input type="text" name="parking_location" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Zona Wilayah</label>
                        <select name="zone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Pilih Zona --</option>
                            <option value="Zona 1">Zona 1</option>
                            <option value="Zona 2">Zona 2</option>
                            <option value="Zona 3">Zona 3</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea name="address" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-4 mt-6 pt-4 border-t">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showEditModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40">
        <div @click.away="showEditModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 mx-4">
            <h3 class="text-xl font-semibold border-b pb-3 mb-4">Edit Lokasi</h3>
            <form :action="formAction" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Koordinator</label>
                        <select name="field_coordinator_id" id="edit_coord" required class="mt-1 block w-full">
                            <option value="">Pilih Koordinator...</option>
                            <?php foreach ($coordinators as $coord): ?>
                            <option value="<?php echo $coord->id ?>"><?php echo htmlspecialchars($coord->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lokasi</label>
                        <input type="text" name="parking_location" :value="editData.parking_location" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Zona Wilayah</label>
                        <select name="zone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" :value="editData.zone">
                            <option value="">-- Pilih Zona --</option>
                            <option value="Zona 1">Zona 1</option>
                            <option value="Zona 2">Zona 2</option>
                            <option value="Zona 3">Zona 3</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea name="address" rows="3" x-text="editData.address" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-4 mt-6 pt-4 border-t">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showImportModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40">
        <div @click.away="showImportModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 mx-4">
            <h3 class="text-xl font-semibold border-b pb-3 mb-4">Import Lokasi dari File</h3>

            <form action="<?php echo BASE_URL ?>/parkinglocations/import" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">

                <div class="space-y-4">
                    <div>
                        <label for="import_coord" class="block text-sm font-medium text-gray-700">Pilih Koordinator (Wajib)</label>
                        <select name="field_coordinator_id" id="import_coord" required class="mt-1 block w-full">
                            <option value="">Pilih Koordinator...</option>
                            <?php foreach ($coordinators as $coord): ?>
                            <option value="<?php echo $coord->id ?>"><?php echo htmlspecialchars($coord->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Set Zona Default (Opsional)</label>
                        <select name="default_zone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Pilih Zona --</option>
                            <option value="Zona 1">Zona 1</option>
                            <option value="Zona 2">Zona 2</option>
                            <option value="Zona 3">Zona 3</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Akan diterapkan ke semua data jika kolom 'Zona' di Excel kosong.</p>
                    </div>

                    <div>
                        <label for="import_file" class="block text-sm font-medium text-gray-700">Pilih File (.xlsx / .csv)</label>
                        <input type="file" name="import_file" id="import_file" required
                            accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100" />
                        <p class="text-xs text-gray-500 mt-2">Format: Kolom A=Nama Lokasi, Kolom B=Alamat, Kolom C=Zona (Opsional).</p>
                    </div>
                </div>

                <div class="flex justify-end gap-4 mt-6 pt-4 border-t">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-teal-500 text-white rounded-md hover:bg-teal-600">Proses Import</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function confirmSingleDelete(id) {
        Swal.fire({
            title: 'Hapus Lokasi?',
            text: "Data tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('single-delete-form');
                form.action = `<?php echo BASE_URL ?>/parkinglocations/destroy/${id}`;
                form.submit();
            }
        })
    }

    function parkingLocationsComponent() {
        return {
            showCreateModal: false,
            showEditModal: false,
            showImportModal: false, // Fitur Import Hidup Lagi
            editData: {},
            formAction: '',
            tomSelectEditInstance: null,

            initTomSelect() {
                new TomSelect('#coordinator_filter', { create: false, sortField: { field: "text", direction: "asc" } });
                new TomSelect('#create_coord', { create: false, sortField: { field: "text", direction: "asc" } });
                new TomSelect('#import_coord', { create: false, sortField: { field: "text", direction: "asc" } }); // TomSelect untuk Import
                this.tomSelectEditInstance = new TomSelect('#edit_coord', { create: false, sortField: { field: "text", direction: "asc" } });
            },

            handleEditClick(locationId) {
                fetch(`<?php echo BASE_URL ?>/parkinglocations/getParkingLocationJson/${locationId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) { alert(data.error); return; }
                        this.editData = data;
                        this.formAction = `<?php echo BASE_URL ?>/parkinglocations/update/${data.id}`;
                        if (this.tomSelectEditInstance) {
                            this.tomSelectEditInstance.setValue(data.field_coordinator_id, true);
                        }
                        this.showEditModal = true;
                    });
            },

            toggleSelectAll(event) {
                const checkboxes = document.querySelectorAll('.location-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = event.target.checked;
                });
            },

            confirmBulkDelete(event) {
                const checkedBoxes = document.querySelectorAll('.location-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    Swal.fire({ title: 'Pilih Data!', text: 'Centang lokasi yang ingin dihapus.', icon: 'info' });
                    return;
                }
                Swal.fire({
                    title: 'Hapus Massal?',
                    text: `Anda akan menghapus ${checkedBoxes.length} lokasi!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus Semua'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('bulk-form');
                        form.action = "<?php echo BASE_URL ?>/parkinglocations/destroyBatch";
                        form.submit();
                    }
                })
            },

            confirmBulkEdit(event) {
                const checkedBoxes = document.querySelectorAll('.location-checkbox:checked');
                const zoneSelect = document.getElementById('bulk_zone_select');
                const selectedZone = zoneSelect.value;

                if (checkedBoxes.length === 0) {
                    Swal.fire({ title: 'Pilih Data!', text: 'Centang lokasi yang ingin diedit.', icon: 'info' });
                    return;
                }
                if (!selectedZone) {
                    Swal.fire({ title: 'Pilih Zona!', text: 'Pilih zona tujuan terlebih dahulu.', icon: 'info' });
                    return;
                }

                Swal.fire({
                    title: 'Update Zona Massal?',
                    text: `Ubah ${checkedBoxes.length} lokasi menjadi ${selectedZone}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4F46E5',
                    confirmButtonText: 'Ya, Update'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('hidden_bulk_zone').value = selectedZone;
                        const form = document.getElementById('bulk-form');
                        form.action = "<?php echo BASE_URL ?>/parkinglocations/updateBatch";
                        form.submit();
                    }
                })
            }
        };
    }
    document.addEventListener('alpine:init', () => {
        Alpine.data('parkingLocationsComponent', parkingLocationsComponent);
    });
</script>