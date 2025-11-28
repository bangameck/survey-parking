<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title?></title>
    <style>
        @page { margin: 40px; } /* Landscape Margin */
        body { font-family: Arial, sans-serif; color: #333; font-size: 12px; }

        /* Header */
        .header-box {
            background-color: #312e81; /* Indigo 900 */
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .header-title { font-size: 24px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .header-sub { font-size: 14px; margin-top: 5px; opacity: 0.9; }
        .header-date { float: right; font-weight: bold; font-size: 16px; margin-top: -35px; }

        /* Grid System (Using Tables) */
        .grid-table { width: 100%; border-collapse: separate; border-spacing: 10px; margin-bottom: 10px; }
        .col { vertical-align: top; }
        .w-25 { width: 25%; }
        .w-50 { width: 50%; }

        /* Card Style */
        .card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            height: 90px;
        }
        .card-label { font-size: 10px; color: #666; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
        .card-value { font-size: 24px; font-weight: bold; color: #1f2937; margin: 0; }

        /* Borders */
        .border-l-indigo { border-left: 4px solid #6366f1; }
        .border-l-purple { border-left: 4px solid #a855f7; }
        .border-l-yellow { border-left: 4px solid #eab308; }
        .border-l-blue { border-left: 4px solid #3b82f6; }
        .border-t-green { border-top: 4px solid #22c55e; }
        .border-t-teal { border-top: 4px solid #14b8a6; }

        /* Section Title */
        .section-title {
            font-size: 14px; font-weight: bold; color: #1f2937;
            border-bottom: 2px solid #eee; padding-bottom: 5px; margin: 20px 0 10px 0;
        }

        /* Table Style */
        .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .data-table th { background: #f3f4f6; padding: 8px; text-align: left; border-bottom: 2px solid #ddd; }
        .data-table td { padding: 6px 8px; border-bottom: 1px solid #eee; }

        /* Images */
        .chart-img { width: 100%; height: auto; max-height: 250px; }

        /* Explanation Box */
        .explanation-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            font-size: 11px;
            line-height: 1.6;
            color: #374151;
            page-break-inside: avoid;
        }
        .explanation-title {
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 8px;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .highlight { color: #000; font-weight: bold; }
        .trend-up { color: #16a34a; font-weight: bold; }
        .trend-down { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header-box">
        <h1 class="header-title">Executive Report</h1>
        <p class="header-sub">Ringkasan Operasional & Keuangan UPT Perparkiran</p>
        <div class="header-date"><?php echo date('d F Y')?></div>
    </div>

    <div class="section-title">DATA OPERASIONAL & POTENSI</div>
    <table class="grid-table">
        <tr>
            <td class="col w-25">
                <div class="card border-l-indigo">
                    <div class="card-label">Total Lokasi</div>
                    <div class="card-value"><?php echo number_format($total_locations)?></div>
                </div>
            </td>
            <td class="col w-25">
                <div class="card border-l-purple">
                    <div class="card-label">Koordinator</div>
                    <div class="card-value"><?php echo number_format($total_coordinators)?></div>
                </div>
            </td>
            <td class="col w-25">
                <div class="card border-l-yellow">
                    <div class="card-label">Lokasi Disurvey</div>
                    <div class="card-value"><?php echo number_format($surveyed_locations)?></div>
                    <div style="font-size: 10px; color: #888; margin-top: 3px;">
                        <?php echo ($total_locations > 0) ? round(($surveyed_locations / $total_locations) * 100) : 0?>% Selesai
                    </div>
                </div>
            </td>
            <td class="col w-25">
                <div class="card border-l-blue" style="background-color: #f0f9ff;">
                    <div class="card-label" style="color: #0369a1;">Total Potensi (Est)</div>
                    <div class="card-value" style="font-size: 20px;">Rp <?php echo number_format($total_potential, 0, ',', '.')?></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">REALISASI KEUANGAN TIM (TAKEOVER)</div>
    <table class="grid-table">
        <tr>
            <td class="col w-25">
                <div class="card border-t-green">
                    <div class="card-label">Hari Ini</div>
                    <div class="card-value" style="font-size: 20px;">Rp <?php echo number_format($finance->today, 0, ',', '.')?></div>
                </div>
            </td>
            <td class="col w-25">
                <div class="card border-t-green">
                    <div class="card-label">Bulan Ini</div>
                    <div class="card-value" style="font-size: 20px;">Rp <?php echo number_format($finance->this_month, 0, ',', '.')?></div>
                </div>
            </td>
            <td class="col w-25">
                <div class="card border-t-teal">
                    <div class="card-label">Tahun Ini</div>
                    <div class="card-value" style="font-size: 20px;">Rp <?php echo number_format($finance->this_year, 0, ',', '.')?></div>
                </div>
            </td>
            <td class="col w-25">
                <div class="card" style="background-color: #166534; color: white; border: none;">
                    <div class="card-label" style="color: #bbf7d0;">Total Realisasi</div>
                    <div class="card-value" style="color: white; font-size: 22px;">Rp <?php echo number_format($finance->total_all_time, 0, ',', '.')?></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">ANALISIS KINERJA</div>
    <table class="grid-table">
        <tr>
            <td class="col w-50">
                <div class="card" style="height: auto; min-height: 200px;">
                    <div class="card-label">Tren Pendapatan (7 Hari)</div>
                    <img src="<?php echo $chart_income?>" class="chart-img">
                </div>
            </td>

            <td class="col w-25">
                <div class="card" style="height: auto; min-height: 200px; text-align: center;">
                    <div class="card-label">Progres Survey</div>
                    <img src="<?php echo $chart_survey?>" style="width: 120px; height: auto; margin-top: 20px;">
                    <div style="margin-top: 10px; font-weight: bold; font-size: 14px;">
                        <?php echo ($total_locations > 0) ? round(($surveyed_locations / $total_locations) * 100) : 0?>%
                    </div>
                </div>
            </td>

            <td class="col w-25">
                <div class="card" style="height: auto; min-height: 200px; padding: 0;">
                    <div style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; font-size: 11px;">Top Tim (Bulan Ini)</div>
                    <table class="data-table">
                        <?php if (empty($team_performance)): ?>
                            <tr><td colspan="2" style="text-align: center; padding: 20px; color: #999;">Belum ada data.</td></tr>
                        <?php else: ?>
                            <?php foreach (array_slice($team_performance, 0, 5) as $idx => $team): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo $idx + 1?> <?php echo htmlspecialchars($team->team_name)?></strong><br>
                                    <span style="color:#666; font-size: 9px;"><?php echo $team->active_locations?> Lokasi</span>
                                </td>
                                <td style="text-align: right; font-weight: bold; color: #166534;">
                                    <?php echo number_format($team->total_revenue / 1000, 0)?>k
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <?php
        // Logic PHP untuk Menghasilkan Kalimat

        // 1. Cari Puncak Pendapatan
        $maxIncome = 0;
        $maxDate   = '-';
        if (! empty($income_values)) {
            $maxIncome = max($income_values);
            $key       = array_search($maxIncome, $income_values);
            $maxDate   = $income_labels[$key] ?? '-';
        }

        // 2. Cari Tim Terbaik
        $topTeamName   = '-';
        $topTeamAmount = 0;
        if (! empty($team_performance)) {
            $top           = $team_performance[0];
            $topTeamName   = $top->team_name;
            $topTeamAmount = $top->total_revenue;
        }

        // 3. Hitung Sisa Survey
        $percent   = ($total_locations > 0) ? round(($surveyed_locations / $total_locations) * 100, 1) : 0;
        $remaining = $total_locations - $surveyed_locations;
    ?>

    <div class="explanation-box">
        <div class="explanation-title">PENJELASAN CHART & ANALISIS DATA</div>

        <p>
            <strong>1. Tren Pendapatan (Income Trend):</strong><br>
            Berdasarkan pemantauan data keuangan selama 7 hari terakhir, puncak pendapatan tertinggi terjadi pada tanggal
            <span class="highlight"><?php echo $maxDate?></span> dengan total setoran mencapai
            <span class="trend-up">Rp <?php echo number_format($maxIncome, 0, ',', '.')?></span>.
            <?php if ($topTeamName !== '-'): ?>
            Kinerja terbaik bulan ini didominasi oleh <span class="highlight"><?php echo htmlspecialchars($topTeamName)?></span>
            yang berhasil membukukan total setoran sebesar <span class="highlight">Rp <?php echo number_format($topTeamAmount, 0, ',', '.')?></span>.
            <?php endif; ?>
        </p>

        <p style="margin-top: 10px;">
            <strong>2. Progres Survey (Survey Progress):</strong><br>
            Hingga saat ini, tim surveyor telah berhasil mendata sebanyak <span class="highlight"><?php echo number_format($surveyed_locations)?></span>
            titik lokasi parkir dari total <span class="highlight"><?php echo number_format($total_locations)?></span> target lokasi yang ada.
            Capaian ini setara dengan <span class="highlight"><?php echo $percent?>%</span> dari keseluruhan target.
            Masih terdapat <span class="highlight"><?php echo number_format($remaining)?></span> titik lokasi yang perlu segera diselesaikan pendataannya untuk memaksimalkan potensi pendapatan daerah.
        </p>
    </div>

</body>
</html>