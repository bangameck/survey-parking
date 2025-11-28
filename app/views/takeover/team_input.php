<div x-data="teamInputComponent()" x-init="init()" class="space-y-6">

    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Input Setoran Tim</h2>
        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold shadow-sm">
            Tim: <?php echo htmlspecialchars($_SESSION['user_team'] ?? '-')?>
        </span>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Koordinator (PKS)</label>
                <select id="select-coordinator" placeholder="Pilih koordinator...">
                    <option value="">-- Pilih Koordinator --</option>
                    <?php foreach ($assigned_coordinators as $ac): ?>
                        <option value="<?php echo $ac->id?>|<?php echo $ac->field_coordinator_id?>"
                                data-start="<?php echo $ac->start_date?>">
                            <?php echo htmlspecialchars($ac->coordinator_name)?> (Mulai: <?php echo date('d/m/Y', strtotime($ac->start_date))?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Setoran</label>
                <input type="date" x-model="selectedDate" :min="minDate" @change="fetchLocations"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5"
                       :class="{'bg-gray-100 cursor-not-allowed': !minDate}" :disabled="!minDate">
                <p x-show="!minDate" class="text-xs text-gray-400 mt-1">Pilih koordinator dulu.</p>
            </div>
        </div>
    </div>

    <form action="<?php echo BASE_URL?>/teaminput/store" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
        <input type="hidden" name="date" :value="selectedDate">
        <input type="hidden" name="takeover_id" :value="takeoverId">

        <div x-show="locations.length > 0" x-transition class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 mb-8">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"></path></svg>
                    Pemasukan (Setoran Parkir)
                </h3>
            </div>

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
                            <tr :class="{ 'bg-green-50': loc.status === 'harian', 'bg-yellow-50': loc.status === 'weekend', 'bg-orange-50': loc.status === 'bulanan', 'opacity-60 bg-gray-100 pointer-events-none': loc.disabled }" class="transition-colors">
                                <td class="px-6 py-4 align-top">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-gray-900 text-sm" x-text="loc.name"></span>
                                        <template x-if="loc.zone">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border bg-blue-50 text-blue-700 border-blue-200" x-text="loc.zone"></span>
                                        </template>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1" x-text="loc.address"></div>
                                    <template x-if="loc.survey_amount_fmt">
                                        <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded bg-white border border-gray-200 shadow-sm">
                                            <span class="text-[10px] font-bold text-gray-500 uppercase mr-1" x-text="loc.survey_label + ':'"></span>
                                            <span class="text-[11px] font-bold text-blue-600" x-text="'Rp ' + loc.survey_amount_fmt"></span>
                                        </div>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-center align-top pt-6">
                                    <template x-if="loc.is_locked || loc.disabled">
                                        <div>
                                            <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase shadow-sm border block w-full text-center"
                                                :class="{ 'bg-green-200 text-green-800 border-green-300': loc.status === 'harian', 'bg-yellow-200 text-yellow-800 border-yellow-300': loc.status === 'weekend', 'bg-orange-200 text-orange-800 border-orange-300': loc.status === 'bulanan' }"
                                                x-text="loc.disabled && loc.status === 'bulanan' ? 'SUDAH LUNAS' : loc.status"></span>
                                            <input type="hidden" :name="`deposits[${loc.id}][status]`" :value="loc.status">
                                        </div>
                                    </template>
                                    <template x-if="!loc.is_locked && !loc.disabled">
                                        <select :name="`deposits[${loc.id}][status]`" x-model="loc.status" class="block w-full py-1.5 px-2 text-xs border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm uppercase font-bold cursor-pointer bg-white">
                                            <option value="harian">Harian</option><option value="weekend">Weekend</option><option value="bulanan">Bulanan</option>
                                        </select>
                                    </template>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="relative mt-1">
                                        <span class="absolute left-3 top-2.5 text-gray-500 font-semibold">Rp</span>
                                        <input type="text" :name="`deposits[${loc.id}][amount]`" x-model="loc.amount" @input="formatRupiahInput($event, loc)" :disabled="loc.disabled"
                                               class="block px-3 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-white rounded-lg border-1 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer text-right font-mono font-bold"
                                               :class="{'border-red-400 bg-red-50': !loc.disabled && (!loc.amount || loc.amount == 0)}" placeholder=" " />
                                        <label class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-transparent px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">Rupiah</label>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top pt-5">
                                    <input type="text" :name="`deposits[${loc.id}][notes]`" x-model="loc.notes" :disabled="loc.disabled" class="w-full border-0 border-b border-gray-300 focus:border-blue-500 focus:ring-0 bg-transparent text-sm py-1" placeholder="Catatan...">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="locations.length > 0" x-transition class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 mb-8">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Pengeluaran Operasional
                </h3>
                <button type="button" @click="addExpense()" class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-bold hover:bg-blue-200 transition">+ Tambah Baris</button>
            </div>

            <div class="p-6 space-y-4">
                <template x-for="(exp, index) in expenses" :key="index">
                    <div class="flex flex-col md:flex-row gap-4 items-start border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                        <div class="flex-grow w-full">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Keterangan Pengeluaran</label>
                            <input type="text" :name="`expenses[${index}][description]`" x-model="exp.description" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500" placeholder="Contoh: Bensin, Makan Siang...">
                        </div>
                        <div class="w-full md:w-1/3">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Biaya (Rp)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500 font-bold">Rp</span>
                                <input type="text" :name="`expenses[${index}][amount]`" x-model="exp.amount" @input="formatRupiahExpense($event, exp)" class="pl-10 w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 font-mono font-bold text-right" placeholder="0">
                            </div>
                        </div>
                        <div class="pt-6">
                            <button type="button" @click="removeExpense(index)" class="text-red-500 hover:text-red-700" title="Hapus Baris">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="expenses.length === 0" class="text-center text-gray-400 text-sm italic">Belum ada pengeluaran yang diinput.</div>
            </div>
        </div>

        <div x-show="locations.length > 0" class="bg-gray-800 text-white rounded-2xl shadow-2xl p-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-left w-full md:w-auto">
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                    <span class="text-gray-400">Total Pemasukan:</span>
                    <span class="text-green-400 font-bold text-right" x-text="formatCurrency(totalIncome)"></span>

                    <span class="text-gray-400">Total Pengeluaran:</span>
                    <span class="text-red-400 font-bold text-right" x-text="formatCurrency(totalExpense)"></span>

                    <div class="col-span-2 border-t border-gray-600 my-1"></div>

                    <span class="text-gray-200 font-bold text-lg">Total Setor Bersih:</span>
                    <span class="text-white font-bold text-lg text-right" x-text="formatCurrency(netTotal)"></span>
                </div>
            </div>

            <button type="submit" class="w-full md:w-auto px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-500 text-white font-bold rounded-xl shadow-lg hover:from-blue-500 hover:to-blue-400 transform hover:scale-105 transition-all flex items-center justify-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                SIMPAN LAPORAN HARIAN
            </button>
        </div>

    </form>

    <div x-show="locations.length === 0 && hasSelected" x-cloak class="text-center py-12 bg-white rounded-2xl border-2 border-dashed border-gray-300">
        <p class="text-gray-500 font-medium">Tidak ada data lokasi untuk koordinator ini.</p>
    </div>

</div>

<script>
function teamInputComponent() {
    return {
        selectedDate: new Date().toISOString().slice(0, 10),
        minDate: '', takeoverId: '', coordId: '', locations: [], expenses: [],
        hasSelected: false, tomSelect: null,

        init() {
            this.tomSelect = new TomSelect('#select-coordinator', {
                create: false, sortField: { field: "text", direction: "asc" },
                onChange: (value) => {
                    if(value) {
                        const parts = value.split('|');
                        this.takeoverId = parts[0]; this.coordId = parts[1]; this.hasSelected = true;
                        const originalSelect = document.getElementById('select-coordinator');
                        const originalOption = originalSelect.querySelector(`option[value="${value}"]`);
                        if (originalOption) {
                            this.minDate = originalOption.getAttribute('data-start');
                            if (this.selectedDate < this.minDate) {
                                this.selectedDate = this.minDate;
                                alert(`Tanggal disesuaikan! Mulai Takeover: ${this.formatDateID(this.minDate)}.`);
                            }
                        }
                        this.fetchLocations();
                    } else {
                        this.locations = []; this.expenses = []; this.hasSelected = false; this.minDate = '';
                    }
                }
            });
        },

        fetchLocations() {
            if (!this.takeoverId || !this.coordId || !this.selectedDate) return;
            this.locations = [];
            fetch(`<?php echo BASE_URL?>/teaminput/getLocationsJson?takeover_id=${this.takeoverId}&coord_id=${this.coordId}&date=${this.selectedDate}`)
                .then(res => res.json())
                .then(data => {
                    this.locations = data.locations;
                    this.expenses = data.expenses; // Load pengeluaran juga
                });
        },

        // --- LOGIKA PENGELUARAN ---
        addExpense() {
            this.expenses.push({ description: '', amount: '' });
        },
        removeExpense(index) {
            this.expenses.splice(index, 1);
        },

        // --- HELPER HITUNG-HITUNGAN ---
        get totalIncome() {
            return this.locations.reduce((sum, loc) => sum + (parseFloat((loc.amount || '').toString().replace(/\./g, '')) || 0), 0);
        },
        get totalExpense() {
            return this.expenses.reduce((sum, exp) => sum + (parseFloat((exp.amount || '').toString().replace(/\./g, '')) || 0), 0);
        },
        get netTotal() {
            return this.totalIncome - this.totalExpense;
        },

        // --- FORMATTERS ---
        formatRupiahInput(e, loc) {
            loc.amount = this.formatNumberString(e.target.value);
        },
        formatRupiahExpense(e, exp) {
            exp.amount = this.formatNumberString(e.target.value);
        },
        formatNumberString(val) {
            let v = val.replace(/[^0-9]/g, '');
            if (v === '') return '';
            let sisa = v.length % 3, rupiah = v.substr(0, sisa), ribuan = v.substr(sisa).match(/\d{3}/g);
            if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
            return rupiah;
        },
        formatCurrency(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        },
        formatDateID(dateString) { return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }); }
    }
}
document.addEventListener('alpine:init', () => { Alpine.data('teamInputComponent', teamInputComponent); });
</script>