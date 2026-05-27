<?php
// sopir/riwayat_pengiriman.php
require_once __DIR__ . '/../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role
if ($_SESSION['role'] !== 'sopir') {
    header("Location: ../dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total records
$total_query = "SELECT COUNT(*) as total FROM data_pengiriman WHERE user_id = $user_id";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Get data with pagination
$query = "SELECT * FROM data_pengiriman 
          WHERE user_id = $user_id 
          ORDER BY tanggal_pengiriman DESC, created_at DESC 
          LIMIT $offset, $limit";
$result = $conn->query($query);

// Get statistics
$stat_query = "SELECT 
                COUNT(*) as total_pengiriman,
                SUM(jumlah_muatan) as total_berat,
                AVG(jumlah_muatan) as rata_rata,
                SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = 'Dalam Perjalanan' THEN 1 ELSE 0 END) as dalam_perjalanan,
                SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as menunggu
               FROM data_pengiriman 
               WHERE user_id = $user_id";
$stat_result = $conn->query($stat_query);
$stats = $stat_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengiriman - SIP Pengolahan Plastik</title>
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
            margin-bottom: 5px;
        }

        .page-header p {
            color: #666;
            font-size: 14px;
        }

        .btn-tambah {
            padding: 10px 20px;
            background: #3B55FF;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-tambah:hover {
            background: #2a3fd1;
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: #3B55FF;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            color: #666;
            font-size: 13px;
        }

        .table-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table-container h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f5f5f5;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-menunggu { background: #fff3cd; color: #856404; }
        .status-dalam-perjalanan { background: #cce5ff; color: #004085; }
        .status-selesai { background: #d4edda; color: #155724; }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            background: #f5f5f5;
            color: #666;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
            font-size: 14px;
        }

        .pagination a:hover {
            background: #3B55FF;
            color: white;
        }

        .pagination .active {
            background: #3B55FF;
            color: white;
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
                <div class="user-role">Sopir</div>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="data_pengiriman.php"><i class="fas fa-truck"></i> Data Pengiriman</a></li>
            <li><a href="riwayat_pengiriman.php" class="active"><i class="fas fa-history"></i> Riwayat Pengiriman</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="page-header">
            <div>
                <h1>Riwayat Pengiriman</h1>
                <p>Daftar lengkap pengiriman yang pernah Anda lakukan</p>
            </div>
            <a href="data_pengiriman.php" class="btn-tambah">
                <i class="fas fa-plus"></i> Tambah Data
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_pengiriman'] ?? 0; ?></div>
                <div class="stat-label">Total Pengiriman</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_berat'] ?? 0, 2); ?> kg</div>
                <div class="stat-label">Total Berat</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['selesai'] ?? 0; ?></div>
                <div class="stat-label">Selesai</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['dalam_perjalanan'] ?? 0; ?></div>
                <div class="stat-label">Dalam Perjalanan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['menunggu'] ?? 0; ?></div>
                <div class="stat-label">Menunggu</div>
            </div>
        </div>

        <div class="table-container">
            <h3><i class="fas fa-history"></i> Riwayat Pengiriman</h3>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Tujuan</th>
                        <th>Perusahaan</th>
                        <th>Jenis</th>
                        <th>Jumlah (kg)</th>
                        <th>Status</th>
                        <th>Waktu Input</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows > 0): ?>
                        <?php $no = $offset + 1; while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['tanggal_pengiriman'])); ?></td>
                            <td><?php echo $row['tujuan']; ?></td>
                            <td><?php echo $row['perusahaan']; ?></td>
                            <td><?php echo $row['jenis_plastik']; ?></td>
                            <td><?php echo number_format($row['jumlah_muatan'], 2); ?> kg</td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d-m-Y H:i:s', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #999; padding: 40px;">
                                <i class="fas fa-info-circle" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                                Belum ada data pengiriman. Silakan tambah data di menu Data Pengiriman.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if($i == $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>">Next <i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
