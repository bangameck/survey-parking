<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title ?? 'Laporan Data Survey' ?></title>
    <style>
        @page { margin: 100px 25px 50px 25px; }

        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }

        /* Header */
        header {
            position: fixed;
            top: -80px;
            left: 0px;
            right: 0px;
            height: 80px;
            text-align: center;
        }
        header h1 { margin: 0; font-size: 16px; text-transform: uppercase; padding-bottom: 5px; }
        header p { margin: 2px 0; font-size: 11px; }

        /* Footer (DIPERBARUI) */
        footer {
            position: fixed;
            bottom: -30px;
            left: 0px;
            right: 0px;
            height: 30px;
            font-size: 9px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            font-style: italic;
        }

        /* Tabel */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 6px; vertical-align: top; }
        th {
            background-color: #4F46E5;
            color: white;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
            vertical-align: middle;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .coord-name {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            color: #000;
            margin-bottom: 5px;
        }

        .row-total { background-color: #E0E7FF; font-weight: bold; }
        .bg-gray { background-color: #f9fafb; }
        .row-grand-total { background-color: #059669; color: white; font-weight: bold; font-size: 11px; }

        .border-hide-bottom { border-bottom: 1px solid white !important; }
        .border-hide-top { border-top: 1px solid white !important; }
        .bg-white { background-color: #fff !important; }
    </style>
</head>
<body>

    <header>
        <h1><?php echo $title ?></h1>

        <p style="font-weight: bold; color: #444;">
            <?php if (! $stats['is_filtered']): ?>
                Total Koordinator:<?php echo $stats['total_coordinators'] ?> &nbsp;|&nbsp;
                Total Lokasi Parkir:                                                                         <?php echo $stats['total_locations'] ?>
            <?php else: ?>
                <?php echo $subtitle ?> &nbsp;|&nbsp;
                Total Lokasi Parkir:                                                                         <?php echo $stats['total_locations'] ?>
            <?php endif; ?>
        </p>
    </header>

    <footer>
        Dicetak otomatis melalui Aplikasi Survey Potensi Parkir Pekanbaru (<?php echo $app_url ?>) Pada (<?php echo $date ?> )
    </footer>

    <main>
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="15%">Koordinator</th>
                    <th width="15%">Nama Lokasi</th>
                    <th width="20%">Alamat</th>
                    <th width="9%">Harian</th>
                    <th width="9%">Weekend</th>
                    <th width="9%">Bulanan</th>
                    <th width="10%">Surveyor</th>
                    <th width="10%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($groupedData)): ?>
                    <tr><td colspan="9" class="text-center">Tidak ada data ditemukan.</td></tr>
                <?php else: ?>
                    <?php
                        $no = 1;
                        foreach ($groupedData as $coordinatorName => $locations):
                            $rowCount   = count($locations);
                            $subDaily   = 0;
                            $subWeekend = 0;
                            $subMonthly = 0;
                        ?>

		                        <?php foreach ($locations as $index => $row):
                                            $subDaily += (float) ($row->daily_deposits ?? 0);
                                            $subWeekend += (float) ($row->weekend_deposits ?? 0);
                                            $subMonthly += (float) ($row->monthly_deposits ?? 0);

                                            $coordCellStyle = "bg-white";
                                            if ($rowCount > 1) {
                                                if ($index === 0) {
                                                    $coordCellStyle .= " border-hide-bottom";
                                                } elseif ($index === $rowCount - 1) {
                                                $coordCellStyle .= " border-hide-top";
                                            } else {
                                                $coordCellStyle .= " border-hide-top border-hide-bottom";
                                            }

                                        }
                                    ?>
		                        <tr class="<?php echo $index % 2 == 0 ? '' : 'bg-gray' ?>">
		                            <td class="text-center"><?php echo $no++ ?></td>

		                            <td class="<?php echo $coordCellStyle ?>">
		                                <?php if ($index === 0): ?>
		                                    <div class="coord-name"><?php echo htmlspecialchars($coordinatorName ?? '-') ?></div>
		                                <?php endif; ?>
                            </td>

                            <td><?php echo htmlspecialchars($row->parking_location ?? '') ?></td>
                            <td><?php echo htmlspecialchars($row->address ?? '') ?></td>

                            <td class="text-right"><?php echo($row->daily_deposits ?? 0) > 0 ? 'Rp ' . number_format($row->daily_deposits, 0, ',', '.') : '-' ?></td>
                            <td class="text-right"><?php echo($row->weekend_deposits ?? 0) > 0 ? 'Rp ' . number_format($row->weekend_deposits, 0, ',', '.') : '-' ?></td>
                            <td class="text-right"><?php echo($row->monthly_deposits ?? 0) > 0 ? 'Rp ' . number_format($row->monthly_deposits, 0, ',', '.') : '-' ?></td>

                            <td>
                                <?php echo htmlspecialchars($row->surveyor_1 ?? '-') ?>
                                <?php echo ! empty($row->surveyor_2) ? '<br>' . htmlspecialchars($row->surveyor_2) : '' ?>
                            </td>
                            <td><?php echo htmlspecialchars($row->information ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <tr class="row-total">
                            <td colspan="4" class="text-right">Total                                                                                                                                         <?php echo htmlspecialchars(strtoupper($coordinatorName ?? '')) ?>:</td>
                            <td class="text-right">Rp                                                                                                           <?php echo number_format($subDaily, 0, ',', '.') ?></td>
                            <td class="text-right">Rp                                                                                                           <?php echo number_format($subWeekend, 0, ',', '.') ?></td>
                            <td class="text-right">Rp                                                                                                           <?php echo number_format($subMonthly, 0, ',', '.') ?></td>
                            <td colspan="2" style="background-color: #fff; border:none;"></td>
                        </tr>

                    <?php endforeach; ?>

                    <tr class="row-grand-total">
                        <td colspan="4" class="text-center" style="padding: 8px;">TOTAL KESELURUHAN HASIL SURVEY</td>
                        <td class="text-right" style="padding: 8px;">Rp                                                                                                                                               <?php echo number_format($grandTotal['daily'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-right" style="padding: 8px;">Rp                                                                                                                                               <?php echo number_format($grandTotal['weekend'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-right" style="padding: 8px;">Rp                                                                                                                                               <?php echo number_format($grandTotal['monthly'] ?? 0, 0, ',', '.') ?></td>
                        <td colspan="2" style="background-color: #fff; border:none;"></td>
                    </tr>

                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>