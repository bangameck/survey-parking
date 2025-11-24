<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title ?></title>
    <style>
        /* Gaya dasar */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "DejaVu Sans", "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 25px;
            background-color: #f9fafb;
            color: #1f2937;
            font-size: 14px;
        }
        h1 {
            text-align: center;
            color: #111827;
            font-size: 28px;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 30px;
        }

        /* Layout Utama */
        .main-layout {
            width: 100%;
            border-collapse: separate;
            border-spacing: 20px 0;
        }
        .stats-column {
            width: 60%; /* Lebar kolom statistik */
            vertical-align: top;
        }
        .chart-column {
            width: 40%; /* Lebar kolom chart */
            vertical-align: top;
        }

        /* Grid Statistik */
        .stats-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px;
        }
        .stats-grid-cell {
            width: 50%;
            vertical-align: top;
        }

        /* Style Card */
        .card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px;
            height: 110px; /* Tinggi sedikit dikurangi agar muat banyak */
            overflow: hidden;
        }

        /* Warna Border Atas */
        .card-blue { border-top: 5px solid #3b82f6; }
        .card-purple { border-top: 5px solid #8b5cf6; }
        .card-yellow { border-top: 5px solid #eab308; }
        .card-green { border-top: 5px solid #22c55e; } /* Untuk semua pendapatan */

        .card-label {
            font-size: 13px;
            color: #6b7280;
            margin: 0 0 8px 0;
            font-weight: 500;
        }
        .card-value {
            font-size: 28px;
            font-weight: bold;
            color: #111827;
            margin: 0;
        }
        .card-value-money {
            font-size: 24px;
            font-weight: bold;
            color: #16a34a; /* Hijau Text */
            margin: 0;
        }

        /* Progress Bar */
        .progress-bar-bg {
            width: 100%;
            background-color: #e5e7eb;
            border-radius: 5px;
            height: 8px;
            margin-top: 10px;
        }
        .progress-bar-fg {
            background-color: #eab308; /* Kuning */
            height: 8px;
            border-radius: 5px;
        }
        .progress-label {
            font-size: 11px;
            color: #6b7280;
            text-align: right;
            margin-top: 4px;
        }

        /* Chart Card */
        .chart-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
        }
        .chart-image {
            max-width: 100%;
            height: auto;
            margin: 10px auto;
        }
        .explanation {
            margin-top: 20px;
            text-align: left;
            font-size: 13px;
            color: #374151;
            border-top: 1px solid #f3f4f6;
            padding-top: 15px;
        }
        .explanation p, .explanation li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <h1>Laporan Ringkasan Dashboard</h1>
    <p class="subtitle">Dibuat pada:                                     <?php echo date('d F Y, H:i'); ?> WIB</p>

    <table class="main-layout">
        <tr>
            <td class="stats-column">
                <table class="stats-grid">
                    <tr>
                        <td class="stats-grid-cell">
                            <div class="card card-blue">
                                <p class="card-label">Total Lokasi Parkir</p>
                                <p class="card-value"><?php echo number_format($total_locations ?? 0, 0, ',', '.') ?></p>
                            </div>
                        </td>
                        <td class="stats-grid-cell">
                            <div class="card card-purple">
                                <p class="card-label">Total Koordinator</p>
                                <p class="card-value"><?php echo number_format($total_coordinators ?? 0, 0, ',', '.') ?></p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="stats-grid-cell">
                            <div class="card card-yellow">
                                <p class="card-label">Lokasi Disurvey</p>
                                <p class="card-value"><?php echo number_format($total_surveyed_locations ?? 0, 0, ',', '.') ?></p>
                                <?php
                                    $percentage = ($total_locations > 0) ? ($total_surveyed_locations / $total_locations) * 100 : 0;
                                ?>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fg" style="width:                                                                               <?php echo round($percentage) ?>%;"></div>
                                </div>
                                <p class="progress-label"><?php echo round($percentage) ?>% Selesai</p>
                            </div>
                        </td>
                        <td class="stats-grid-cell">
                            <div class="card card-green">
                                <p class="card-label">Est. Pendapatan Harian</p>
                                <p class="card-value-money">
                                    <?php echo 'Rp ' . number_format($deposits['daily'] ?? 0, 0, ',', '.') ?>
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="stats-grid-cell">
                            <div class="card card-green">
                                <p class="card-label">Est. Pendapatan Weekend</p>
                                <p class="card-value-money">
                                    <?php echo 'Rp ' . number_format($deposits['weekend'] ?? 0, 0, ',', '.') ?>
                                </p>
                            </div>
                        </td>
                        <td class="stats-grid-cell">
                            <div class="card card-green">
                                <p class="card-label">Est. Pendapatan Bulanan</p>
                                <p class="card-value-money">
                                    <?php echo 'Rp ' . number_format($deposits['monthly'] ?? 0, 0, ',', '.') ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            <td class="chart-column">
                <div class="chart-card">
                    <h2 style="margin-top: 0; text-align: center; font-size: 18px;">Grafik Survey</h2>
                    <img src="<?php echo $chart_image_base64; ?>" class="chart-image" alt="Grafik Perbandingan Survey">

                    <div class="explanation">
                        <p style="font-weight: bold; font-size: 14px;">Analisis Data:</p>
                        <p>Rincian status survey lokasi parkir saat ini:</p>
                        <ul>
                            <li>
                                <strong>Sudah Disurvey (Kuning):</strong>
                                <?php echo number_format($chart_data['surveyed'] ?? 0, 0, ',', '.') ?> lokasi
                            </li>
                            <li>
                                <strong>Belum Disurvey (Abu-abu):</strong>
                                <?php echo number_format($chart_data['not_surveyed'] ?? 0, 0, ',', '.') ?> lokasi
                            </li>
                        </ul>
                        <p style="margin-top: 10px; font-size: 12px; color: #666;">
                            *Estimasi pendapatan dihitung berdasarkan data setoran yang telah diinput oleh surveyor.
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>