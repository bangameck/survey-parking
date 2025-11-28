<div x-data="usersComponent()" class="space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h2>
            <p class="text-gray-500 mt-1">Kelola akun untuk Admin, Tim, Bendahara, dan Pimpinan.</p>
        </div>
        <button @click="openCreateModal()" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold text-sm shadow-lg transform hover:scale-105 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            Tambah User
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase w-12">No</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Username</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Tim (Khusus Tim)</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $no = 1;foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-500 text-center"><?php echo $no++?></td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900"><?php echo htmlspecialchars($user->username)?></td>
                        <td class="px-6 py-4">
                            <?php
                                $roleClass = 'bg-gray-100 text-gray-800';
                                if ($user->role == 'admin') {
                                    $roleClass = 'bg-red-100 text-red-800';
                                } elseif ($user->role == 'team') {
                                    $roleClass = 'bg-blue-100 text-blue-800';
                                } elseif ($user->role == 'bendahara') {
                                    $roleClass = 'bg-green-100 text-green-800';
                                } elseif ($user->role == 'pimpinan') {
                                    $roleClass = 'bg-purple-100 text-purple-800';
                                }

                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?php echo $roleClass?>">
                                <?php echo $user->role?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo $user->role == 'team' ? htmlspecialchars($user->team_name) : '-'?>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <button @click="openEditModal(<?php echo $user->id?>)" class="text-indigo-600 hover:text-indigo-900 mr-3 font-bold">Edit</button>
                            <?php if ($user->id != $_SESSION['user_id']): ?>
                            <form id="delete-form-<?php echo $user->id?>" action="<?php echo BASE_URL?>/users/destroy/<?php echo $user->id?>" method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
                                <button type="button" @click="confirmDelete(<?php echo $user->id?>)" class="text-red-600 hover:text-red-900 font-bold">Hapus</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm">
        <div @click.away="showModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 relative">
            <h3 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2" x-text="isEdit ? 'Edit User' : 'Tambah User Baru'"></h3>

            <form :action="formAction" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">

                <div class="space-y-5">

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" x-model="formData.username" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               :placeholder="isEdit ? 'Kosongkan jika tidak diubah' : 'Masukkan password'">
                        <p x-show="isEdit" class="text-xs text-gray-400 mt-1">* Isi hanya jika ingin mengganti password.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Role (Jabatan)</label>
                        <select name="role" x-model="formData.role" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="admin">Admin</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="pimpinan">Pimpinan</option>
                            <option value="guest">Guest</option>
                            <option value="team">Anggota Tim Lapangan</option>
                        </select>
                    </div>

                    <div x-show="formData.role === 'team'" x-transition>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Tim</label>
                        <input type="text" name="team_name" x-model="formData.team_name" placeholder="Contoh: Tim 1" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-400 mt-1">Wajib diisi untuk Anggota Tim Lapangan.</p>
                    </div>

                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function usersComponent() {
    return {
        showModal: false,
        isEdit: false,
        formData: { username: '', role: 'guest', team_name: '' },
        formAction: '',

        openCreateModal() {
            this.isEdit = false;
            this.formData = { username: '', role: 'guest', team_name: '' };
            this.formAction = '<?php echo BASE_URL?>/users/store';
            this.showModal = true;
        },

        openEditModal(id) {
            this.isEdit = true;
            fetch(`<?php echo BASE_URL?>/users/getUserJson/${id}`)
                .then(res => res.json())
                .then(data => {
                    this.formData = {
                        username: data.username,
                        role: data.role,
                        team_name: data.team_name || ''
                    };
                    this.formAction = `<?php echo BASE_URL?>/users/update/${id}`;
                    this.showModal = true;
                });
        },

        confirmDelete(id) {
            Swal.fire({
                title: 'Hapus User?',
                text: "Akses akun ini akan hilang permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    }
}
document.addEventListener('alpine:init', () => {
    Alpine.data('usersComponent', usersComponent);
});
</script>