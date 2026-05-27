<?php
// penyortir/hasil_sortir.php
require_once __DIR__ . '/../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role
if ($_SESSION['role'] !== 'penyortir') {
    header("Location: ../dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan'])) {
    $jenis_plastik = mysqli_real_escape_string($conn, $_POST['jenis_plastik']);
    $kualitas = mysqli_real_escape_string($conn, $_POST['kualitas']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    
    if (empty($jenis_plastik) || empty($kualitas) || empty($jumlah) || empty($tanggal)) {
        $error = "Semua field harus diisi!";
    } else {
        $query = "INSERT INTO hasil_sortir (user_id, jenis_plastik, kualitas, jumlah_layak_olah, tanggal) 
                  VALUES ('$user_id', '$jenis_plastik', '$kualitas', '$jumlah', '$tanggal')";
        
        if ($conn->query($query)) {
            $success = "Data hasil sortir berhasil disimpan!";
            // Redirect ke halaman yang sama setelah sukses
            header("Refresh:2; url=hasil_sortir.php");
        } else {
            $error = "Gagal menyimpan data: " . $conn->error;
        }
    }
}

// Ambil semua data
$query_all = "SELECT * FROM hasil_sortir WHERE user_id = $user_id ORDER BY tanggal DESC, created_at DESC";
$result_all = $conn->query($query_all);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Sortir - SIP Pengolahan Plastik</title>
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

        .btn-riwayat {
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

        .btn-riwayat:hover {
            background: #2a3fd1;
            transform: translateY(-2px);
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
            display: flex;
            align-items: center;
            gap: 10px;
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

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #3B55FF;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59,85,255,0.1);
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
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-simpan:hover {
            background: #2a3fd1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59,85,255,0.3);
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
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-A { background: #d4edda; color: #155724; }
        .status-B { background: #fff3cd; color: #856404; }
        .status-C { background: #ffe5d0; color: #fd7e14; }
        .status-D { background: #f8d7da; color: #721c24; }
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
                <div class="user-role">Penyortir</div>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="hasil_sortir.php" class="active"><i class="fas fa-sort-amount-down"></i> Hasil Sortir</a></li>
            <li><a href="riwayat_sortir.php"><i class="fas fa-history"></i> Riwayat Sortir</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="page-header">
            <div>
                <h1>Hasil Sortir</h1>
                <p>Catat hasil penyortiran plastik</p>
            </div>
            <a href="riwayat_sortir.php" class="btn-riwayat">
                <i class="fas fa-history"></i> Lihat Riwayat
            </a>
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
            <h3><i class="fas fa-plus-circle"></i> Tambah Data Sortir Baru</h3>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Jenis Plastik</label>
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
                        <label>Kualitas</label>
                        <select name="kualitas" required>
                            <option value="">Pilih Kualitas</option>
                            <option value="A">A - Sangat Baik</option>
                            <option value="B">B - Baik</option>
                            <option value="C">C - Cukup</option>
                            <option value="D">D - Kurang</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Jumlah (kg)</label>
                        <input type="number" step="0.01" name="jumlah" placeholder="Masukkan jumlah" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div>
                    <button type="submit" name="simpan" class="btn-simpan">
                        <i class="fas fa-save"></i> Simpan Data
                    </button>
                    <button type="reset" class="btn-batal">
                        <i class="fas fa-times"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h3><i class="fas fa-list"></i> Data Sortir Terbaru</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Plastik</th>
                        <th>Kualitas</th>
                        <th>Jumlah (kg)</th>
                        <th>Tanggal</th>
                        <th>Waktu Input</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result_all && $result_all->num_rows > 0): ?>
                        <?php $no = 1; while($row = $result_all->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['jenis_plastik']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $row['kualitas']; ?>">
                                    <?php echo $row['kualitas']; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($row['jumlah_layak_olah'], 2); ?> kg</td>
                            <td><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                            <td><?php echo date('H:i:s', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999; padding: 40px;">
                                <i class="fas fa-info-circle" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                                Belum ada data sortir. Silakan tambah data baru di atas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
