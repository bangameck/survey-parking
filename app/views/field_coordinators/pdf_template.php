<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title?></title>
    <style>
        @page { margin: 100px 25px 50px 25px; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }

        /* HEADER & FOOTER */
        header { position: fixed; top: -80px; left: 0px; right: 0px; height: 80px; text-align: center; }
        header h1 { margin: 0; font-size: 18px; text-transform: uppercase; padding-bottom: 5px; }
        header p { margin: 2px 0; font-size: 11px; }
        footer { position: fixed; bottom: -30px; left: 0px; right: 0px; height: 30px; font-size: 9px; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 5px; font-style: italic; }

        /* CONTAINER GROUP */
        .coord-group { margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; overflow: hidden; page-break-inside: avoid; }
        .coord-header { padding: 8px 12px; border-bottom: 1px solid #ccc; color: #000; }
        .coord-name { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .pks-status { float: right; font-size: 11px; font-weight: bold; }

        /* TABEL */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 10px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: middle; }
        th { background-color: #fff; font-weight: bold; color: #555; font-size: 10px; border-bottom: 2px solid #ccc; text-transform: uppercase; }
        tr:last-child td { border-bottom: none; }

        /* WARNA BACKGROUND HEADER KOORDINATOR */
        .bg-gray { background-color: #f3f4f6; }
        .bg-green { background-color: #dcfce7; color: #14532d; } .bg-green-row { background-color: #f0fdf4; }
        .bg-yellow { background-color: #fef9c3; color: #713f12; } .bg-yellow-row { background-color: #fefce8; }
        .bg-orange { background-color: #ffedd5; color: #7c2d12; } .bg-orange-row { background-color: #fff7ed; }

        /* BADGE ZONA (Style Baru) */
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            min-width: 40px;
        }
        /* Warna Badge Zona - Disesuaikan agar mirip Dashboard */
        .badge-purple { background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; } /* Zona 1 */
        .badge-blue { background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }   /* Zona 2 */
        .badge-green-badge { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; } /* Zona 3 (Beda nama class biar gak bentrok sama bg-green header) */
        .badge-gray { background-color: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }   /* Default */
    </style>
</head>
<body>

    <header>
        <h1><?php echo $title?></h1>
        <p>Rekapitulasi Data Koordinator & Lokasi Parkir (By Address)</p>
    </header>

    <footer>
        Dokumen dicetak otomatis Aplikasi Survey Parkir Pekanbaru (<?php echo $app_url?>) Pada (<?php echo $date?>)
    </footer>

    <main>
        <?php
            $today = date('Y-m-d');
            foreach ($groupedData as $coordId => $data):

                // --- LOGIKA WARNA PKS (HEADER) ---
                $pksText     = 'PKS: BELUM DIISI';
                $headerClass = 'bg-gray';
                $rowClass    = '';

                if (! empty($data['pks_expired'])) {
                    $expiry         = $data['pks_expired'];
                    $daysLeft       = (strtotime($expiry) - strtotime($today)) / (60 * 60 * 24);
                    $expiryDateIndo = date('d/m/Y', strtotime($expiry));

                    if ($daysLeft < 0) {
                        $pksText     = "STATUS: EXPIRED ($expiryDateIndo)";
                        $headerClass = 'bg-orange';
                        $rowClass    = 'bg-orange-row';
                    } elseif ($daysLeft <= 90) {
                    $sisaHari    = ceil($daysLeft);
                    $pksText     = "STATUS: MASA TENGGANG ($sisaHari Hari) - $expiryDateIndo";
                    $headerClass = 'bg-yellow';
                    $rowClass    = 'bg-yellow-row';
                } else {
                    $pksText     = "STATUS: AKTIF s.d $expiryDateIndo";
                    $headerClass = 'bg-green';
                    $rowClass    = 'bg-green-row';
                }
            }

            // Info Kontak
            $nik = ! empty($data['nik']) ? $data['nik'] : '-';
            $hp  = ! empty($data['phone_number']) ? $data['phone_number'] : '-';
        ?>

        <div class="coord-group">
            <div class="coord-header <?php echo $headerClass?>">
                <span class="coord-name"><?php echo htmlspecialchars($data['name'])?></span>
                <span style="font-size: 10px; font-weight: normal; margin-left: 10px; color: #444;">[ NIK: <?php echo $nik?> | HP: <?php echo $hp?> ]</span>
                <span class="pks-status"><?php echo $pksText?></span>
            </div>

            <?php if (empty($data['locations'])): ?>
                <div style="padding: 10px; font-style: italic; color: #777; font-size: 10px;" class="<?php echo $rowClass?>">
                    Belum ada lokasi terdaftar.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th width="5%" class="<?php echo $rowClass?>">No</th>
                            <th width="45%" class="<?php echo $rowClass?>">Alamat Lokasi</th>
                            <th width="35%" class="<?php echo $rowClass?>">Nama Titik Parkir</th>
                            <th width="15%" class="<?php echo $rowClass?>">Zona</th> </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no = 1;
                            foreach ($data['locations'] as $loc):
                                // Logika Warna Badge Zona
                                $zone       = $loc['zone'] ?? '';
                                $badgeClass = 'badge-gray';
                                if ($zone == 'Zona 1') {
                                    $badgeClass = 'badge-purple';
                                } elseif ($zone == 'Zona 2') {
                                $badgeClass = 'badge-blue';
                            } elseif ($zone == 'Zona 3') {
                                $badgeClass = 'badge-green-badge';
                            }

                        ?>
                        <tr class="<?php echo $rowClass?>">
                            <td style="text-align: center;"><?php echo $no++?></td>
                            <td><?php echo htmlspecialchars($loc['address'])?></td>
                            <td><?php echo htmlspecialchars($loc['location'])?></td>
                            <td>
                                <?php if ($zone): ?>
                                    <span class="badge <?php echo $badgeClass?>">
                                        <?php echo htmlspecialchars($zone)?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999; font-style: italic;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php endforeach; ?>
    </main>

</body>
</html>