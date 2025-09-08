<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title ?? 'Laporan Titik Lokasi Parkir'?></title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; }
        h1 { text-align: center; margin-bottom: 5px; }
        .info { text-align: center; margin-bottom: 20px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dddddd; padding: 6px; text-align: left; }
        thead tr { background-color: #f2f2f2; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; }
    </style>
</head>
<body>
    <div class="footer">
        Laporan Dibuat pada: <?php echo date('d F Y, H:i:s')?>
    </div>

    <h1>Laporan Daftar Titik Lokasi Parkir</h1>
    <div class="info">
        <?php if ($coordinator_name): ?>
            <strong>Koordinator:</strong> <?php echo htmlspecialchars($coordinator_name)?>
<?php endif; ?>
<?php if ($searchTerm): ?>
            | <strong>Pencarian:</strong> "<?php echo htmlspecialchars($searchTerm)?>"
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 30%;">Nama Lokasi</th>
                <th style="width: 40%;">Alamat</th>
                <th style="width: 25%;">Nama Koordinator</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($locations)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada data ditemukan.</td>
                </tr>
            <?php else: ?>
<?php $nomor = 1;foreach ($locations as $loc): ?>
                <tr>
                    <td style="text-align: center;"><?php echo $nomor++?></td>
                    <td><?php echo htmlspecialchars($loc->parking_location)?></td>
                    <td><?php echo htmlspecialchars($loc->address)?></td>
                    <td><?php echo htmlspecialchars($loc->coordinator_name)?></td>
                </tr>
                <?php endforeach; ?>
<?php endif; ?>
        </tbody>
    </table>
</body>
</html>