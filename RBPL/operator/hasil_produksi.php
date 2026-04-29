<?php
// operator/hasil_produksi.php
include '../config.php';
checkLogin();
checkRole(['operator_mesin']);

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan'])) {
    $jenis_plastik = mysqli_real_escape_string($conn, $_POST['jenis_plastik']);
    $nama_mesin = mysqli_real_escape_string($conn, $_POST['nama_mesin']);
    $operator = mysqli_real_escape_string($conn, $_POST['operator']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $kondisi_mesin = mysqli_real_escape_string($conn, $_POST['kondisi_mesin']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    if(empty($jenis_plastik) || empty($nama_mesin) || empty($operator) || empty($tanggal) || empty($jumlah)) {
        $error = "Field yang bertanda * harus diisi!";
    } else {
        $query = "INSERT INTO hasil_produksi (user_id, jenis_plastik, nama_mesin, operator, tanggal_produksi, jumlah_hasil, kondisi_mesin, catatan) 
                  VALUES ('$user_id', '$jenis_plastik', '$nama_mesin', '$operator', '$tanggal', '$jumlah', '$kondisi_mesin', '$catatan')";
        
        if($conn->query($query)) {
            $success = "Data hasil produksi berhasil disimpan!";
            
            // Show warning if machine needs maintenance or broken
            if($kondisi_mesin != 'Baik') {
                $success .= " Perhatikan: Kondisi mesin perlu ditindaklanjuti.";
            }
        } else {
            $error = "Gagal menyimpan data: " . $conn->error;
        }
    }
}

// Get today's production
$today = date('Y-m-d');
$query_today = "SELECT * FROM hasil_produksi WHERE user_id = $user_id AND tanggal_produksi = '$today' ORDER BY created_at DESC";
$result_today = $conn->query($query_today);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Produksi - SIP Pengolahan Plastik</title>
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

        .form-group label .required {
            color: #ff4444;
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

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #ffeeba;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-baik { background: #d4edda; color: #155724; }
        .status-perlu-maintenance { background: #fff3cd; color: #856404; }
        .status-rusak { background: #f8d7da; color: #721c24; }
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
                <div class="user-role">Operator Mesin</div>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="hasil_produksi.php" class="active"><i class="fas fa-cogs"></i> Hasil Produksi</a></li>
            <li><a href="riwayat_produksi.php"><i class="fas fa-history"></i> Riwayat Produksi</a></li>
            <li><a href="kondisi_mesin.php"><i class="fas fa-tools"></i> Kondisi Mesin</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="page-header">
            <div>
                <h1>Hasil Produksi</h1>
                <p>Catat hasil produksi biji plastik</p>
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
            <h3><i class="fas fa-plus-circle"></i> Tambah Data Produksi</h3>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Jenis Plastik <span class="required">*</span></label>
                        <select name="jenis_plastik" required>
                            <option value="">Pilih Jenis Plastik</option>
                            <option value="PET">PET (Polyethylene Terephthalate)</option>
                            <option value="HDPE">HDPE (High-Density Polyethylene)</option>
                            <option value="PVC">PVC (Polyvinyl Chloride)</option>
                            <option value="LDPE">LDPE (Low-Density Polyethylene)</option>
                            <option value="PP">PP (Polypropylene)</option>
                            <option value="PS">PS (Polystyrene)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Mesin <span class="required">*</span></label>
                        <input type="text" name="nama_mesin" placeholder="Contoh: Extruder A1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Operator <span class="required">*</span></label>
                        <input type="text" name="operator" value="<?php echo $_SESSION['nama']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal Produksi <span class="required">*</span></label>
                        <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Jumlah Hasil (kg) <span class="required">*</span></label>
                        <input type="number" step="0.01" name="jumlah" placeholder="Masukkan jumlah" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kondisi Mesin <span class="required">*</span></label>
                        <select name="kondisi_mesin" required>
                            <option value="Baik">Baik</option>
                            <option value="Perlu Maintenance">Perlu Maintenance</option>
                            <option value="Rusak">Rusak</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Catatan</label>
                        <textarea name="catatan" rows="3" placeholder="Catatan tambahan..."></textarea>
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
            <h3><i class="fas fa-list"></i> Produksi Hari Ini</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Plastik</th>
                        <th>Mesin</th>
                        <th>Operator</th>
                        <th>Jumlah (kg)</th>
                        <th>Kondisi Mesin</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result_today->num_rows > 0): ?>
                        <?php $no = 1; while($row = $result_today->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['jenis_plastik']; ?></td>
                            <td><?php echo $row['nama_mesin']; ?></td>
                            <td><?php echo $row['operator']; ?></td>
                            <td><?php echo number_format($row['jumlah_hasil'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['kondisi_mesin'])); ?>">
                                    <?php echo $row['kondisi_mesin']; ?>
                                </span>
                            </td>
                            <td><?php echo date('H:i:s', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #999;">Belum ada data produksi hari ini</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>