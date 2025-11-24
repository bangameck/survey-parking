<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        h2 { margin: 0 0 5px 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 6px; vertical-align: middle; }
        th { background-color: #eee; text-align: center; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* WARNA TEXT */
        .text-green { color: #059669; font-weight: bold; }
        .text-orange { color: #d97706; font-weight: bold; } /* Warning */
        .text-red { color: #dc2626; font-style: italic; } /* Belum Setor */
        .text-blue { color: #2563eb; font-weight: bold; } /* Lunas Bulanan */
        .text-yellow { color: #ca8a04; font-weight: bold; } /* Lunas Mingguan */
        .text-gray { color: #6b7280; }

        .target-info { font-size: 9px; color: #666; display: block; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <h2><?php echo $title?></h2>
        <p>Tim: <strong><?php echo htmlspecialchars($team_name)?></strong> | Cetak: <?php echo date('d F Y H:i')?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Nama Lokasi & Alamat</th>
                <?php if ($report_type == 'harian'): ?>
                    <th width="15%">Target</th>
                    <th width="20%">Status</th>
                    <th width="15%">Catatan</th>
                <?php else: ?>
                    <th width="15%">Target/Bln</th>
                    <th width="20%">Periode</th>
                <?php endif; ?>
                <th width="15%">Realisasi</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $no    = 1;
                $total = 0;
                foreach ($data as $row):
                    $amount = $report_type == 'harian' ? ($row->amount ?? 0) : $row->total_amount;
                    $total += $amount;

                    // Logic Target
                    $target     = 0;
                    $tipeTarget = '-';
                    if ($row->monthly_deposits > 0) {$target = $row->monthly_deposits;
                        $tipeTarget                       = 'Bulanan';} elseif ($row->daily_deposits > 0) {$target = $row->daily_deposits;
                    $tipeTarget                        = 'Harian';} elseif ($row->weekend_deposits > 0) {$target = $row->weekend_deposits;
                    $tipeTarget                        = 'Weekend';}

                // Logic Warna Realisasi
                $classRealisasi = 'text-gray';
                if ($amount > 0) {
                    if ($amount >= $target && $target > 0) {
                        $classRealisasi = 'text-green';
                    } elseif ($amount < $target && $target > 0) {
                        $classRealisasi = 'text-orange';
                    } else {
                        $classRealisasi = 'text-black';
                    }

                }
            ?>
            <tr>
                <td class="text-center"><?php echo $no++?></td>
                <td>
                    <strong><?php echo htmlspecialchars($row->parking_location)?></strong><br>
                    <span style="font-size: 9px; color: #555;"><?php echo htmlspecialchars($row->address)?></span>
                </td>

                <td class="text-right">
                     <?php if ($target > 0): ?>
                        Rp <?php echo number_format($target, 0, ',', '.')?><br>
                        <span class="target-info">(<?php echo $tipeTarget?>)</span>
                     <?php else: ?>
                        -
                     <?php endif; ?>
                </td>

                <?php if ($report_type == 'harian'): ?>
                    <td class="text-center">
                        <?php
                            if ($amount > 0) {
                                echo '<span class="text-green">Sudah Setor</span>';
                            } elseif ($tipeTarget == 'Bulanan' && ($row->is_paid_monthly > 0)) {
                                echo '<span class="text-blue">Lunas Bulanan</span>';
                            } elseif ($tipeTarget == 'Weekend' && ($row->is_paid_weekly > 0)) {
                                echo '<span class="text-yellow">Lunas Mingguan</span>';
                            } else {
                                echo '<span class="text-red">Belum Setor</span>';
                            }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row->notes ?? '-')?></td>
                <?php else: ?>
                    <td class="text-center">
                        <?php
                            if ($tipeTarget == 'Bulanan') {
                                echo "1 Bulan";
                            } elseif ($tipeTarget == 'Weekend') {
                                echo $row->total_trx . " Minggu";
                            } else {
                                echo $row->total_trx . " Hari";
                            }

                        ?>
                    </td>
                <?php endif; ?>

                <td class="text-right <?php echo $classRealisasi?>">
                    Rp <?php echo number_format($amount, 0, ',', '.')?>
                </td>
            </tr>
            <?php endforeach; ?>

            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="<?php echo $report_type == 'harian' ? '5' : '4'?>" class="text-right">TOTAL PENDAPATAN</td>
                <td class="text-right text-green" style="font-size: 12px;">Rp <?php echo number_format($total, 0, ',', '.')?></td>
            </tr>
        </tbody>
    </table>
</body>
</html>