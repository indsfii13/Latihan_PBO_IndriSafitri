<?php
require_once 'config/database.php';
require_once 'classes/Tiket.php';
require_once 'classes/TiketRegular.php';
require_once 'classes/TiketIMAX.php';
require_once 'classes/TiketVelvet.php';

// Koneksi database
$database = new Database();
$db = $database->getConnection();

// Ambil keyword pencarian dari URL (GET)
$keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Query dengan filter pencarian
if (!empty($keyword)) {
    $query = "SELECT * FROM tabel_tiket 
              WHERE nama_film LIKE :keyword 
                 OR jenis_studio LIKE :keyword 
              ORDER BY jenis_studio, jadwal_tayang";
    $stmt = $db->prepare($query);
    $stmt->execute(['keyword' => '%' . $keyword . '%']);
} else {
    $query = "SELECT * FROM tabel_tiket ORDER BY jenis_studio, jadwal_tayang";
    $stmt = $db->prepare($query);
    $stmt->execute();
}
$dataTiket = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fungsi untuk mengkonversi data menjadi objek sesuai jenis studio
function buatObjekTiket($data) {
    switch($data['jenis_studio']) {
        case 'Regular':
            return new TiketRegular(
                $data['id_tiket'],
                $data['nama_film'],
                $data['jadwal_tayang'],
                $data['jumlah_kursi'],
                $data['harga_dasar_tiket'],
                $data['tipe_audio'],
                $data['lokasi_baris']
            );
        case 'IMAX':
            return new TiketIMAX(
                $data['id_tiket'],
                $data['nama_film'],
                $data['jadwal_tayang'],
                $data['jumlah_kursi'],
                $data['harga_dasar_tiket'],
                $data['kacamata_3d_id'],
                $data['efek_gerak_fitur']
            );
        case 'Velvet':
            return new TiketVelvet(
                $data['id_tiket'],
                $data['nama_film'],
                $data['jadwal_tayang'],
                $data['jumlah_kursi'],
                $data['harga_dasar_tiket'],
                $data['bantal_selimut_pack'],
                $data['layanan_butler']
            );
        default:
            return null;
    }
}

// Kelompokkan data berdasarkan jenis studio
$kelompokTiket = [
    'Regular' => [],
    'IMAX' => [],
    'Velvet' => []
];

foreach($dataTiket as $row) {
    $objek = buatObjekTiket($row);
    if($objek) {
        $kelompokTiket[$row['jenis_studio']][] = $objek;
    }
}

// Hitung total data
$totalData = count($dataTiket);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Tiket Bioskop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            color: #eee;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #e94560;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        /* ===== SEARCH BOX ===== */
        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 10px;
            flex-wrap: wrap;
        }
        .search-container form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .search-container input[type="text"] {
            padding: 12px 20px;
            border-radius: 30px;
            border: none;
            width: 350px;
            max-width: 80vw;
            font-size: 16px;
            background: #0f3460;
            color: #fff;
            outline: 2px solid #e94560;
        }
        .search-container input[type="text"]::placeholder {
            color: #888;
        }
        .search-container button {
            padding: 12px 25px;
            border-radius: 30px;
            border: none;
            background: #e94560;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .search-container button:hover {
            background: #c73652;
        }
        .search-container .reset-btn {
            background: #555;
        }
        .search-container .reset-btn:hover {
            background: #333;
        }
        .search-info {
            text-align: center;
            color: #aaa;
            margin-bottom: 20px;
            font-size: 0.95em;
        }
        .search-info strong {
            color: #e94560;
        }

        /* ===== STUDIO SECTION ===== */
        .studio-section {
            background: #16213e;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 5px solid #e94560;
        }
        .studio-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e94560;
        }
        .studio-header h2 {
            color: #e94560;
            font-size: 1.8em;
        }
        .studio-header .badge {
            background: #e94560;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }

        /* ===== CARD TIKET ===== */
        .grid-tiket {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .card-tiket {
            background: #0f3460;
            border-radius: 10px;
            padding: 15px;
            transition: transform 0.3s;
            border: 1px solid #1a4a6e;
        }
        .card-tiket:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }
        .card-tiket h3 {
            color: #e94560;
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        .card-tiket .info {
            margin: 5px 0;
            font-size: 0.95em;
            color: #ccc;
        }
        .card-tiket .info strong {
            color: #fff;
        }
        .card-tiket .fasilitas {
            background: #1a1a2e;
            padding: 10px;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 0.9em;
            color: #8ecae6;
        }
        .card-tiket .total-harga {
            background: #e94560;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
            color: #fff;
            margin-top: 5px;
        }
        .empty-message {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 20px;
        }
        .no-result {
            text-align: center;
            color: #e94560;
            font-size: 1.2em;
            padding: 30px;
        }

        @media (max-width: 768px) {
            .grid-tiket {
                grid-template-columns: 1fr;
            }
            h1 {
                font-size: 1.8em;
            }
            .search-container input[type="text"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 Sistem Manajemen Tiket Bioskop</h1>

        <!-- ===== SEARCH BOX ===== -->
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Cari film atau studio..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit">🔍 Cari</button>
                <?php if (!empty($keyword)): ?>
                    <a href="index.php" class="reset-btn" style="padding:12px 25px; border-radius:30px; background:#555; color:#fff; text-decoration:none; font-weight:bold;">↺ Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- ===== INFO PENCARIAN ===== -->
        <div class="search-info">
            <?php if (!empty($keyword)): ?>
                Menampilkan <strong><?= $totalData ?></strong> hasil untuk pencarian "<strong><?= htmlspecialchars($keyword) ?></strong>"
            <?php else: ?>
                Total <strong><?= $totalData ?></strong> tiket tersedia
            <?php endif; ?>
        </div>

        <!-- ===== TAMPILKAN TIAP STUDIO ===== -->
        <?php 
        $studioList = ['Regular', 'IMAX', 'Velvet'];
        $icons = ['Regular' => '🎞️', 'IMAX' => '🎥', 'Velvet' => '✨'];
        
        foreach($studioList as $jenis): 
            $jumlahTiket = count($kelompokTiket[$jenis]);
        ?>
        <div class="studio-section">
            <div class="studio-header">
                <h2><?= $icons[$jenis] . ' Studio ' . $jenis ?></h2>
                <span class="badge"><?= $jumlahTiket ?> Tiket</span>
            </div>
            
            <div class="grid-tiket">
                <?php if($jumlahTiket > 0): ?>
                    <?php foreach($kelompokTiket[$jenis] as $tiket): ?>
                    <div class="card-tiket">
                        <h3><?= htmlspecialchars($tiket->getNamaFilm()) ?></h3>
                        <div class="info">
                            <strong>ID Tiket:</strong> <?= $tiket->getIdTiket() ?>
                        </div>
                        <div class="info">
                            <strong>Jadwal:</strong> <?= date('d M Y H:i', strtotime($tiket->getJadwalTayang())) ?>
                        </div>
                        <div class="info">
                            <strong>Jumlah Kursi:</strong> <?= $tiket->getJumlahKursi() ?>
                        </div>
                        <div class="info">
                            <strong>Harga Dasar:</strong> Rp <?= number_format($tiket->getHargaDasar(), 0, ',', '.') ?>
                        </div>
                        
                        <div class="fasilitas">
                            <strong>🎯 Fasilitas:</strong><br>
                            <?= $tiket->tampilkanInfoFasilitas() ?>
                        </div>
                        
                        <div class="total-harga">
                            💰 Total: Rp <?= number_format($tiket->hitungTotalHarga(), 0, ',', '.') ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-message">Tidak ada tiket untuk studio ini</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- ===== PESAN KALAU TIDAK ADA HASIL ===== -->
        <?php if($totalData == 0 && !empty($keyword)): ?>
            <div class="no-result">
                😔 Tidak ditemukan tiket dengan kata kunci "<strong><?= htmlspecialchars($keyword) ?></strong>"
            </div>
        <?php endif; ?>

        <footer style="text-align: center; margin-top: 40px; color: #666; font-size: 0.9em;">
            <p>© 2026 - Sistem Manajemen Tiket Bioskop | Praktikum PBO</p>
        </footer>
    </div>
</body>
</html>