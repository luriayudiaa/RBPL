<?php
// dashboard.php - Letakkan di ROOT folder
require_once 'config.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$nama = $_SESSION['nama'];
$user_id = $_SESSION['user_id'];

// Get statistics based on role
$total_sortir = 0;
$total_pengiriman = 0;
$total_produksi = 0;
$total_pelanggan = 0;

if($role == 'penyortir') {
    $result = $conn->query("SELECT COALESCE(SUM(jumlah_layak_olah), 0) as total FROM hasil_sortir WHERE user_id = $user_id");
    if($row = $result->fetch_assoc()) $total_sortir = $row['total'];
} elseif($role == 'sopir') {
    $result = $conn->query("SELECT COUNT(*) as total FROM data_pengiriman WHERE user_id = $user_id");
    if($row = $result->fetch_assoc()) $total_pengiriman = $row['total'];
} elseif($role == 'operator_mesin') {
    $result = $conn->query("SELECT COALESCE(SUM(jumlah_hasil), 0) as total FROM hasil_produksi WHERE user_id = $user_id");
    if($row = $result->fetch_assoc()) $total_produksi = $row['total'];
} elseif($role == 'kepala_produksi') {
    $result = $conn->query("SELECT COALESCE(SUM(jumlah_layak_olah), 0) as total FROM hasil_sortir");
    if($row = $result->fetch_assoc()) $total_sortir = $row['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM data_pengiriman");
    if($row = $result->fetch_assoc()) $total_pengiriman = $row['total'];
    
    $result = $conn->query("SELECT COALESCE(SUM(jumlah_hasil), 0) as total FROM hasil_produksi");
    if($row = $result->fetch_assoc()) $total_produksi = $row['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM data_pelanggan");
    if($row = $result->fetch_assoc()) $total_pelanggan = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIP Pengolahan Plastik</title>
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
            background: #f0f2f5;
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
            gap: 12px;
        }

        .navbar-brand i {
            font-size: 28px;
            color: #3B55FF;
        }

        .navbar-brand h2 {
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .user-info {
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
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
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
            padding: 15px 30px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            gap: 15px;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: #f0f2f5;
            color: #3B55FF;
            border-left: 4px solid #3B55FF;
        }

        .sidebar-menu a.active {
            background: #3B55FF;
            color: white;
            border-left: 4px solid #2a3fd1;
        }

        .sidebar-menu a i {
            width: 20px;
            font-size: 18px;
        }

        .content {
            margin-left: 280px;
            margin-top: 70px;
            padding: 30px;
            min-height: calc(100vh - 70px);
        }

        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .welcome-card h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .welcome-card p {
            color: #666;
            font-size: 14px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #3B55FF 0%, #2a3fd1 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon i {
            font-size: 35px;
            color: white;
        }

        .stat-info h3 {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .stat-info p {
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        .section-title {
            margin-bottom: 20px;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border-color: #3B55FF;
        }

        .menu-card i {
            font-size: 50px;
            color: #3B55FF;
            margin-bottom: 20px;
        }

        .menu-card h4 {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .menu-card p {
            color: #666;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 240px;
            }
            .content {
                margin-left: 240px;
                padding: 20px;
            }
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
                <div class="user-name"><?php echo htmlspecialchars($nama); ?></div>
                <div class="user-role"><?php echo ucwords(str_replace('_', ' ', $role)); ?></div>
            </div>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            
            <?php if($role == 'penyortir'): ?>
                <li><a href="penyortir/hasil_sortir.php"><i class="fas fa-sort-amount-down"></i> Hasil Sortir</a></li>
                <li><a href="penyortir/riwayat_sortir.php"><i class="fas fa-history"></i> Riwayat Sortir</a></li>
            
            <?php elseif($role == 'sopir'): ?>
                <li><a href="sopir/data_pengiriman.php"><i class="fas fa-truck"></i> Data Pengiriman</a></li>
                <li><a href="sopir/riwayat_pengiriman.php"><i class="fas fa-history"></i> Riwayat Pengiriman</a></li>
            
            <?php elseif($role == 'operator_mesin'): ?>
                <li><a href="operator/hasil_produksi.php"><i class="fas fa-cogs"></i> Hasil Produksi</a></li>
                <li><a href="operator/riwayat_produksi.php"><i class="fas fa-history"></i> Riwayat Produksi</a></li>
                <li><a href="operator/kondisi_mesin.php"><i class="fas fa-tools"></i> Kondisi Mesin</a></li>
            
            <?php elseif($role == 'kepala_produksi'): ?>
                <li><a href="kepala/rekap_data.php"><i class="fas fa-chart-bar"></i> Rekap Data</a></li>
                <li><a href="kepala/koreksi_data.php"><i class="fas fa-edit"></i> Koreksi Data</a></li>
                <li><a href="kepala/data_pelanggan.php"><i class="fas fa-building"></i> Data Pelanggan</a></li>
                <li><a href="kepala/generate_laporan.php"><i class="fas fa-file-pdf"></i> Generate Laporan</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="content">
        <div class="welcome-card">
            <h1>Selamat Datang, <?php echo htmlspecialchars($nama); ?>!</h1>
            <p>Anda login sebagai <?php echo ucwords(str_replace('_', ' ', $role)); ?></p>
        </div>

        <div class="stats-grid">
            <?php if($role == 'penyortir'): ?>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Sortir Saya</h3>
                        <p><?php echo number_format($total_sortir, 0, ',', '.'); ?> kg</p>
                    </div>
                </div>
            
            <?php elseif($role == 'sopir'): ?>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pengiriman Saya</h3>
                        <p><?php echo $total_pengiriman; ?> kali</p>
                    </div>
                </div>
            
            <?php elseif($role == 'operator_mesin'): ?>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Produksi Saya</h3>
                        <p><?php echo number_format($total_produksi, 0, ',', '.'); ?> kg</p>
                    </div>
                </div>
            
            <?php elseif($role == 'kepala_produksi'): ?>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Sortir</h3>
                        <p><?php echo number_format($total_sortir, 0, ',', '.'); ?> kg</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pengiriman</h3>
                        <p><?php echo $total_pengiriman; ?> kali</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Produksi</h3>
                        <p><?php echo number_format($total_produksi, 0, ',', '.'); ?> kg</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pelanggan</h3>
                        <p><?php echo $total_pelanggan; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <h3 class="section-title">Menu Cepat</h3>
        <div class="menu-grid">
            <?php if($role == 'penyortir'): ?>
                <a href="penyortir/hasil_sortir.php" class="menu-card">
                    <i class="fas fa-sort-amount-down"></i>
                    <h4>Hasil Sortir</h4>
                    <p>Catat hasil penyortiran plastik</p>
                </a>
                <a href="penyortir/riwayat_sortir.php" class="menu-card">
                    <i class="fas fa-history"></i>
                    <h4>Riwayat Sortir</h4>
                    <p>Lihat riwayat sortir Anda</p>
                </a>
            
            <?php elseif($role == 'sopir'): ?>
                <a href="sopir/data_pengiriman.php" class="menu-card">
                    <i class="fas fa-truck"></i>
                    <h4>Data Pengiriman</h4>
                    <p>Catat data pengiriman plastik</p>
                </a>
                <a href="sopir/riwayat_pengiriman.php" class="menu-card">
                    <i class="fas fa-history"></i>
                    <h4>Riwayat Pengiriman</h4>
                    <p>Lihat riwayat pengiriman</p>
                </a>
            
            <?php elseif($role == 'operator_mesin'): ?>
                <a href="operator/hasil_produksi.php" class="menu-card">
                    <i class="fas fa-cogs"></i>
                    <h4>Hasil Produksi</h4>
                    <p>Catat hasil produksi</p>
                </a>
                <a href="operator/riwayat_produksi.php" class="menu-card">
                    <i class="fas fa-history"></i>
                    <h4>Riwayat Produksi</h4>
                    <p>Lihat riwayat produksi</p>
                </a>
                <a href="operator/kondisi_mesin.php" class="menu-card">
                    <i class="fas fa-tools"></i>
                    <h4>Kondisi Mesin</h4>
                    <p>Laporkan kondisi mesin</p>
                </a>
            
            <?php elseif($role == 'kepala_produksi'): ?>
                <a href="kepala/rekap_data.php" class="menu-card">
                    <i class="fas fa-chart-bar"></i>
                    <h4>Rekap Data</h4>
                    <p>Lihat ringkasan data produksi</p>
                </a>
                <a href="kepala/koreksi_data.php" class="menu-card">
                    <i class="fas fa-edit"></i>
                    <h4>Koreksi Data</h4>
                    <p>Perbaiki data operasional</p>
                </a>
                <a href="kepala/data_pelanggan.php" class="menu-card">
                    <i class="fas fa-building"></i>
                    <h4>Data Pelanggan</h4>
                    <p>Kelola data pelanggan</p>
                </a>
                <a href="kepala/generate_laporan.php" class="menu-card">
                    <i class="fas fa-file-pdf"></i>
                    <h4>Generate Laporan</h4>
                    <p>Buat laporan produksi</p>
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
