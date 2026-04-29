<?php
// kepala/generate_laporan.php
include '../config.php';
checkLogin();
checkRole(['kepala_produksi']);

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    $jenis_laporan = $_POST['jenis_laporan'];
    $periode_awal = $_POST['periode_awal'];
    $periode_akhir = $_POST['periode_akhir'];
    
    // Save to database
    $user_id = $_SESSION['user_id'];
    $query = "INSERT INTO laporan_produksi (user_id, jenis_laporan, periode_awal, periode_akhir) 
              VALUES ('$user_id', '$jenis_laporan', '$periode_awal', '$periode_akhir')";
    
    if($conn->query($query)) {
        $success = "Laporan berhasil digenerate! Silakan download.";
    } else {
        $error = "Gagal menyimpan laporan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Laporan - SIP Pengolahan Plastik</title>
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

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #3B55FF;
            outline: none;
        }

        .btn-generate {
            width: 100%;
            padding: 14px;
            background: #3B55FF;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-generate:hover {
            background: #2a3fd1;
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

        .info-box {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #3B55FF;
        }

        .info-box h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .info-box p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
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
            <li><a href="rekap_data.php"><i class="fas fa-chart-bar"></i> Rekap Data</a></li>
            <li><a href="koreksi_data.php"><i class="fas fa-edit"></i> Koreksi Data</a></li>
            <li><a href="data_pelanggan.php"><i class="fas fa-building"></i> Data Pelanggan</a></li>
            <li><a href="generate_laporan.php" class="active"><i class="fas fa-file-pdf"></i> Generate Laporan</a></li>
            <li><a href="manajemen_user.php"><i class="fas fa-users-cog"></i> Manajemen User</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="page-header">
            <h1>Generate Laporan Produksi</h1>
            <p>Buat laporan produksi berdasarkan periode tertentu</p>
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
            <form method="POST" action="">
                <div class="form-group">
                    <label>Jenis Laporan</label>
                    <select name="jenis_laporan" required>
                        <option value="">Pilih Jenis Laporan</option>
                        <option value="Lengkap">Laporan Lengkap</option>
                        <option value="Sortir">Laporan Sortir</option>
                        <option value="Pengiriman">Laporan Pengiriman</option>
                        <option value="Produksi">Laporan Produksi</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Periode Awal</label>
                    <input type="date" name="periode_awal" value="<?php echo date('Y-m-01'); ?>" required>
                </div>

                <div class="form-group">
                    <label>Periode Akhir</label>
                    <input type="date" name="periode_akhir" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <button type="submit" name="generate" class="btn-generate">
                    <i class="fas fa-file-pdf"></i> Generate Laporan
                </button>
            </form>

            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Informasi</h4>
                <p>Laporan akan digenerate dalam format PDF yang mencakup:<br>
                - Data sortir berdasarkan jenis plastik<br>
                - Data pengiriman dan statusnya<br>
                - Data produksi dan kondisi mesin<br>
                - Grafik dan ringkasan statistik</p>
            </div>
        </div>
    </div>
</body>
</html>