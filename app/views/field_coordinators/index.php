<div x-data="coordinatorsComponent()" class="space-y-6">

    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Koordinator</h2>
            <p class="text-gray-500 mt-1">Kelola data koordinator, kontak, dan masa berlaku PKS.</p>
        </div>
        <div class="flex gap-3">
            <button @click="startExport()" type="button" class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 font-bold text-sm transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Export PDF
            </button>

            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <button @click="openCreateModal()" type="button" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold text-sm transition shadow-lg transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Baru
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form action="<?php echo BASE_URL?>/fieldcoordinators" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-grow">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchTerm ?? '')?>"
                       class="w-full pl-10 pr-4 py-2 border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Cari nama atau NIK koordinator...">
            </div>
            <div class="w-full md:w-48">
                <select name="zone" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
                    <option value="">Semua Zona</option>
                    <option value="Zona 1" <?php echo ($selectedZone == 'Zona 1') ? 'selected' : ''?>>Zona 1</option>
                    <option value="Zona 2" <?php echo ($selectedZone == 'Zona 2') ? 'selected' : ''?>>Zona 2</option>
                    <option value="Zona 3" <?php echo ($selectedZone == 'Zona 3') ? 'selected' : ''?>>Zona 3</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 font-medium">Filter</button>
            <?php if (! empty($searchTerm) || ! empty($selectedZone)): ?>
                <a href="<?php echo BASE_URL?>/fieldcoordinators" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium flex items-center justify-center">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-12">No</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama & Kontak</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status PKS</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($coordinators)): ?>
                        <tr><td colspan="5" class="text-center py-12 text-gray-500 italic">Tidak ada data koordinator untuk filter ini.</td></tr>
                    <?php else: ?>
                        <?php
                            $nomor = ($page - 1) * 15 + 1;
                            $today = date('Y-m-d');
                        ?>
                        <?php foreach ($coordinators as $coord): ?>
                            <?php
                                $pksText  = 'Belum Diisi';
                                $pksClass = 'bg-gray-100 text-gray-600';
                                if ($coord->pks_expired) {
                                    $expiry   = $coord->pks_expired;
                                    $daysLeft = (strtotime($expiry) - strtotime($today)) / (60 * 60 * 24);
                                    if ($daysLeft < 0) {
                                        $pksText  = 'Expired (' . date('d/m/y', strtotime($expiry)) . ')';
                                        $pksClass = 'bg-orange-100 text-orange-800 border border-orange-200';
                                    } elseif ($daysLeft <= 90) {
                                        $pksText  = 'Masa Tenggang (' . ceil($daysLeft) . ' hari)';
                                        $pksClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                    } else {
                                        $pksText  = 'Aktif s.d ' . date('d M Y', strtotime($expiry));
                                        $pksClass = 'bg-green-100 text-green-700 border border-green-200';
                                    }
                                }
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $nomor++?></td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($coord->name)?></div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        NIK: <?php echo htmlspecialchars($coord->nik ?? '-')?> | HP: <?php echo htmlspecialchars($coord->phone_number ?? '-')?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold <?php echo $pksClass?>"><?php echo $pksText?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold"><?php echo $coord->location_count?> Titik</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openDetailModal(<?php echo $coord->id?>)" class="text-blue-600 hover:text-blue-900 mr-3" title="Detail"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></button>

                                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <button @click="openEditModal(<?php echo $coord->id?>)" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                    <form id="delete-form-<?php echo $coord->id?>" action="<?php echo BASE_URL?>/fieldcoordinators/destroy/<?php echo $coord->id?>" method="POST" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token'] ?? ''?>">
                                        <button type="button" @click="confirmDelete(<?php echo $coord->id?>)" class="text-red-600 hover:text-red-900" title="Hapus"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center bg-gray-50">
            <span class="text-xs text-gray-500">Halaman <strong><?php echo $page?></strong> dari <strong><?php echo $total_pages?></strong></span>
            <div class="flex gap-1">
                <?php
                    $queryParams = [];
                    if ($searchTerm) {
                        $queryParams['q'] = $searchTerm;
                    }

                    if ($selectedZone) {
                        $queryParams['zone'] = $selectedZone;
                    }

                    $prevParams = array_merge($queryParams, ['page' => $page - 1]);
                    $nextParams = array_merge($queryParams, ['page' => $page + 1]);
                ?>
                <?php if ($page > 1): ?> <a href="?<?php echo http_build_query($prevParams)?>" class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-100">Prev</a><?php endif; ?>
                <?php if ($page < $total_pages): ?> <a href="?<?php echo http_build_query($nextParams)?>" class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-100">Next</a><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm">
        <div @click.away="showModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 relative">
            <h3 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2" x-text="isEdit ? 'Edit Koordinator' : 'Tambah Koordinator'"></h3>

            <form :action="formAction" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Koordinator</label>
                        <input type="text" name="name" x-model="formData.name" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">NIK</label>
                            <input type="text" name="nik" x-model="formData.nik" maxlength="16" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border-gray-300 rounded-lg shadow-sm" placeholder="16 Digit">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">No. HP</label>
                            <input type="text" name="phone_number" x-model="formData.phone_number" maxlength="13" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border-gray-300 rounded-lg shadow-sm" placeholder="08xxx">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Masa Berlaku PKS (Expired)</label>
                        <input type="date" name="pks_expired" x-model="formData.pks_expired" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-400 mt-1">Biarkan kosong jika belum ada PKS.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm">
        <div @click.away="showDetailModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-gray-100 bg-gray-50 rounded-t-2xl">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800" x-text="detailData.name"></h3>

                        <div class="mt-1 text-sm text-gray-600 grid grid-cols-2 gap-x-6">
                            <span><strong>NIK:</strong> <span x-text="detailData.nik || '-'"></span></span>
                            <span><strong>HP:</strong> <span x-text="detailData.phone_number || '-'"></span></span>
                        </div>

                        <div class="mt-3 flex items-center gap-3">
                            <span x-show="detailData.pks_expired" class="px-2 py-1 text-xs font-bold rounded" :class="getPksColor(detailData.pks_expired)">PKS Exp: <span x-text="formatDateID(detailData.pks_expired)"></span></span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded" x-text="'Total: ' + (detailData.locations ? detailData.locations.length : 0) + ' Lokasi'"></span>
                        </div>
                    </div>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Daftar Lokasi Parkir
                </h4>
                <div class="border rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-12">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Nama Lokasi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Alamat</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/4">Zona</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-if="!detailData.locations || detailData.locations.length === 0"><tr><td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500 italic">Belum ada lokasi terdaftar.</td></tr></template>
                            <template x-for="(loc, index) in detailData.locations" :key="index">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-500" x-text="index + 1"></td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900" x-text="loc.parking_location"></td>
                                    <td class="px-4 py-3 text-sm text-gray-500" x-text="loc.address"></td>
                                    <td class="px-4 py-3 text-sm"><span x-show="loc.zone" class="px-2 py-1 text-xs rounded-full font-semibold" :class="{'bg-purple-100 text-purple-800': loc.zone === 'Zona 1', 'bg-blue-100 text-blue-800': loc.zone === 'Zona 2', 'bg-green-100 text-green-800': loc.zone === 'Zona 3'}" x-text="loc.zone"></span><span x-show="!loc.zone" class="text-gray-400 italic">-</span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl text-right">
                <button @click="showDetailModal = false" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow-md">Tutup</button>
            </div>
        </div>
    </div>

    <div x-show="isLoading" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 text-center">
            <h3 class="text-xl font-bold text-gray-800 mb-2">Menyiapkan Laporan PDF</h3>
            <p class="text-gray-500 text-sm mb-4" x-text="statusMessage"></p>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden"><div class="bg-blue-600 h-4 rounded-full transition-all duration-300 ease-out" :style="`width: ${progress}%`"></div></div>
            <span class="text-xs font-bold" x-text="Math.round(progress) + '%'"></span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function coordinatorsComponent() {
    return {
        showModal: false, showDetailModal: false, isEdit: false,
        formData: { name: '', nik: '', phone_number: '', pks_expired: '' },
        detailData: {}, formAction: '', isLoading: false, progress: 0, statusMessage: '',

        openCreateModal() { this.isEdit = false; this.formData = { name: '', nik: '', phone_number: '', pks_expired: '' }; this.formAction = '<?php echo BASE_URL?>/fieldcoordinators/store'; this.showModal = true; },
        openEditModal(id) { this.isEdit = true; fetch(`<?php echo BASE_URL?>/fieldcoordinators/getCoordinatorJson/${id}`).then(res => res.json()).then(data => { this.formData = { name: data.name, nik: data.nik || '', phone_number: data.phone_number || '', pks_expired: data.pks_expired || '' }; this.formAction = `<?php echo BASE_URL?>/fieldcoordinators/update/${id}`; this.showModal = true; }); },
        openDetailModal(id) { fetch(`<?php echo BASE_URL?>/fieldcoordinators/getDetailJson/${id}`).then(res => res.json()).then(data => { this.detailData = data; this.showDetailModal = true; }); },
        confirmDelete(id) { Swal.fire({ title: 'Hapus Koordinator?', text: "Data lokasi terkait akan terhapus!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya' }).then((result) => { if (result.isConfirmed) { document.getElementById('delete-form-' + id).submit(); } }) },
        getPksColor(expiryDate) { if (!expiryDate) return 'bg-gray-100 text-gray-600'; const diffDays = Math.ceil((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24)); if (diffDays < 0) return 'bg-orange-100 text-orange-800'; if (diffDays <= 90) return 'bg-yellow-100 text-yellow-800'; return 'bg-green-100 text-green-800'; },
        startExport() { this.isLoading = true; this.progress = 0; this.statusMessage = 'Menghubungkan...'; let timer = setInterval(() => { if (this.progress < 90) this.progress += 1.5; }, 100); const params = new URLSearchParams(window.location.search); fetch(`<?php echo BASE_URL?>/fieldcoordinators/export_pdf?${params.toString()}`).then(res => res.blob()).then(blob => { clearInterval(timer); this.progress = 100; const url = window.URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `Data_Koordinator.pdf`; document.body.appendChild(a); a.click(); a.remove(); setTimeout(() => { this.isLoading = false; }, 1000); }); },
        formatDateID(dateString) { return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }); }
    }
}
document.addEventListener('alpine:init', () => { Alpine.data('coordinatorsComponent', coordinatorsComponent); });
</script>