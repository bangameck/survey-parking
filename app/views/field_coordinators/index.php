<div x-data="{
    showCreateModal: false,
    showEditModal: false,
    editId: null,
    editName: '',
    formAction: ''
}">

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex justify-between items-center p-6">
            <h3 class="font-semibold text-lg text-gray-800">Manajemen Koordinator Lapangan</h3>
            <button @click="showCreateModal = true" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                + Tambah Baru
            </button>
        </div>

        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Koordinator</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($coordinators as $coord): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $coord->id ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($coord->name) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button
                                @click="
                                    fetch('<?php echo BASE_URL ?>/fieldcoordinators/getCoordinatorJson/<?php echo $coord->id ?>')
                                        .then(response => response.json())
                                        .then(data => {
                                            editId = data.id;
                                            editName = data.name;
                                            formAction = '<?php echo BASE_URL ?>/fieldcoordinators/update/' + data.id;
                                            showEditModal = true;
                                        });
                                "
                                class="text-indigo-600 hover:text-indigo-900">
                                Edit
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div x-show="showCreateModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40" @keydown.escape.window="showCreateModal = false">
        <div @click.away="showCreateModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 mx-4">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold">Tambah Koordinator Baru</h3>
                <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-800">&times;</button>
            </div>
            <form action="<?php echo BASE_URL ?>/fieldcoordinators/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
                <div class="mb-4">
                    <label for="create_name" class="block text-gray-700 text-sm font-bold mb-2">Nama Koordinator:</label>
                    <input type="text" id="create_name" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showEditModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40" @keydown.escape.window="showEditModal = false">
        <div @click.away="showEditModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 mx-4">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold">Edit Koordinator</h3>
                <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-800">&times;</button>
            </div>
            <form :action="formAction" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
                <div class="mb-4">
                    <label for="edit_name" class="block text-gray-700 text-sm font-bold mb-2">Nama Koordinator:</label>
                    <input type="text" id="edit_name" name="name" x-model="editName" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Update</button>
                </div>
            </form>
        </div>
    </div>

</div>