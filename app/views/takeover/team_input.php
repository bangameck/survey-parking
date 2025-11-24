<div x-data="teamInputComponent()" x-init="init()" class="space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Input Setoran Pengambilalihan</h2>
        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold shadow-sm">
            Tim:                 <?php echo htmlspecialchars($_SESSION['user_team'] ?? '-') ?>
        </span>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- 1. Pilih Koordinator (DENGAN DATA TANGGAL MULAI) -->
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Koordinator (PKS)</label>
                <select id="select-coordinator" placeholder="Pilih koordinator...">
                    <option value="">-- Pilih Koordinator --</option>
                    <?php foreach ($assigned_coordinators as $ac): ?>
                        <!-- Value: id_takeover|id_coord -->
                        <!-- Data Start: Tanggal mulai takeover -->
                        <option value="<?php echo $ac->id ?>|<?php echo $ac->field_coordinator_id ?>"
                                data-start="<?php echo $ac->start_date ?>">
                            <?php echo htmlspecialchars($ac->coordinator_name) ?> (Mulai:<?php echo date('d/m/Y', strtotime($ac->start_date)) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 2. Pilih Tanggal (MIN DATE DINAMIS) -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Setoran</label>
                <input type="date"
                       x-model="selectedDate"
                       :min="minDate"
                       @change="fetchLocations"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
                       :class="{'bg-gray-100 cursor-not-allowed': !minDate}"
                       :disabled="!minDate">
                <p x-show="!minDate" class="text-xs text-gray-400 mt-1">Pilih koordinator dulu.</p>
            </div>
        </div>
    </div>

    <!-- Tabel Input Data -->
    <div x-show="locations.length > 0" x-transition class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">

        <form action="<?php echo BASE_URL ?>/teaminput/store" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
            <input type="hidden" name="date" :value="selectedDate">
            <input type="hidden" name="takeover_id" :value="takeoverId">

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider w-1/3">Titik Lokasi</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider w-1/5">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider w-1/4">Jumlah Setoran</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider w-1/4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="loc in locations" :key="loc.id">

                            <!-- WARNA BARIS DINAMIS (Tergantung loc.status di JS) -->
                            <tr :class="{
                                'bg-green-50': loc.status === 'harian',
                                'bg-yellow-50': loc.status === 'weekend',
                                'bg-orange-50': loc.status === 'bulanan',
                                'opacity-60 bg-gray-100 pointer-events-none': loc.disabled
                            }" class="transition-colors">

                                <!-- 1. NAMA LOKASI -->
                                <td class="px-6 py-4 align-top">
                                    <div class="font-bold text-gray-900 text-sm" x-text="loc.name"></div>
                                    <div class="text-xs text-gray-500" x-text="loc.address"></div>

                                    <!-- Label Hasil Survey / History -->
                                    <template x-if="loc.survey_amount_fmt">
                                        <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded bg-white border border-gray-200 shadow-sm">
                                            <span class="text-[10px] font-bold text-gray-500 uppercase mr-1" x-text="loc.survey_label + ':'"></span>
                                            <span class="text-[11px] font-bold text-blue-600" x-text="'Rp ' + loc.survey_amount_fmt"></span>
                                        </div>
                                    </template>
                                </td>

                                <!-- 2. STATUS (SELECT vs LOCKED) -->
                                <td class="px-6 py-4 text-center align-top pt-6">
                                    <!-- JIKA TERKUNCI (Ada Survey / History / Disabled) -->
                                    <template x-if="loc.is_locked || loc.disabled">
                                        <div>
                                            <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase shadow-sm border block w-full text-center"
                                                :class="{
                                                    'bg-green-200 text-green-800 border-green-300': loc.status === 'harian',
                                                    'bg-yellow-200 text-yellow-800 border-yellow-300': loc.status === 'weekend',
                                                    'bg-orange-200 text-orange-800 border-orange-300': loc.status === 'bulanan'
                                                }"
                                                x-text="loc.disabled && loc.status === 'bulanan' ? 'SUDAH LUNAS' : loc.status"></span>
                                            <input type="hidden" :name="`deposits[${loc.id}][status]`" :value="loc.status">
                                        </div>
                                    </template>

                                    <!-- JIKA BEBAS (Belum pernah input & Tidak ada survey) -->
                                    <template x-if="!loc.is_locked && !loc.disabled">
                                        <select :name="`deposits[${loc.id}][status]`"
                                                x-model="loc.status"
                                                class="block w-full py-1.5 px-2 text-xs border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm uppercase font-bold cursor-pointer bg-white">
                                            <option value="harian">Harian</option>
                                            <option value="weekend">Weekend</option>
                                            <option value="bulanan">Bulanan</option>
                                        </select>
                                    </template>
                                </td>

                                <!-- 3. INPUT RUPIAH (FLOAT LABEL) -->
                                <td class="px-6 py-4 align-top">
                                    <div class="relative mt-1">
                                        <input type="text"
                                               :id="`amount_${loc.id}`"
                                               :name="`deposits[${loc.id}][amount]`"
                                               x-model="loc.amount"
                                               @input="formatRupiahInput($event, loc)"
                                               :disabled="loc.disabled"
                                               class="block px-3 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-white rounded-lg border-1 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer text-right font-mono font-bold"
                                               :class="{'border-red-400 bg-red-50': !loc.disabled && (!loc.amount || loc.amount == 0)}"
                                               placeholder=" " />
                                        <label :for="`amount_${loc.id}`"
                                               class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-transparent px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                                            Rupiah (Rp)
                                        </label>
                                    </div>
                                </td>

                                <!-- 4. KETERANGAN -->
                                <td class="px-6 py-4 align-top pt-5">
                                    <input type="text"
                                           :name="`deposits[${loc.id}][notes]`"
                                           x-model="loc.notes"
                                           :disabled="loc.disabled"
                                           class="w-full border-0 border-b border-gray-300 focus:border-blue-500 focus:ring-0 bg-transparent text-sm py-1"
                                           placeholder="Catatan..."
                                    >
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t bg-gray-50 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl shadow-lg hover:from-blue-700 hover:to-blue-800 transform hover:scale-105 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Data
                </button>
            </div>
        </form>
    </div>

    <!-- Pesan Kosong -->
    <div x-show="locations.length === 0 && hasSelected" x-cloak class="text-center py-12 bg-white rounded-2xl border-2 border-dashed border-gray-300">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-gray-500 font-medium">Tidak ada data lokasi untuk koordinator ini.</p>
    </div>

</div>

<script>
function teamInputComponent() {
    return {
        selectedDate: new Date().toISOString().slice(0, 10),
        minDate: '',
        takeoverId: '',
        coordId: '',
        locations: [],
        hasSelected: false,
        tomSelect: null,

        init() {
            this.tomSelect = new TomSelect('#select-coordinator', {
                create: false,
                sortField: { field: "text", direction: "asc" },
                onChange: (value) => {
                    if(value) {
                        const parts = value.split('|');
                        this.takeoverId = parts[0];
                        this.coordId = parts[1];
                        this.hasSelected = true;

                        // --- PERBAIKAN LOGIKA KUNCI KALENDER ---
                        // Kita ambil atribut data-start langsung dari elemen <option> di select ASLI
                        // Bukan dari objek TomSelect
                        const originalSelect = document.getElementById('select-coordinator');
                        const originalOption = originalSelect.querySelector(`option[value="${value}"]`);

                        if (originalOption) {
                            const startDate = originalOption.getAttribute('data-start');
                            this.minDate = startDate;

                            console.log("Tanggal Mulai Takeover:", this.minDate); // Debugging

                            // Jika tanggal terpilih sekarang LEBIH KECIL dari minDate, paksa maju ke minDate
                            if (this.selectedDate < this.minDate) {
                                this.selectedDate = this.minDate;
                                alert(`Tanggal disesuaikan! Pengambilalihan baru dimulai tanggal ${this.formatDateID(this.minDate)}.`);
                            }
                        }

                        this.fetchLocations();
                    } else {
                        this.locations = [];
                        this.hasSelected = false;
                        this.minDate = '';
                    }
                }
            });
        },

        fetchLocations() {
            if (!this.takeoverId || !this.coordId || !this.selectedDate) return;

            // Kosongkan dulu untuk efek loading
            this.locations = [];

            fetch(`<?php echo BASE_URL?>/teaminput/getLocationsJson?takeover_id=${this.takeoverId}&coord_id=${this.coordId}&date=${this.selectedDate}`)
                .then(res => res.json())
                .then(data => {
                    this.locations = data;
                })
                .catch(err => {
                    console.error("Gagal mengambil data lokasi:", err);
                });
        },

        formatRupiahInput(e, loc) {
            let val = e.target.value.replace(/[^0-9]/g, '');
            if (val === '') {
                loc.amount = '';
                return;
            }
            let number_string = val.toString(),
                sisa = number_string.length % 3,
                rupiah = number_string.substr(0, sisa),
                ribuan = number_string.substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            loc.amount = rupiah;
        },

        // Helper untuk memformat tanggal ke Indonesia (opsional, untuk alert)
        formatDateID(dateString) {
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        }
    }
}
document.addEventListener('alpine:init', () => {
    Alpine.data('teamInputComponent', teamInputComponent);
});
</script>