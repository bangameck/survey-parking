<div x-data="backupComponent()" class="max-w-2xl mx-auto space-y-6">

    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Backup Database</h2>
        <p class="text-gray-500 mt-2">Amankan data sistem secara berkala.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 text-center relative overflow-hidden">

        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-green-500 rounded-full opacity-10 blur-xl"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-blue-500 rounded-full opacity-10 blur-xl"></div>

        <div class="mb-6 inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-600 mb-4">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4M4 7l8 4l8-4m-8 4v10"></path></svg>
        </div>

        <h3 class="text-xl font-bold text-gray-800 mb-2">Siap Melakukan Backup</h3>
        <p class="text-gray-600 mb-8 leading-relaxed">
            Sistem akan mengunduh file SQL dari database <strong><?php echo DB_NAME ?></strong>.<br>
            File ini berisi seluruh data Koordinator, Lokasi, dan Transaksi.
        </p>

        <button @click="startBackup()" type="button" class="w-full py-4 bg-gradient-to-r from-green-600 to-green-700 text-white font-bold rounded-xl shadow-lg hover:from-green-700 hover:to-green-800 transform hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span>Proses Backup & Download</span>
        </button>

        <div class="mt-8 p-4 bg-yellow-50 rounded-xl border border-yellow-200 text-left flex items-start gap-3">
            <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="text-sm text-yellow-800">
                <strong class="block mb-1 font-bold">Cara Restore Data:</strong>
                File <code>.sql</code> hasil backup ini dapat di-import kembali menggunakan <strong>phpMyAdmin</strong> atau <strong>HeidiSQL</strong> jika terjadi kendala pada server atau perpindahan hosting.
            </div>
        </div>
    </div>

    <div x-show="isLoading" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 border border-gray-200 text-center">

            <div class="mb-6 relative flex justify-center">
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center animate-pulse">
                    <svg class="w-10 h-10 text-green-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            <h3 class="text-xl font-bold text-gray-800 mb-2" x-text="statusTitle">Memproses Backup</h3>
            <p class="text-gray-500 text-sm mb-6" x-text="statusMessage">Menghubungkan ke database...</p>

            <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden">
                <div class="bg-green-600 h-4 rounded-full transition-all duration-300 ease-out relative"
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
function backupComponent() {
    return {
        isLoading: false,
        progress: 0,
        statusTitle: '',
        statusMessage: '',
        timer: null,
        csrfToken: '<?php echo $csrf_token ?>',

        startBackup() {
            this.isLoading = true;
            this.progress = 0;
            this.statusTitle = 'Backup Sedang Berjalan';
            this.statusMessage = 'Inisialisasi proses dump...';

            // 1. Simulasi Progress Bar Cerdas
            // Karena proses backup server-side tidak bisa dilacak real-time via AJAX sederhana,
            // kita gunakan estimasi waktu.
            this.timer = setInterval(() => {
                if (this.progress < 30) {
                    this.progress += 3;
                    this.statusMessage = 'Membaca tabel database...';
                } else if (this.progress < 60) {
                    this.progress += 1;
                    this.statusMessage = 'Menulis struktur & data...';
                } else if (this.progress < 85) {
                    this.progress += 0.5;
                    this.statusMessage = 'Kompresi & Finalisasi...';
                } else if (this.progress < 95) {
                    this.progress += 0.1; // Tunggu server selesai
                    this.statusMessage = 'Menunggu respon server...';
                }
            }, 100);

            // 2. Kirim Request AJAX ke Controller
            const formData = new FormData();
            formData.append('csrf_token', this.csrfToken);

            fetch('<?php echo BASE_URL ?>/backup/create', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    // Jika error, coba baca pesan errornya
                    return response.text().then(text => { throw new Error(text || 'Terjadi kesalahan server') });
                }
                // Ambil nama file dari header Content-Disposition jika ada, atau default
                const disposition = response.headers.get('Content-Disposition');
                let filename = `Backup_${new Date().toISOString().slice(0,10)}.sql`;
                if (disposition && disposition.indexOf('attachment') !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }

                return response.blob().then(blob => ({ blob, filename }));
            })
            .then(({ blob, filename }) => {
                // 3. Sukses! Download File
                clearInterval(this.timer);
                this.progress = 100;
                this.statusTitle = 'Backup Selesai!';
                this.statusMessage = 'Mengunduh file ke perangkat Anda...';

                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);

                // Tutup Modal setelah selesai
                setTimeout(() => {
                    this.isLoading = false;
                    this.progress = 0;
                    // Optional: Tampilkan notifikasi sukses kecil
                    toastr.success('Database berhasil di-backup!');
                }, 1500);
            })
            .catch(error => {
                console.error('Backup Error:', error);
                clearInterval(this.timer);
                this.isLoading = false;

                // Tampilkan pesan error yang user-friendly
                Swal.fire({
                    icon: 'error',
                    title: 'Backup Gagal',
                    text: error.message,
                    confirmButtonColor: '#d33'
                });
            });
        }
    }
}
document.addEventListener('alpine:init', () => {
    Alpine.data('backupComponent', backupComponent);
});
</script>