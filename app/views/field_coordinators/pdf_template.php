<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title ?? 'Laporan Koordinator'?></title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dddddd; padding: 8px; text-align: left; }
        thead tr { background-color: #f2f2f2; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="footer">
        Dicetak pada: <?php echo date('d F Y, H:i:s')?>
    </div>

    <h1>Laporan Daftar Koordinator Lapangan</h1>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 55%;">Nama Koordinator</th>
                <th style="width: 20%;" class="text-center">Jumlah Titik Parkir</th>
                <th style="width: 20%;">Checklist</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($coordinators)): ?>
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data.</td>
                </tr>
            <?php else: ?>
<?php $nomor = 1;foreach ($coordinators as $coord): ?>
                <tr>
                    <td class="text-center"><?php echo $nomor++?></td>
                    <td><?php echo htmlspecialchars($coord->name)?></td>
                    <td class="text-center"><?php echo $coord->location_count?></td>
                    <td></td>
                </tr>
                <?php endforeach; ?>
<?php endif; ?>
        </tbody>
    </table>
</body>
</html>