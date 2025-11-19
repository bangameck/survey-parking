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
            background-color: #f9fafb; /* Latar belakang abu-abu muda */
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

        /* Layout Grid 2x2 untuk Statistik */
        .stats-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px; /* Jarak antar 'card' */
        }
        .stats-grid-cell {
            width: 50%;
            vertical-align: top;
        }

        /* Ini adalah style untuk 'card' statistik PUTIH yang baru */
        .card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb; /* Border abu-abu tipis */
            border-radius: 16px; /* Ini adalah rounded-2xl versi CSS */
            padding: 25px;
            height: 130px;
            overflow: hidden; /* Mencegah konten keluar */
            /* Efek shadow tidak didukung baik, jadi kita pakai border */
        }

        /* Aksen warna "smooth" menggunakan border-top tebal */
        .card-blue { border-top: 5px solid #3b82f6; }
        .card-purple { border-top: 5px solid #8b5cf6; }
        .card-green { border-top: 5px solid #22c55e; }
        .card-green-dark { border-top: 5px solid #16a34a; } /* Warna lain untuk pendapatan */

        .card-label {
            font-size: 15px;
            color: #6b7280;
            margin: 0 0 10px 0;
        }
        .card-value {
            font-size: 34px;
            font-weight: bold;
            color: #111827;
            margin: 10px 0 0 0;
            padding: 0;
        }
        .card-value-money {
            font-size: 30px; /* Lebih kecil agar pas */
            font-weight: bold;
            color: #16a34a; /* Hijau tua */
            margin: 10px 0 0 0;
        }

        /* Progress Bar (untuk kartu Lokasi Disurvey) */
        .progress-bar-bg {
            width: 100%;
            background-color: #e5e7eb;
            border-radius: 5px;
            height: 10px;
            margin-top: 15px;
        }
        .progress-bar-fg {
            background-color: #22c55e; /* Warna hijau */
            height: 10px;
            border-radius: 5px;
        }
        .progress-label {
            font-size: 12px;
            color: #6b7280;
            text-align: right;
            margin-top: 5px;
        }

        /* Kartu Chart (juga putih dan rounded) */
        .chart-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 25px;
            margin-top: 15px;
            text-align: center;
        }
        .chart-image {
            max-width: 450px;
            height: auto;
            margin: 0 auto;
        }
        .explanation {
            margin-top: 25px;
            text-align: left;
            font-size: 14px;
            color: #374151;
            border-top: 1px solid #f3f4f6;
            padding-top: 20px;
        }
        .explanation p, .explanation li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <h1>Laporan Ringkasan Dashboard</h1>
    <p class="subtitle">Dibuat pada:                                     <?php echo date('d F Y, H:i'); ?> WIB</p>

    <table class="stats-grid">
        <tr>
            <td class="stats-grid-cell">
                <table class="card card-blue">
                    <tr><td>
                        <p class="card-label">Total Lokasi Parkir</p>
                        <p class="card-value"><?php echo number_format($total_locations ?? 0, 0, ',', '.') ?></p>
                    </td></tr>
                </table>
            </td>
            <td class="stats-grid-cell">
                <table class="card card-purple">
                    <tr><td>
                        <p class="card-label">Total Koordinator</p>
                        <p class="card-value"><?php echo number_format($total_coordinators ?? 0, 0, ',', '.') ?></p>
                    </td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="stats-grid-cell">
                <table class="card card-green">
                    <tr><td>
                        <p class="card-label">Lokasi Disurvey</p>
                        <p class="card-value"><?php echo number_format($total_surveyed_locations ?? 0, 0, ',', '.') ?></p>
                        <?php
                            $percentage = ($total_locations > 0) ? ($total_surveyed_locations / $total_locations) * 100 : 0;
                        ?>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fg" style="width:                                                                       <?php echo round($percentage) ?>%;"></div>
                        </div>
                        <p class="progress-label"><?php echo round($percentage) ?>% Selesai</p>
                    </td></tr>
                </table>
            </td>
            <td class="stats-grid-cell">
                <table class="card card-green-dark">
                    <tr><td>
                        <p class="card-label">Estimasi Pendapatan</p>
                        <p class="card-value-money">
                            <?php echo 'Rp ' . number_format($grand_total_deposits ?? 0, 0, ',', '.') ?>
                        </p>
                    </td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="stats-grid">
        <tr>
            <td colspan="2"> <table class="chart-card">
                    <tr><td>
                        <h2 style="margin-top: 0; text-align: center;">Perbandingan Survey</h2>
                        <img src="<?php echo $chart_image_base64; ?>" class="chart-image" alt="Grafik Perbandingan Survey">

                        <div class="explanation">
                            <p style="font-weight: bold; font-size: 16px;">Penjelasan Grafik:</p>
                            <p>
                                Grafik ini memvisualisasikan proporsi lokasi yang telah disurvey dibandingkan dengan yang masih menunggu untuk disurvey.
                            </p>
                            <ul>
                                <li>
                                    <strong>Sudah Disurvey (Hijau):</strong>
                                    <strong><?php echo number_format($chart_data['surveyed'] ?? 0, 0, ',', '.') ?></strong> lokasi
                                    (<?php echo round($percentage) ?>%)
                                </li>
                                <li>
                                    <strong>Belum Disurvey (Abu-abu):</strong>
                                    <strong><?php echo number_format($chart_data['not_surveyed'] ?? 0, 0, ',', '.') ?></strong> lokasi
                                    (<?php echo 100 - round($percentage) ?>%)
                                </li>
                            </ul>
                            <p>
                                Data ini sangat penting untuk melacak progres dan efektivitas tim surveyor di lapangan serta untuk memproyeksikan potensi pendapatan.
                            </p>
                        </div>
                    </td></tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>