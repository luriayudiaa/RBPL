<?php
// operator/kondisi_mesin.php
require_once __DIR__ . '/../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Cek role
if ($_SESSION['role'] !== 'operator_mesin') {
    header("Location: ../dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];
$success = '';
$error = '';

// Handle update kondisi mesin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $kondisi_baru = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    $query = "UPDATE hasil_produksi SET kondisi_mesin = '$kondisi_baru', catatan = CONCAT(catatan, ' | Update: ', '$catatan') WHERE id = $id AND user_id = $user_id";
    
    if ($conn->query($query)) {
        $success = "Status mesin berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui status: " . $conn->error;
    }
}

// Ambil semua mesin dengan kondisi tidak baik
$query = "SELECT DISTINCT nama_mesin, kondisi_mesin, COUNT(*) as jumlah, MAX(created_at) as terakhir 
          FROM hasil_produksi 
          WHERE user_id = $user_id AND kondisi_mesin != 'Baik'
          GROUP BY nama_mesin, kondisi_mesin
          ORDER BY terakhir DESC";
$result = $conn->query($query);

// Ambil riwayat kondisi mesin
$riwayat_query = "SELECT * FROM hasil_produksi 
                  WHERE user_id = $user_id AND kondisi_mesin != 'Baik'
                  ORDER BY tanggal_produksi DESC, created_at DESC 
                  LIMIT 50";
$riwayat = $conn->query($riwayat_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kondisi Mesin - SIP Pengolahan Plastik</title>
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

        .status-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-info h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .status-info p {
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

        .status-perlu-maintenance { background: #fff3cd; color: #856404; }
        .status-rusak { background: #f8d7da; color: #721c24; }

        .btn-update {
            padding: 8px 16px;
            background: #3B55FF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-update:hover {
            background: #2a3fd1;
        }

        .table-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 30px;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .modal-content h3 {
            color: #333;
            margin-bottom: 20px;
        }

        .modal-content select, .modal-content textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .modal-content button {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-simpan {
            background: #3B55FF;
            color: white;
        }

        .btn-tutup {
            background: #f5f5f5;
            color: #666;
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
                <div class="user-role">Operator Mesin</div>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="hasil_produksi.php"><i class="fas fa-cogs"></i> Hasil Produksi</a></li>
            <li><a href="riwayat_produksi.php"><i class="fas fa-history"></i> Riwayat Produksi</a></li>
            <li><a href="kondisi_mesin.php" class="active"><i class="fas fa-tools"></i> Kondisi Mesin</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="page-header">
            <h1>Kondisi Mesin</h1>
            <p>Pantau dan update kondisi mesin yang bermasalah</p>
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

        <!-- Ringkasan Mesin Bermasalah -->
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="status-card">
                <div class="status-info">
                    <h3><?php echo $row['nama_mesin']; ?></h3>
                    <p>
                        Status: 
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['kondisi_mesin'])); ?>">
                            <?php echo $row['kondisi_mesin']; ?>
                        </span>
                    </p>
                    <p>Jumlah laporan: <?php echo $row['jumlah']; ?>x</p>
                    <p>Terakhir: <?php echo date('d-m-Y H:i', strtotime($row['terakhir'])); ?></p>
                </div>
                <button class="btn-update" onclick="openModal('<?php echo $row['nama_mesin']; ?>')">
                    <i class="fas fa-edit"></i> Update Status
                </button>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="background: white; padding: 40px; border-radius: 12px; text-align: center; color: #28a745;">
                <i class="fas fa-check-circle" style="font-size: 60px; margin-bottom: 15px;"></i>
                <h3>Semua Mesin dalam Kondisi Baik</h3>
                <p>Tidak ada mesin yang perlu maintenance atau rusak saat ini.</p>
            </div>
        <?php endif; ?>

        <!-- Riwayat Kondisi Mesin -->
        <div class="table-container">
            <h3><i class="fas fa-history"></i> Riwayat Kondisi Mesin</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Mesin</th>
                        <th>Jenis</th>
                        <th>Kondisi</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($riwayat && $riwayat->num_rows > 0): ?>
                        <?php $no = 1; while($row = $riwayat->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['tanggal_produksi'])); ?></td>
                            <td><?php echo $row['nama_mesin']; ?></td>
                            <td><?php echo $row['jenis_plastik']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['kondisi_mesin'])); ?>">
                                    <?php echo $row['kondisi_mesin']; ?>
                                </span>
                            </td>
                            <td><?php echo $row['catatan'] ?: '-'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999; padding: 40px;">
                                Belum ada riwayat kondisi mesin.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Update Kondisi -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-tools"></i> Update Kondisi Mesin</h3>
            <form method="POST" action="">
                <input type="hidden" name="id" id="mesin_id">
                <select name="kondisi" required>
                    <option value="">Pilih Kondisi</option>
                    <option value="Baik">Baik (Sudah diperbaiki)</option>
                    <option value="Perlu Maintenance">Perlu Maintenance</option>
                    <option value="Rusak">Rusak</option>
                </select>
                <textarea name="catatan" rows="3" placeholder="Catatan perbaikan..." required></textarea>
                <div>
                    <button type="submit" name="update" class="btn-simpan">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" class="btn-tutup" onclick="closeModal()">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(mesin) {
            document.getElementById('updateModal').style.display = 'block';
            document.getElementById('mesin_id').value = mesin;
        }

        function closeModal() {
            document.getElementById('updateModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('updateModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>