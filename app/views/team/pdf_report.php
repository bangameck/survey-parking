<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title ?></title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10px; color: #333; }
        @page { margin: 30px; }

        /* HEADER */
        .header-container { text-align: center; margin-bottom: 25px; border-bottom: 3px double #1e3a8a; padding-bottom: 10px; }
        .header-title { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #1e3a8a; margin: 0; }
        .header-meta { font-size: 11px; color: #555; margin-top: 5px; }

        /* TABEL MODERN */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th {
            background-color: #1e3a8a; /* Biru Tua Modern */
            color: #fff;
            font-weight: bold;
            padding: 8px;
            text-transform: uppercase;
            font-size: 9px;
            border: 1px solid #1e3a8a;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        /* Zebra Striping */
        tr:nth-child(even) { background-color: #f8fafc; }

        /* GROUP HEADER (Nama Koordinator) */
        .group-header { background-color: #dbeafe; color: #1e40af; font-weight: bold; font-size: 11px; }

        /* TEXT UTILITIES */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        /* STATUS COLORS */
        .badge-green { color: #166534; font-weight: bold; }
        .badge-yellow { color: #ca8a04; font-weight: bold; }
        .badge-orange { color: #c2410c; font-weight: bold; }
        .badge-red { color: #dc2626; font-style: italic; }
        .badge-blue { color: #2563eb; font-weight: bold; }

        /* EXPENSE BOX */
        .expense-box { margin-top: 30px; page-break-inside: avoid; }
        .expense-title { font-size: 12px; font-weight: bold; color: #dc2626; margin-bottom: 5px; border-bottom: 2px solid #dc2626; display: inline-block; }

        /* SUMMARY BOX (Grand Total) */
        .summary-box {
            margin-top: 30px;
            background-color: #f0fdf4;
            border: 2px solid #166534;
            padding: 15px;
            border-radius: 8px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <div class="header-container">
        <h1 class="header-title"><?php echo $title ?></h1>
        <div class="header-meta">
            TIM: <strong><?php echo htmlspecialchars($team_name) ?></strong> &nbsp;|&nbsp;
            CETAK:                                     <?php echo date('d F Y, H:i') ?>
        </div>
    </div>

    <?php
        $grandTotalIncome = 0;
        // Grouping Data by Koordinator
        $groupedData = [];
        if (! empty($data)) {
            foreach ($data as $row) {
                $groupedData[$row->coordinator_name][] = $row;
            }
        }
    ?>

    <?php if ($report_type == 'harian'): ?>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="35%">Lokasi & Alamat</th>
                    <th width="15%">Zona</th>
                    <th width="15%">Target</th>
                    <th width="15%">Status</th>
                    <th width="15%">Realisasi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($groupedData)): ?>
                    <tr><td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data lokasi.</td></tr>
                <?php else: ?>
                    <?php foreach ($groupedData as $coordName => $locations):
                            $subTotal = 0;
                            $no       = 1;
                        ?>
			                        <tr>
			                            <td colspan="6" class="group-header">KOORDINATOR:			                                                                             		                                                                              <?php echo htmlspecialchars($coordName) ?></td>
			                        </tr>

			                        <?php foreach ($locations as $row):
                                                $amount = $row->amount ?? 0;
                                                $subTotal += $amount;
                                                $grandTotalIncome += $amount;

                                                // Logic Target
                                                $target     = 0;
                                                $tipeTarget = '-';
                                                if ($row->monthly_deposits > 0) {$target = $row->monthly_deposits;
                                                    $tipeTarget                      = 'Bulanan';} elseif ($row->daily_deposits > 0) {$target = $row->daily_deposits;
                                                $tipeTarget                       = 'Harian';} elseif ($row->weekend_deposits > 0) {$target = $row->weekend_deposits;
                                            $tipeTarget                        = 'Weekend';}

                                        // Logic Status
                                        $statusHtml = '<span class="badge-red">Belum Setor</span>';
                                        if ($amount > 0) {
                                            $statusHtml = '<span class="badge-green">Sudah Setor</span>';
                                        } elseif ($tipeTarget == 'Bulanan' && ($row->is_paid_monthly > 0)) {
                                            $statusHtml = '<span class="badge-blue">Lunas Bln</span>';
                                        } elseif ($tipeTarget == 'Weekend' && ($row->is_paid_weekly > 0)) {
                                            $statusHtml = '<span class="badge-yellow">Lunas Mgg</span>';
                                        }

                                    ?>
                        <tr>
                            <td class="text-center"><?php echo $no++ ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row->parking_location) ?></strong><br>
                                <span style="color:#666; font-size:9px;"><?php echo htmlspecialchars($row->address) ?></span>
                            </td>
                            <td class="text-center"><?php echo htmlspecialchars($row->zone ?? '-') ?></td>
                            <td class="text-right">
                                <?php if ($target > 0): ?>
                                    Rp<?php echo number_format($target, 0, ',', '.') ?><br>
                                    <span style="font-size:8px; color:#666;">(<?php echo $tipeTarget ?>)</span>
                                <?php else: ?> -<?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo $statusHtml ?></td>
                            <td class="text-right font-bold">Rp                                                                                                                               <?php echo number_format($amount, 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <tr style="background-color: #e0f2fe; border-top: 2px solid #93c5fd;">
                            <td colspan="5" class="text-right font-bold text-blue-900">TOTAL                                                                                                                                                                                         <?php echo strtoupper($coordName) ?>:</td>
                            <td class="text-right font-bold text-blue-900">Rp                                                                                                                                                           <?php echo number_format($subTotal, 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th rowspan="2" width="3%">No</th>
                    <th rowspan="2" width="20%">Lokasi</th>
                    <th colspan="<?php echo $daysInMonth ?>">Tanggal</th>
                    <th rowspan="2" width="10%">Total</th>
                </tr>
                <tr>
                    <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                        <th style="font-size: 7px; padding: 2px;"><?php echo $d ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groupedData as $coordName => $locations):
                        $no = 1;
                    ?>
			                <tr><td colspan="<?php echo $daysInMonth + 3 ?>" class="group-header"><?php echo htmlspecialchars($coordName) ?></td></tr>
			                <?php foreach ($locations as $loc):
                                        $rowTotal = 0;
                                    ?>
						                <tr>
						                    <td class="text-center"><?php echo $no++ ?></td>
						                    <td>
						                        <strong><?php echo htmlspecialchars($loc->parking_location) ?></strong><br>
						                        <span style="font-size:8px;"><?php echo htmlspecialchars($loc->address) ?></span>
						                    </td>
						                    <?php for ($d = 1; $d <= $daysInMonth; $d++):
                                                            $val = $loc->deposits[$d] ?? null;
                                                            $bg  = '';
                                                            $txt = '';
                                                            if ($val) {
                                                                $amount = $val->amount;
                                                                $rowTotal += $amount;
                                                                $grandTotalIncome += $amount;
                                                                if ($val->status == 'bulanan') {$bg = '#ffedd5';
                                                                    $txt                        = 'B';} elseif ($val->status == 'weekend') {$bg = '#fef9c3';
                                                                $txt                         = 'W';} else { $bg = '#dcfce7';
                                                                $txt                          = number_format($amount / 1000, 0);}
                                                        }
                                                    ?>
						                    <td style="background-color:<?php echo $bg ?>; font-size:8px; text-align:center; padding:2px;"><?php echo $txt ?></td>
						                    <?php endfor; ?>
			                    <td class="text-right font-bold">Rp<?php echo number_format($rowTotal, 0, ',', '.') ?></td>
			                </tr>
			                <?php endforeach;endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="margin-top: 30px; page-break-inside: avoid;">
        <h3 style="margin: 0 0 10px 0; border-bottom: 1px solid #ccc; font-size: 12px; color: #991b1b; text-transform: uppercase;">
            Rincian Pengeluaran Operasional
        </h3>

        <!-- Tabel Pengeluaran (Float Kiri) -->
        <table style="width: 60%; float: left; margin-right: 20px; font-size: 9px; border: none;">
            <thead>
                <tr>
                    <?php if ($report_type != 'harian'): ?>
                        <th width="20%" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">TANGGAL</th>
                    <?php endif; ?>
                    <th style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">KOORDINATOR / KETERANGAN</th>
                    <th width="25%" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;" class="text-right">JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $totalExp = 0;
                    if (empty($expenses)):
                ?>
                    <tr>
                        <td colspan="<?php echo $report_type != 'harian' ? '3' : '2'?>" style="text-align:center; padding: 15px; font-style:italic; border: 1px solid #eee;">
                            Tidak ada pengeluaran tercatat.
                        </td>
                    </tr>
                <?php else:
                        // Grouping Pengeluaran by Koordinator
                        $expenseGrouped = [];
                        foreach ($expenses as $exp) {
                            $expenseGrouped[$exp->coordinator_name][] = $exp;
                        }

                        foreach ($expenseGrouped as $coordName => $items):
                            $subExp = 0;
                        ?>
		                    <!-- Header Koordinator -->
		                    <tr>
		                        <td colspan="<?php echo $report_type != 'harian' ? '3' : '2'?>" style="background-color: #fff1f2; font-weight: bold; color: #be123c; border: 1px solid #fecdd3; text-align: left;">
		                            <?php echo htmlspecialchars($coordName)?>
		                        </td>
		                    </tr>

		                    <?php foreach ($items as $exp):
                                            $totalExp += $exp->amount;
                                            $subExp += $exp->amount;
                                        ?>
			                    <tr>
			                        <?php if ($report_type != 'harian'): ?>
			                            <td class="text-center" style="border: 1px solid #eee;">
			                                <?php echo date('d/m/Y', strtotime($exp->expense_date))?>
			                            </td>
			                        <?php endif; ?>

		                        <td class="text-left" style="padding-left: 15px; border: 1px solid #eee;">
		                            - <?php echo htmlspecialchars($exp->description)?>
		                        </td>
		                        <td class="text-right" style="border: 1px solid #eee;">
		                            Rp <?php echo number_format($exp->amount, 0, ',', '.')?>
		                        </td>
		                    </tr>
		                    <?php endforeach; ?>

	                    <!-- Subtotal per Koordinator -->
	                    <tr style="background-color: #fafafa;">
	                        <td colspan="<?php echo $report_type != 'harian' ? '2' : '1'?>" class="text-right" style="font-style: italic; border: 1px solid #eee;">Subtotal:</td>
	                        <td class="text-right" style="font-weight: bold; color: #be123c; border: 1px solid #eee;">Rp <?php echo number_format($subExp, 0, ',', '.')?></td>
	                    </tr>

	                <?php endforeach;endif; ?>

                <!-- Footer Total Pengeluaran -->
                <tr style="background: #fee2e2; font-weight: bold;">
                    <td colspan="<?php echo $report_type != 'harian' ? '2' : '1'?>" class="text-right" style="border: 1px solid #fca5a5; color: #991b1b;">TOTAL PENGELUARAN</td>
                    <td class="text-right" style="border: 1px solid #fca5a5; color: #991b1b;">Rp <?php echo number_format($totalExp, 0, ',', '.')?></td>
                </tr>
            </tbody>
        </table>

        <!-- Kotak Ringkasan Final (Float Kanan) -->
        <div style="float: right; width: 35%; text-align: right; border: 2px solid #e5e7eb; padding: 15px; background: #f9fafb; border-radius: 8px;">
            <div style="margin-bottom: 10px;">
                <span style="font-size: 11px; color: #374151; display: block;">TOTAL PEMASUKAN (KOTOR)</span>
                <strong style="font-size: 14px; color: #166534;">Rp <?php echo number_format($grandTotalIncome ?? 0, 0, ',', '.')?></strong>
            </div>

            <div style="margin-bottom: 10px;">
                <span style="font-size: 11px; color: #374151; display: block;">TOTAL PENGELUARAN</span>
                <strong style="font-size: 14px; color: #dc2626;">- Rp <?php echo number_format($totalExp, 0, ',', '.')?></strong>
            </div>

            <div style="border-top: 2px dashed #9ca3af; margin: 10px 0;"></div>

            <div>
                <span style="font-size: 12px; font-weight: bold; color: #111827; display: block;">SETORAN BERSIH (NETTO)</span>
                <h2 style="color: #15803d; font-size: 18px; margin: 5px 0 0 0;">
                    Rp <?php echo number_format(($grandTotalIncome ?? 0) - $totalExp, 0, ',', '.')?>
                </h2>
            </div>
        </div>

        <!-- Clear Float -->
        <div style="clear: both;"></div>
    </div>

</body>
</html>