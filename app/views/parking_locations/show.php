<h3>Detail Lokasi: <?php echo htmlspecialchars($location->parking_location)?></h3>
<a href="<?php echo BASE_URL?>/parkinglocations" class="btn btn-secondary mb-3">Kembali ke Daftar</a>

<div class="card">
    <div class="card-header">
        Informasi Lokasi
    </div>
    <div class="card-body">
        <p><strong>Nama Lokasi:</strong> <?php echo htmlspecialchars($location->parking_location)?></p>
        <p><strong>Alamat:</strong> <?php echo htmlspecialchars($location->address)?></p>
        <p><strong>Koordinator:</strong> <?php echo htmlspecialchars($location->coordinator_name)?></p>
    </div>
</div>

<?php if ($_SESSION['user_role'] === 'admin'): ?>
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            Informasi Setoran (Hanya Admin)
        </div>
        <div class="card-body">
            <?php if ($location->deposits): ?>
                <p><strong>Setoran Harian:</strong> Rp <?php echo number_format($location->deposits->daily_deposits, 2, ',', '.')?></p>
                <p><strong>Setoran Akhir Pekan:</strong> Rp <?php echo number_format($location->deposits->weekend_deposits, 2, ',', '.')?></p>
                <p><strong>Setoran Bulanan:</strong> Rp <?php echo number_format($location->deposits->monthly_deposits, 2, ',', '.')?></p>
            <?php else: ?>
                <div class="alert alert-warning">Data setoran untuk lokasi ini belum diinput.</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>