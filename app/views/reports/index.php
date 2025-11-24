<div x-data="reportsComponent()" class="space-y-6">

    <div class="flex flex-col md:flex-row justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Pusat Laporan & Export Data</h2>
            <p class="text-gray-500 mt-1">Unduh rekapitulasi data survey lengkap dalam format PDF atau Excel.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
        <h3 class="font-semibold text-lg text-gray-800 mb-4 border-b pb-2">Filter Data Export</h3>

        <form id="exportForm" onsubmit="return false;">

            <div class="max-w-md mb-6">
                <label for="report_coordinator" class="block text-sm font-medium text-gray-700 mb-2">Pilih Koordinator (Opsional)</label>

                <select id="report_coordinator" name="coordinator_id" placeholder="Cari nama koordinator..." autocomplete="off">
                    <option value="">-- Tampilkan Semua Data --</option>
                    <?php foreach ($coordinators as $coord): ?>
                        <option value="<?php echo $coord->id ?>"><?php echo htmlspecialchars($coord->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">Biarkan kosong untuk meng-export data dari seluruh koordinator.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <button type="button" @click="startExport('excel')" class="group relative bg-white rounded-xl border-2 border-green-100 hover:border-green-500 p-6 transition-all duration-300 hover:shadow-lg text-left w-full">
                    <div class="flex items-center gap-4">
                        <div class="p-4 bg-green-100 text-green-600 rounded-xl group-hover:bg-green-600 group-hover:text-white transition-colors">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800 group-hover:text-green-600">Export ke Excel (.xlsx)</h4>
                            <p class="text-sm text-gray-500 mt-1">Format spreadsheet lengkap dengan grouping.</p>
                        </div>
                    </div>
                </button>

                <button type="button" @click="startExport('pdf')" class="group relative bg-white rounded-xl border-2 border-red-100 hover:border-red-500 p-6 transition-all duration-300 hover:shadow-lg text-left w-full">
                    <div class="flex items-center gap-4">
                        <div class="p-4 bg-red-100 text-red-600 rounded-xl group-hover:bg-red-600 group-hover:text-white transition-colors">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800 group-hover:text-red-600">Export Laporan PDF</h4>
                            <p class="text-sm text-gray-500 mt-1">Format dokumen siap cetak (F4 Landscape).</p>
                        </div>
                    </div>
                </button>

            </div>
        </form>
    </div>

    <div x-show="isLoading" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 border border-gray-200 text-center">

            <div class="mb-6 relative flex justify-center">
                <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center animate-pulse">
                    <svg class="w-10 h-10 text-blue-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            <h3 class="text-xl font-bold text-gray-800 mb-2" x-text="statusTitle">Sedang Memproses...</h3>
            <p class="text-gray-500 text-sm mb-6" x-text="statusMessage">Mohon tunggu sebentar, sedang menyiapkan data.</p>

            <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden">
                <div class="bg-blue-600 h-4 rounded-full transition-all duration-300 ease-out relative"
                     :style="`width: ${progress}%`">
                     <div class="absolute top-0 left-0 bottom-0 right-0 bg-white opacity-20 w-full animate-pulse"></div>
                </div>
            </div>

            <div class="flex justify-between text-xs font-semibold text-gray-600">
                <span>0%</span>
                <span x-text="Math.round(progress) + '%'"></span>
                <span>100%</span>
            </div>
        </div>
    </div>

</div>

<script>
    function reportsComponent() {
        return {
            isLoading: false,
            progress: 0,
            statusTitle: '',
            statusMessage: '',
            tomSelectInstance: null,
            timer: null,

            init() {
                // Inisialisasi TomSelect
                this.tomSelectInstance = new TomSelect('#report_coordinator', {
                    create: false,
                    sortField: { field: "text", direction: "asc" }
                });
            },

            startExport(type) {
                this.isLoading = true;
                this.progress = 0;
                this.statusTitle = type === 'excel' ? 'Menyiapkan Spreadsheet' : 'Menyusun Dokumen PDF';
                this.statusMessage = 'Menghubungkan ke server...';

                // Ambil nilai dari TomSelect
                const coordinatorId = this.tomSelectInstance.getValue();

                // SIMULASI PROGRESS BAR CERDAS
                // Bar akan jalan cepat di awal, lalu melambat saat mendekati 90%
                // sambil menunggu respon server.
                this.timer = setInterval(() => {
                    if (this.progress < 30) {
                        this.progress += 2; // Cepat
                        this.statusMessage = 'Mengambil data database...';
                    } else if (this.progress < 60) {
                        this.progress += 1; // Sedang
                        this.statusMessage = 'Melakukan kalkulasi & grouping...';
                    } else if (this.progress < 85) {
                        this.progress += 0.5; // Lambat
                        this.statusMessage = 'Menyusun format laporan...';
                    } else if (this.progress < 95) {
                        this.progress += 0.1; // Sangat lambat (menunggu server)
                        this.statusMessage = 'Finalisasi dokumen...';
                    }
                }, 100);

                // Siapkan Data Form
                const formData = new FormData();
                formData.append('export_type', type);
                if(coordinatorId) formData.append('coordinator_id', coordinatorId);

                // REQUEST AJAX (FETCH) KE SERVER
                fetch('<?php echo BASE_URL ?>/reports/process', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Terjadi kesalahan jaringan');
                    // Ambil nama file dari header jika ada, atau buat default
                    return response.blob();
                })
                .then(blob => {
                    // SUKSES! Server sudah mengirim file
                    clearInterval(this.timer);
                    this.progress = 100;
                    this.statusTitle = 'Selesai!';
                    this.statusMessage = 'File siap diunduh.';

                    // Download file secara otomatis
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    // Tentukan nama file
                    const ext = type === 'excel' ? 'xlsx' : 'pdf';
                    const date = new Date().toISOString().slice(0, 10);
                    a.download = `Laporan_Survey_Parkir_${date}.${ext}`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                    // Tutup modal setelah jeda singkat
                    setTimeout(() => {
                        this.isLoading = false;
                        this.progress = 0;
                    }, 1500);
                })
                .catch(error => {
                    console.error('Error:', error);
                    clearInterval(this.timer);
                    this.isLoading = false;
                    alert('Gagal melakukan export. Silakan coba lagi.');
                });
            }
        };
    }

    // Inisialisasi Alpine Component
    document.addEventListener('alpine:init', () => {
        Alpine.data('reportsComponent', reportsComponent);
    });
</script>