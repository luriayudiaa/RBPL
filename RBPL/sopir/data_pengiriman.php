<?php
// sopir/data_pengiriman.php
include '../config.php';
checkLogin();
checkRole(['sopir']);

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan'])) {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $perusahaan = mysqli_real_escape_string($conn, $_POST['perusahaan']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $jenis_plastik = mysqli_real_escape_string($conn, $_POST['jenis_plastik']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    if(empty($tanggal) || empty($tujuan) || empty($perusahaan) || empty($jumlah) || empty($jenis_plastik)) {
        $error = "Semua field harus diisi!";
    } else {
        $query = "INSERT INTO data_pengiriman (user_id, tanggal_pengiriman, tujuan, perusahaan, jumlah_muatan, jenis_plastik, status) 
                  VALUES ('$user_id', '$tanggal', '$tujuan', '$perusahaan', '$jumlah', '$jenis_plastik', '$status')";
        
        if($conn->query($query)) {
            $success = "Data pengiriman berhasil disimpan!";
        } else {
            $error = "Gagal menyimpan data: " . $conn->error;
        }
    }
}

// Get pending deliveries
$query_pending = "SELECT * FROM data_pengiriman WHERE user_id = $user_id AND status != 'Selesai' ORDER BY tanggal_pengiriman DESC";
$result_pending = $conn->query($query_pending);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengiriman - SIP Pengolahan Plastik</title>
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

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-container h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #3B55FF;
            outline: none;
        }

        .btn-simpan {
            padding: 12px 30px;
            background: #3B55FF;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-simpan:hover {
            background: #2a3fd1;
        }

        .btn-batal {
            padding: 12px 30px;
            background: #f5f5f5;
            color: #666;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-batal:hover {
            background: #e0e0e0;
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

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
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
                <div class="user-role">Sopir</div>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="data_pengiriman.php" class="active"><i class="fas fa-truck"></i> Data Pengiriman</a></li>
            <li><a href="riwayat_pengiriman.php"><i class="fas fa-history"></i> Riwayat Pengiriman</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="page-header">
            <div>
                <h1>Data Pengiriman</h1>
                <p>Catat data pengiriman plastik</p>
            </div>
        </div>

        <?php if($success): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
        <?php endif; ?>

        <?php if($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="form-container">
            <h3><i class="fas fa-plus-circle"></i> Tambah Pengiriman Baru</h3>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tanggal Pengiriman</label>
                        <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tujuan</label>
                        <input type="text" name="tujuan" placeholder="Kota tujuan" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Perusahaan</label>
                        <input type="text" name="perusahaan" placeholder="Nama perusahaan tujuan" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Jumlah Muatan (kg)</label>
                        <input type="number" step="0.01" name="jumlah" placeholder="Masukkan jumlah" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Jenis Plastik</label>
                        <select name="jenis_plastik" required>
                            <option value="">Pilih Jenis Plastik</option>
                            <option value="PET">PET</option>
                            <option value="HDPE">HDPE</option>
                            <option value="PVC">PVC</option>
                            <option value="LDPE">LDPE</option>
                            <option value="PP">PP</option>
                            <option value="PS">PS</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Menunggu">Menunggu</option>
                            <option value="Dalam Perjalanan">Dalam Perjalanan</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="simpan" class="btn-simpan">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="reset" class="btn-batal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h3><i class="fas fa-truck-loading"></i> Pengiriman Aktif</h3>
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
                    </tr>
                </thead>
                <tbody>
                    <?php if($result_pending->num_rows > 0): ?>
                        <?php $no = 1; while($row = $result_pending->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['tanggal_pengiriman'])); ?></td>
                            <td><?php echo $row['tujuan']; ?></td>
                            <td><?php echo $row['perusahaan']; ?></td>
                            <td><?php echo $row['jenis_plastik']; ?></td>
                            <td><?php echo number_format($row['jumlah_muatan'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #999;">Tidak ada pengiriman aktif</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>