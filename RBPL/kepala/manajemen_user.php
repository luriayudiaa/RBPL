<?php
// kepala/manajemen_user.php
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

// Handle add/edit user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['simpan'])) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $nama_user = mysqli_real_escape_string($conn, $_POST['nama']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        
        // Cek username sudah ada atau belum
        $check_query = "SELECT id FROM users WHERE username = '$username'";
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $check_query .= " AND id != " . (int)$_POST['id'];
        }
        $check_result = $conn->query($check_query);
        
        if ($check_result->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // Update
                $id = (int)$_POST['id'];
                if (!empty($password)) {
                    $query = "UPDATE users SET 
                              username = '$username',
                              password = '$password',
                              nama = '$nama_user',
                              role = '$role'
                              WHERE id = $id";
                } else {
                    $query = "UPDATE users SET 
                              username = '$username',
                              nama = '$nama_user',
                              role = '$role'
                              WHERE id = $id";
                }
                $msg = "Data user berhasil diperbarui!";
            } else {
                // Insert
                $query = "INSERT INTO users (username, password, nama, role) 
                          VALUES ('$username', '$password', '$nama_user', '$role')";
                $msg = "User baru berhasil ditambahkan!";
            }
            
            if ($conn->query($query)) {
                $success = $msg;
            } else {
                $error = "Gagal menyimpan data: " . $conn->error;
            }
        }
    }
    
    if (isset($_POST['hapus'])) {
        $id = (int)$_POST['id'];
        
        // Cek jangan sampai hapus diri sendiri
        if ($id == $_SESSION['user_id']) {
            $error = "Tidak dapat menghapus akun sendiri!";
        } else {
            $query = "DELETE FROM users WHERE id = $id";
            if ($conn->query($query)) {
                $success = "User berhasil dihapus!";
            } else {
                $error = "Gagal menghapus user: " . $conn->error;
            }
        }
    }
}

// Get all users
$query = "SELECT * FROM users ORDER BY role, nama ASC";
$result = $conn->query($query);

// Get user for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_query = "SELECT * FROM users WHERE id = $id";
    $edit_result = $conn->query($edit_query);
    $edit_data = $edit_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - SIP Pengolahan Plastik</title>
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .role-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .role-penyortir { background: #cce5ff; color: #004085; }
        .role-sopir { background: #fff3cd; color: #856404; }
        .role-operator_mesin { background: #d4edda; color: #155724; }
        .role-kepala_produksi { background: #f8d7da; color: #721c24; }

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

        .warning-text {
            color: #ff4444;
            font-size: 12px;
            margin-top: 5px;
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
            <li><a href="data_pelanggan.php"><i class="fas fa-building"></i> Data Pelanggan</a></li>
            <li><a href="generate_laporan.php"><i class="fas fa-file-pdf"></i> Generate Laporan</a></li>
            <li><a href="manajemen_user.php" class="active"><i class="fas fa-users-cog"></i> Manajemen User</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="page-header">
            <div>
                <h1>Manajemen User</h1>
                <p>Kelola akun pengguna sistem</p>
            </div>
            <a href="?tambah=1" class="btn-tambah">
                <i class="fas fa-plus"></i> Tambah User
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
        <!-- Form Tambah/Edit User -->
        <div class="form-container">
            <h3>
                <i class="fas <?php echo $edit_data ? 'fa-edit' : 'fa-user-plus'; ?>"></i>
                <?php echo $edit_data ? 'Edit User' : 'Tambah User Baru'; ?>
            </h3>
            <form method="POST" action="">
                <?php if($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" 
                               value="<?php echo $edit_data ? $edit_data['username'] : ''; ?>" 
                               placeholder="Masukkan username" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password <?php echo $edit_data ? '(Kosongkan jika tidak diubah)' : ''; ?></label>
                        <input type="password" name="password" 
                               placeholder="<?php echo $edit_data ? '********' : 'Masukkan password'; ?>" 
                               <?php echo $edit_data ? '' : 'required'; ?>>
                        <?php if($edit_data): ?>
                            <div class="warning-text">*Kosongkan jika tidak ingin mengubah password</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" 
                               value="<?php echo $edit_data ? $edit_data['nama'] : ''; ?>" 
                               placeholder="Masukkan nama lengkap" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="penyortir" <?php echo ($edit_data && $edit_data['role'] == 'penyortir') ? 'selected' : ''; ?>>Penyortir</option>
                            <option value="sopir" <?php echo ($edit_data && $edit_data['role'] == 'sopir') ? 'selected' : ''; ?>>Sopir</option>
                            <option value="operator_mesin" <?php echo ($edit_data && $edit_data['role'] == 'operator_mesin') ? 'selected' : ''; ?>>Operator Mesin</option>
                            <option value="kepala_produksi" <?php echo ($edit_data && $edit_data['role'] == 'kepala_produksi') ? 'selected' : ''; ?>>Kepala Produksi</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <button type="submit" name="simpan" class="btn-simpan">
                        <i class="fas fa-save"></i> <?php echo $edit_data ? 'Update User' : 'Simpan User'; ?>
                    </button>
                    <a href="manajemen_user.php" class="btn-batal">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Daftar User -->
        <div class="table-container">
            <h3><i class="fas fa-list"></i> Daftar User</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows > 0): ?>
                        <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['nama']; ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $row['role']; ?>">
                                    <?php 
                                    switch($row['role']) {
                                        case 'penyortir': echo 'Penyortir'; break;
                                        case 'sopir': echo 'Sopir'; break;
                                        case 'operator_mesin': echo 'Operator Mesin'; break;
                                        case 'kepala_produksi': echo 'Kepala Produksi'; break;
                                        default: echo $row['role'];
                                    }
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <?php if($row['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="hapus" class="btn-hapus">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999; padding: 40px;">
                                <i class="fas fa-info-circle" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                                Belum ada data user.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>