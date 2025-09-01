<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">Backup Database</h1>
        <p class="text-gray-600 mb-6">
            Klik tombol di bawah ini untuk membuat dan mengunduh file backup (`.sql`) dari seluruh database <strong><?php echo DB_NAME ?></strong>.
            File ini berisi semua data yang telah Anda input (Koordinator, Lokasi, Setoran, dll).
        </p>
        <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 mb-6">
            <h4 class="font-bold">Cara Menggunakan File Backup</h4>
            <p class="text-sm mt-1">
                Setelah Anda meng-upload aplikasi ke hosting, Anda bisa mengimpor file `.sql` ini melalui <strong>phpMyAdmin</strong> di hosting Anda untuk mentransfer semua data dari lokal ke server produksi.
            </p>
        </div>

        <form action="<?php echo BASE_URL ?>/backup/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>">
            <button type="submit" class="w-full py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-lg focus:outline-none focus:shadow-outline">
                Backup & Download Database Sekarang
            </button>
        </form>
    </div>
</div>