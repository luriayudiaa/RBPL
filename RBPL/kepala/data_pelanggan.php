<?php
// kepala/data_pelanggan.php
require_once __DIR__ . '/../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Cek role
if ($_SESSION['role'] !== 'kepala_produksi') {
    header("Location: ../dashboard.php");
    exit();
}

$nama = $_SESSION['nama'];
$success = '';
$error = '';

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['simpan'])) {
        $nama_perusahaan = mysqli_real_escape_string($conn, $_POST['nama_perusahaan']);
        $nama_kontak = mysqli_real_escape_string($conn, $_POST['nama_kontak']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $id = (int)$_POST['id'];
            $query = "UPDATE data_pelanggan SET 
                      nama_perusahaan = '$nama_perusahaan',
                      nama_kontak = '$nama_kontak',
                      email = '$email',
                      telepon = '$telepon',
                      alamat = '$alamat'
                      WHERE id = $id";
            $msg = "Data pelanggan berhasil diperbarui!";
        } else {
            // Insert
            $query = "INSERT INTO data_pelanggan (nama_perusahaan, nama_kontak, email, telepon, alamat) 
                      VALUES ('$nama_perusahaan', '$nama_kontak', '$email', '$telepon', '$alamat')";
            $msg = "Data pelanggan berhasil ditambahkan!";
        }
        
        if ($conn->query($query)) {
            $success = $msg;
        } else {
            $error = "Gagal menyimpan data: " . $conn->error;
        }
    }
    
    if (isset($_POST['hapus'])) {
        $id = (int)$_POST['id'];
        $query = "DELETE FROM data_pelanggan WHERE id = $id";
        
        if ($conn->query($query)) {
            $success = "Data pelanggan berhasil dihapus!";
        } else {
            $error = "Gagal menghapus data: " . $conn->error;
        }
    }
}

// Get all customers
$query = "SELECT * FROM data_pelanggan ORDER BY nama_perusahaan ASC";
$result = $conn->query($query);

// Get customer for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_query = "SELECT * FROM data_pelanggan WHERE id = $id";
    $edit_result = $conn->query($edit_query);
    $edit_data = $edit_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan - SIP Pengolahan Plastik</title>
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
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

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus, .form-group textarea:focus {
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
        }

        .btn-simpan:hover {
            background: #2a3fd1;
            transform: translateY(-2px);
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
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
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

        .btn-edit {
            padding: 5px 10px;
            background: #ffc107;
            color: #333;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-hapus {
            padding: 5px 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            border: none;
        }

        .btn-hapus:hover {
            background: #c82333;
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
            <li><a href="data_pelanggan.php" class="active"><i class="fas fa-building"></i> Data Pelanggan</a></li>
            <li><a href="generate_laporan.php"><i class="fas fa-file-pdf"></i> Generate Laporan</a></li>
            <li><a href="manajemen_user.php"><i class="fas fa-users-cog"></i> Manajemen User</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="page-header">
            <div>
                <h1>Data Pelanggan</h1>
                <p>Kelola data pelanggan perusahaan</p>
            </div>
            <a href="?tambah=1" class="btn-tambah">
                <i class="fas fa-plus"></i> Tambah Pelanggan
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

        <?php if(isset($_GET['tambah']) || $edit_data): ?>
        <!-- Form Tambah/Edit Pelanggan -->
        <div class="form-container">
            <h3>
                <i class="fas <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
                <?php echo $edit_data ? 'Edit Data Pelanggan' : 'Tambah Pelanggan Baru'; ?>
            </h3>
            <form method="POST" action="">
                <?php if($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Perusahaan</label>
                        <input type="text" name="nama_perusahaan" 
                               value="<?php echo $edit_data ? $edit_data['nama_perusahaan'] : ''; ?>" 
                               placeholder="Masukkan nama perusahaan" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Kontak</label>
                        <input type="text" name="nama_kontak" 
                               value="<?php echo $edit_data ? $edit_data['nama_kontak'] : ''; ?>" 
                               placeholder="Masukkan nama kontak person" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" 
                               value="<?php echo $edit_data ? $edit_data['email'] : ''; ?>" 
                               placeholder="contoh@email.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" name="telepon" 
                               value="<?php echo $edit_data ? $edit_data['telepon'] : ''; ?>" 
                               placeholder="021-5550123" required>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Alamat</label>
                        <textarea name="alamat" rows="3" placeholder="Alamat lengkap perusahaan" required><?php echo $edit_data ? $edit_data['alamat'] : ''; ?></textarea>
                    </div>
                </div>
                
                <div>
                    <button type="submit" name="simpan" class="btn-simpan">
                        <i class="fas fa-save"></i> <?php echo $edit_data ? 'Update Data' : 'Simpan Data'; ?>
                    </button>
                    <a href="data_pelanggan.php" class="btn-batal">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Daftar Pelanggan -->
        <div class="table-container">
            <h3><i class="fas fa-list"></i> Daftar Pelanggan</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Perusahaan</th>
                        <th>Kontak Person</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows > 0): ?>
                        <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['nama_perusahaan']; ?></td>
                            <td><?php echo $row['nama_kontak']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['telepon']; ?></td>
                            <td><?php echo $row['alamat']; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="hapus" class="btn-hapus">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #999; padding: 40px;">
                                <i class="fas fa-info-circle" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                                Belum ada data pelanggan. Silakan tambah data pelanggan baru.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>