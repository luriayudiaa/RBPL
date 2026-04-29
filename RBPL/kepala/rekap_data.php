<?php
// kepala/rekap_data.php
include '../config.php';
checkLogin();
checkRole(['kepala_produksi']);

$filter_tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
$filter_tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'semua';

// Get summary data
$query_sortir = "SELECT 
                    jenis_plastik,
                    SUM(jumlah_layak_olah) as total,
                    COUNT(*) as jumlah_transaksi
                 FROM hasil_sortir 
                 WHERE tanggal BETWEEN '$filter_tanggal_awal' AND '$filter_tanggal_akhir'
                 GROUP BY jenis_plastik";
$result_sortir = $conn->query($query_sortir);

$query_pengiriman = "SELECT 
                        status,
                        COUNT(*) as jumlah,
                        SUM(jumlah_muatan) as total_muatan
                     FROM data_pengiriman 
                     WHERE tanggal_pengiriman BETWEEN '$filter_tanggal_awal' AND '$filter_tanggal_akhir'
                     GROUP BY status";
$result_pengiriman = $conn->query($query_pengiriman);

$query_produksi = "SELECT 
                      jenis_plastik,
                      SUM(jumlah_hasil) as total,
                      kondisi_mesin,
                      COUNT(*) as jumlah_produksi
                   FROM hasil_produksi 
                   WHERE tanggal_produksi BETWEEN '$filter_tanggal_awal' AND '$filter_tanggal_akhir'
                   GROUP BY jenis_plastik, kondisi_mesin";
$result_produksi = $conn->query($query_produksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Data - SIP Pengolahan Plastik</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f5f5;
        }

        .navbar {
            background: white;
            padding: 0 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand i {
            font-size: 24px;
            color: #3B55FF;
        }

        .navbar-brand h2 {
            color: #333;
            font-size: 18px;
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-detail {
            text-align: right;
        }

        .user-name {
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            color: #666;
            font-size: 12px;
        }

        .btn-logout {
            padding: 8px 20px;
            background: #ff4444;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn-logout:hover {
            background: #cc0000;
        }

        .sidebar {
            width: 280px;
            background: white;
            position: fixed;
            left: 0;
            top: 70px;
            bottom: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 30px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            gap: 15px;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #3B55FF;
            color: white;
        }

        .sidebar-menu a i {
            width: 20px;
        }

        .content {
            margin-left: 280px;
            margin-top: 70px;
            padding: 30px;
            min-height: calc(100vh - 70px);
        }

        .page-header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            color: #333;
            font-size: 24px;
        }

        .page-header p {
            color: #666;
            font-size: 14px;
        }

        .filter-container {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .filter-group input, .filter-group select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-filter {
            padding: 10px 25px;
            background: #3B55FF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-filter:hover {
            background: #2a3fd1;
        }

        .rekap-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .rekap-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .rekap-section h3 i {
            color: #3B55FF;
        }

        .rekap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .rekap-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #3B55FF;
        }

        .rekap-card h4 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .rekap-card .total {
            color: #333;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .rekap-card .detail {
            color: #999;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-recycle"></i>
            <h2>SIP Pengolahan Plastik</h2>
        </div>
        <div class="user-info">
            <div class="user-detail">
                <div class="user-name"><?php echo $_SESSION['nama']; ?></div>
                <div class="user-role">Kepala Produksi</div>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="rekap_data.php" class="active"><i class="fas fa-chart-bar"></i> Rekap Data</a></li>
            <li><a href="koreksi_data.php"><i class="fas fa-edit"></i> Koreksi Data</a></li>
            <li><a href="data_pelanggan.php"><i class="fas fa-building"></i> Data Pelanggan</a></li>
            <li><a href="generate_laporan.php"><i class="fas fa-file-pdf"></i> Generate Laporan</a></li>
            <li><a href="manajemen_user.php"><i class="fas fa-users-cog"></i> Manajemen User</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="page-header">
            <div>
                <h1>Rekap Data Produksi</h1>
                <p>Lihat ringkasan data produksi periode tertentu</p>
            </div>
        </div>

        <div class="filter-container">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" value="<?php echo $filter_tanggal_awal; ?>">
                </div>
                <div class="filter-group">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" value="<?php echo $filter_tanggal_akhir; ?>">
                </div>
                <div class="filter-group">
                    <label>Jenis Data</label>
                    <select name="jenis">
                        <option value="semua" <?php echo $filter_jenis == 'semua' ? 'selected' : ''; ?>>Semua Data</option>
                        <option value="sortir" <?php echo $filter_jenis == 'sortir' ? 'selected' : ''; ?>>Hasil Sortir</option>
                        <option value="pengiriman" <?php echo $filter_jenis == 'pengiriman' ? 'selected' : ''; ?>>Data Pengiriman</option>
                        <option value="produksi" <?php echo $filter_jenis == 'produksi' ? 'selected' : ''; ?>>Hasil Produksi</option>
                    </select>
                </div>
                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> Tampilkan
                </button>
            </form>
        </div>

        <?php if($filter_jenis == 'semua' || $filter_jenis == 'sortir'): ?>
        <div class="rekap-section">
            <h3><i class="fas fa-recycle"></i> Rekap Hasil Sortir</h3>
            <table>
                <thead>
                    <tr>
                        <th>Jenis Plastik</th>
                        <th>Total (kg)</th>
                        <th>Jumlah Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_sortir_all = 0;
                    if($result_sortir->num_rows > 0): 
                        while($row = $result_sortir->fetch_assoc()): 
                            $total_sortir_all += $row['total'];
                    ?>
                    <tr>
                        <td><?php echo $row['jenis_plastik']; ?></td>
                        <td><?php echo number_format($row['total'], 2); ?> kg</td>
                        <td><?php echo $row['jumlah_transaksi']; ?> kali</td>
                    </tr>
                    <?php endwhile; ?>
                    <tr style="background: #f5f5f5; font-weight: 600;">
                        <td>TOTAL</td>
                        <td><?php echo number_format($total_sortir_all, 2); ?> kg</td>
                        <td></td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #999;">Tidak ada data sortir pada periode ini</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if($filter_jenis == 'semua' || $filter_jenis == 'pengiriman'): ?>
        <div class="rekap-section">
            <h3><i class="fas fa-truck"></i> Rekap Data Pengiriman</h3>
            <div class="rekap-grid">
                <?php 
                $total_pengiriman_all = 0;
                if($result_pengiriman->num_rows > 0): 
                    while($row = $result_pengiriman->fetch_assoc()): 
                        $total_pengiriman_all += $row['total_muatan'];
                ?>
                <div class="rekap-card">
                    <h4>Status: <?php echo $row['status']; ?></h4>
                    <div class="total"><?php echo number_format($row['total_muatan'], 2); ?> kg</div>
                    <div class="detail"><?php echo $row['jumlah']; ?> kali pengiriman</div>
                </div>
                <?php endwhile; ?>
                <div class="rekap-card" style="border-left-color: #28a745;">
                    <h4>TOTAL SEMUA</h4>
                    <div class="total"><?php echo number_format($total_pengiriman_all, 2); ?> kg</div>
                </div>
                <?php else: ?>
                <p style="color: #999; text-align: center; grid-column: 1/-1;">Tidak ada data pengiriman pada periode ini</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($filter_jenis == 'semua' || $filter_jenis == 'produksi'): ?>
        <div class="rekap-section">
            <h3><i class="fas fa-industry"></i> Rekap Hasil Produksi</h3>
            <table>
                <thead>
                    <tr>
                        <th>Jenis Plastik</th>
                        <th>Kondisi Mesin</th>
                        <th>Jumlah Produksi</th>
                        <th>Total (kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_produksi_all = 0;
                    if($result_produksi->num_rows > 0): 
                        while($row = $result_produksi->fetch_assoc()): 
                            $total_produksi_all += $row['total'];
                    ?>
                    <tr>
                        <td><?php echo $row['jenis_plastik']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['kondisi_mesin'])); ?>">
                                <?php echo $row['kondisi_mesin']; ?>
                            </span>
                        </td>
                        <td><?php echo $row['jumlah_produksi']; ?> batch</td>
                        <td><?php echo number_format($row['total'], 2); ?> kg</td>
                    </tr>
                    <?php endwhile; ?>
                    <tr style="background: #f5f5f5; font-weight: 600;">
                        <td colspan="3">TOTAL</td>
                        <td><?php echo number_format($total_produksi_all, 2); ?> kg</td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">Tidak ada data produksi pada periode ini</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>