<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Setoran Parkir</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        h1 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dddddd; padding: 8px; text-align: left; }
        thead { background-color: #f2f2f2; }
        tfoot { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Setoran Parkir Harian</h1>
    <h1><strong>Koordinator:</strong>                                                                                                                                                              <?php echo htmlspecialchars($coordinator_name) ?></p></h1>
    <p><strong>Surveyor 1:</strong>                                                                                                                                             <?php echo htmlspecialchars($surveyor_1) ?></p>
    <p><strong>Surveyor 2:</strong>                                                                                                                                             <?php echo htmlspecialchars($surveyor_2) ?></p>

    <p><strong>Tanggal Cetak:</strong>                                                                                                                                                         <?php echo date('d F Y') ?></p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Lokasi</th>
                <th>Alamat</th>
                <th>Harian (Rp)</th>
                <th>Sabtu/Minggu (Rp)</th>
                <th>Bulanan (Rp)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $no           = 1;
                $totalDaily   = 0;
                $totalWeekend = 0;
                $totalMonthly = 0;
                foreach ($locations as $loc):
                    $deposit = $deposits[$loc->id] ?? null;
                    $totalDaily += $deposit->daily_deposits ?? 0;
                    $totalWeekend += $deposit->weekend_deposits ?? 0;
                    $totalMonthly += $deposit->monthly_deposits ?? 0;
                ?>
									            <tr>
									                <td><?php echo $no++ ?></td>
									                <td><?php echo htmlspecialchars($loc->parking_location) ?></td>
									                <td><?php echo htmlspecialchars($loc->address) ?></td>
									                <td><?php echo number_format($deposit->daily_deposits ?? 0, 0, ',', '.') ?></td>
									                <td><?php echo number_format($deposit->weekend_deposits ?? 0, 0, ',', '.') ?></td>
									                <td><?php echo number_format($deposit->monthly_deposits ?? 0, 0, ',', '.') ?></td>
									                <td><?php echo htmlspecialchars($deposit->information ?? '') ?></td>
									            </tr>
									            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>TOTAL</strong></td>
                <td><strong><?php echo number_format($totalDaily, 0, ',', '.') ?></strong></td>
                <td><strong><?php echo number_format($totalWeekend, 0, ',', '.') ?></strong></td>
                <td><strong><?php echo number_format($totalMonthly, 0, ',', '.') ?></strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>