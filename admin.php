<?php
// Konfigurasi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "undangan_khitan";

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Menghitung total data untuk ringkasan
$query_all = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku_tamu");
$total_tamu = mysqli_fetch_assoc($query_all)['total'];

$query_hadir = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku_tamu WHERE kehadiran = 'Hadir'");
$total_hadir = mysqli_fetch_assoc($query_hadir)['total'];

$query_tidak = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku_tamu WHERE kehadiran = 'Tidak Hadir'");
$total_tidak = mysqli_fetch_assoc($query_tidak)['total'];

// Mengambil semua data tamu diurutkan dari yang terbaru (id terbesar/terakhir masuk)
$query_data = mysqli_query($koneksi, "SELECT * FROM buku_tamu ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Buku Tamu Khitanan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
        }

        /* Navbar / Header */
        .header {
            background-color: #1f306e; /* Biru Navy sesuai tema */
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #c5a059; /* Warna Emas */
        }

        .container {
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Kartu Ringkasan (Dashboard Cards) */
        .summary-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            flex: 1;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-bottom: 4px solid #ddd;
            text-align: center;
        }

        .card h3 {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .card .angka {
            font-size: 36px;
            font-weight: 700;
            color: #1f306e;
        }

        .card.card-hadir { border-bottom-color: #28a745; }
        .card.card-hadir .angka { color: #28a745; }
        
        .card.card-tidak { border-bottom-color: #dc3545; }
        .card.card-tidak .angka { color: #dc3545; }

        /* Tabel Data Tamu */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow-x: auto;
        }

        .table-container h2 {
            margin-bottom: 20px;
            font-size: 20px;
            color: #1f306e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        table td {
            font-size: 14px;
            color: #444;
        }

        table tr:hover {
            background-color: #f9fbfd;
        }

        /* Label Badge Kehadiran */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-hadir {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-tidak {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Responsif untuk HP */
        @media (max-width: 768px) {
            .summary-cards {
                flex-direction: column;
            }
            .header {
                padding: 15px 20px;
            }
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Admin Khitanan</h1>
        <p style="font-size: 14px;">Data Real-time Buku Tamu</p>
    </div>

    <div class="container">
        <div class="summary-cards">
            <div class="card">
                <h3>Total Form Masuk</h3>
                <div class="angka"><?= $total_tamu; ?></div>
            </div>
            <div class="card card-hadir">
                <h3>Total Akan Hadir</h3>
                <div class="angka"><?= $total_hadir; ?></div>
            </div>
            <div class="card card-tidak">
                <h3>Tidak Bisa Hadir</h3>
                <div class="angka"><?= $total_tidak; ?></div>
            </div>
        </div>

        <div class="table-container">
            <h2>Daftar Detail Tamu & Ucapan</h2>
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Nama Lengkap</th>
                        <th width="12%">No. Telepon</th>
                        <th width="20%">Alamat</th>
                        <th width="25%">Do'a & Ucapan</th>
                        <th width="10%">Status</th>
                        <th width="13%">Waktu Isi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if(mysqli_num_rows($query_data) > 0) {
                        while($row = mysqli_fetch_assoc($query_data)) { 
                            // Mengatur warna badge berdasarkan kehadiran
                            $badge_class = ($row['kehadiran'] == 'Hadir') ? 'badge-hadir' : 'badge-tidak';
                            
                            // Mengubah format waktu menjadi lebih rapi
                            $waktu = date('d-m-Y H:i', strtotime($row['waktu_submit']));
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td style="font-weight: 600; color: #1f306e;"><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= htmlspecialchars($row['telepon']); ?></td>
                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                        <td style="font-style: italic;">"<?= htmlspecialchars($row['ucapan']); ?>"</td>
                        <td>
                            <span class="badge <?= $badge_class; ?>">
                                <?= $row['kehadiran']; ?>
                            </span>
                        </td>
                        <td style="font-size: 12px; color: #888;"><?= $waktu; ?></td>
                    </tr>
                    <?php 
                        } 
                    } else {
                    ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #888;">
                            Belum ada tamu yang mengisi buku tamu.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>