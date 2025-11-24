<div class="max-w-4xl mx-auto space-y-8">

    <div>
        <div class="mb-6 text-center">
            <h2 class="text-3xl font-bold text-gray-800">Pengambilalihan PKS</h2>
            <p class="text-gray-500 mt-1">Kelola lokasi expired dan tugaskan ke Tim UPT.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-500 rounded-full opacity-10 blur-xl"></div>

            <form action="<?php echo BASE_URL ?>/takeover/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Pilih Koordinator (Available)</label>
                        <div class="relative">
                            <select id="coordinator" name="coordinator_id" required class="w-full" placeholder="Cari nama koordinator...">
                                <option value="">Cari Koordinator...</option>
                                <?php foreach ($coordinators as $c): ?>
                                    <option value="<?php echo $c->id ?>"><?php echo htmlspecialchars($c->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (empty($coordinators)): ?>
                            <p class="text-xs text-red-500 mt-2">* Tidak ada koordinator yang tersedia.</p>
                        <?php endif; ?>
                    </div>

                    <div class="relative z-0 w-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Pilih Tim atau Ketik Baru</label>
                        <select id="team_select" name="team_name" required placeholder="Pilih atau ketik nama tim baru...">
                            <option value="">-- Pilih Tim --</option>
                            <?php foreach ($existing_teams as $team): ?>
                                <option value="<?php echo htmlspecialchars($team) ?>"><?php echo htmlspecialchars($team) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1" id="team-status-text">Ketik untuk membuat tim baru.</p>
                    </div>

                    <div class="relative z-0 w-full group pt-1">
                        <input type="date" name="start_date" id="start_date" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                        <label for="start_date" class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                            Tanggal Mulai
                        </label>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-8 transition-all duration-300" id="members-container">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-gray-700 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-4 0 4 4 0 014 0z"></path></svg>
                            Anggota Tim
                        </h3>
                        <span id="team-badge" class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">Tim Baru</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="relative z-0 w-full group">
                            <input type="text" name="members[]" id="member_<?php echo $i ?>"
                                   class="member-input block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer disabled:text-gray-500 disabled:border-gray-200"
                                   placeholder=" " required />
                            <label for="member_<?php echo $i ?>" class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                Nama Anggota                                                                                                                                                                                                                             <?php echo $i ?>
                            </label>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <div class="mt-4 p-3 bg-blue-50 rounded-lg flex items-start gap-3 border border-blue-100" id="info-login">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div class="text-xs text-blue-800">
                            <p class="font-bold">Info:</p>
                            <p class="mt-1" id="info-text">User akan dibuat otomatis dengan username <strong>namaanggota</strong> dan password default: <strong>password</strong></p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-bold shadow-lg hover:from-blue-700 hover:to-blue-800 transform hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Data
                </button>

            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Daftar PKS yang Sedang Diambil Alih</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Tim</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Koordinator (PKS)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Mulai</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($existing_takeovers)): ?>
                        <tr><td colspan="4" class="text-center py-6 text-gray-500 italic">Belum ada data pengambilalihan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($existing_takeovers as $et): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($et->team_name) ?></div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900"><?php echo htmlspecialchars($et->coordinator_name) ?></div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?php echo date('d M Y', strtotime($et->start_date)) ?></span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><span class="text-blue-600">Aktif</span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ==================================================================
    // FIX LOGIKA: Simpan data Tim Lama dari PHP ke variabel JS (Array)
    // ==================================================================
    const existingTeamsList =                                                                                                                                                                               <?php echo json_encode($existing_teams); ?>;

    // 1. TomSelect untuk Koordinator
    new TomSelect('#coordinator', {
        create: false,
        sortField: { field: "text", direction: "asc" },
        placeholder: 'Cari nama koordinator...',
        plugins: ['clear_button']
    });

    // 2. TomSelect untuk TIM (Bisa Create Baru)
    const teamSelect = new TomSelect('#team_select', {
        create: true, // Izinkan ketik baru
        sortField: { field: "text", direction: "asc" },
        placeholder: 'Pilih atau ketik nama tim...',
        plugins: ['clear_button'],
        onChange: function(value) {
            checkTeamStatus(value);
        }
    });

    // Fungsi untuk mengecek apakah tim baru atau lama
    function checkTeamStatus(teamName) {
        const inputs = document.querySelectorAll('.member-input');
        const badge = document.getElementById('team-badge');
        const infoText = document.getElementById('info-text');

        if (!teamName) {
            resetInputs();
            return;
        }

        // ==================================================================
        // PERBAIKAN UTAMA: Cek keberadaan tim dari ARRAY, bukan dari OPTIONS
        // ==================================================================
        let isExisting = existingTeamsList.includes(teamName);

        if (isExisting) {
            // --- KONDISI: TIM SUDAH ADA ---
            badge.textContent = 'Tim Sudah Ada';
            badge.className = 'px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full';
            infoText.textContent = 'Anggota tim akan dimuat otomatis. Tidak perlu input ulang.';

            // Fetch AJAX Anggota Tim
            fetch(`<?php echo BASE_URL ?>/takeover/getTeamMembersJson?team=${encodeURIComponent(teamName)}`)
                .then(response => response.json())
                .then(data => {
                    inputs.forEach((input, index) => {
                        if (data[index]) {
                            input.value = data[index]; // Isi nama dari DB
                            input.disabled = true;     // Kunci input
                            input.classList.add('bg-gray-100');
                        } else {
                            input.value = '';
                            input.disabled = true;
                            input.classList.add('bg-gray-100');
                        }
                    });
                });

        } else {
            // --- KONDISI: TIM BARU (KETIKAN USER) ---
            resetInputs(); // Bersihkan dan Buka Kunci
            badge.textContent = 'Tim Baru';
            badge.className = 'px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full';
            infoText.innerHTML = 'User akan dibuat otomatis dengan password default: <strong>123456</strong>';
        }
    }

    function resetInputs() {
        const inputs = document.querySelectorAll('.member-input');
        inputs.forEach(input => {
            input.value = '';
            input.disabled = false;
            input.classList.remove('bg-gray-100');
        });
    }
});
</script>