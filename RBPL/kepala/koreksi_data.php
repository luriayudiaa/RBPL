<?php
// kepala/koreksi_data.php
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
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'sortir';

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id']) && isset($_GET['type'])) {
    $id = (int)$_GET['id'];
    $type = $_GET['type'];
    
    switch($type) {
        case 'sortir':
            $query = "DELETE FROM hasil_sortir WHERE id = $id";
            break;
        case 'pengiriman':
            $query = "DELETE FROM data_pengiriman WHERE id = $id";
            break;
        case 'produksi':
            $query = "DELETE FROM hasil_produksi WHERE id = $id";
            break;
        default:
            $query = "";
    }
    
    if ($query && $conn->query($query)) {
        $success = "Data berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data: " . $conn->error;
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $type = $_POST['type'];
    
    switch($type) {
        case 'sortir':
            $jenis_plastik = mysqli_real_escape_string($conn, $_POST['jenis_plastik']);
            $kualitas = mysqli_real_escape_string($conn, $_POST['kualitas']);
            $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
            $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
            
            $query = "UPDATE hasil_sortir SET 
                      jenis_plastik = '$jenis_plastik',
                      kualitas = '$kualitas',
                      jumlah_layak_olah = '$jumlah',
                      tanggal = '$tanggal'
                      WHERE id = $id";
            break;
            
        case 'pengiriman':
            $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
            $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);
            $perusahaan = mysqli_real_escape_string($conn, $_POST['perusahaan']);
            $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
            $jenis_plastik = mysqli_real_escape_string($conn, $_POST['jenis_plastik']);
            $status = mysqli_real_escape_string($conn, $_POST['status']);
            
            $query = "UPDATE data_pengiriman SET 
                      tanggal_pengiriman = '$tanggal',
                      tujuan = '$tujuan',
                      perusahaan = '$perusahaan',
                      jumlah_muatan = '$jumlah',
                      jenis_plastik = '$jenis_plastik',
                      status = '$status'
                      WHERE id = $id";
            break;
            
        case 'produksi':
            $jenis_plastik = mysqli_real_escape_string($conn, $_POST['jenis_plastik']);
            $nama_mesin = mysqli_real_escape_string($conn, $_POST['nama_mesin']);
            $operator = mysqli_real_escape_string($conn, $_POST['operator']);
            $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
            $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
            $kondisi_mesin = mysqli_real_escape_string($conn, $_POST['kondisi_mesin']);
            $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
            
            $query = "UPDATE hasil_produksi SET 
                      jenis_plastik = '$jenis_plastik',
                      nama_mesin = '$nama_mesin',
                      operator = '$operator',
                      tanggal_produksi = '$tanggal',
                      jumlah_hasil = '$jumlah',
                      kondisi_mesin = '$kondisi_mesin',
                      catatan = '$catatan'
                      WHERE id = $id";
            break;
    }
    
    if (isset($query) && $conn->query($query)) {
        $success = "Data berhasil diperbarui!";
    } else if (isset($query)) {
        $error = "Gagal memperbarui data: " . $conn->error;
    }
}

// Get data for display
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

switch($active_tab) {
    case 'sortir':
        $total_query = "SELECT COUNT(*) as total FROM hasil_sortir";
        $total_result = $conn->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total_records = $total_row['total'];
        $total_pages = ceil($total_records / $limit);
        
        $query = "SELECT hs.*, u.nama as user_nama 
                  FROM hasil_sortir hs
                  JOIN users u ON hs.user_id = u.id
                  ORDER BY hs.tanggal DESC, hs.created_at DESC 
                  LIMIT $offset, $limit";
        $result = $conn->query($query);
        break;
        
    case 'pengiriman':
        $total_query = "SELECT COUNT(*) as total FROM data_pengiriman";
        $total_result = $conn->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total_records = $total_row['total'];
        $total_pages = ceil($total_records / $limit);
        
        $query = "SELECT dp.*, u.nama as user_nama 
                  FROM data_pengiriman dp
                  JOIN users u ON dp.user_id = u.id
                  ORDER BY dp.tanggal_pengiriman DESC, dp.created_at DESC 
                  LIMIT $offset, $limit";
        $result = $conn->query($query);
        break;
        
    case 'produksi':
        $total_query = "SELECT COUNT(*) as total FROM hasil_produksi";
        $total_result = $conn->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total_records = $total_row['total'];
        $total_pages = ceil($total_records / $limit);
        
        $query = "SELECT hp.*, u.nama as user_nama 
                  FROM hasil_produksi hp
                  JOIN users u ON hp.user_id = u.id
                  ORDER BY hp.tanggal_produksi DESC, hp.created_at DESC 
                  LIMIT $offset, $limit";
        $result = $conn->query($query);
        break;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koreksi Data - SIP Pengolahan Plastik</title>
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

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            background: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .tab-link {
            padding: 12px 25px;
            background: #f5f5f5;
            color: #666;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .tab-link:hover {
            background: #e0e0e0;
        }

        .tab-link.active {
            background: #3B55FF;
            color: white;
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
            transition: all 0.3s;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete {
            padding: 5px 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background: #c82333;
        }

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
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .modal-content h3 {
            color: #333;
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
            padding: 12px 25px;
            background: #3B55FF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-tutup {
            padding: 12px 25px;
            background: #f5f5f5;
            color: #666;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
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
        
        .status-menunggu { background: #fff3cd; color: #856404; }
        .status-dalam-perjalanan { background: #cce5ff; color: #004085; }
        .status-selesai { background: #d4edda; color: #155724; }
        
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
            <li><a href="koreksi_data.php" class="active"><i class="fas fa-edit"></i> Koreksi Data</a></li>
            <li><a href="data_pelanggan.php"><i class="fas fa-building"></i> Data Pelanggan</a></li>
            <li><a href="generate_laporan.php"><i class="fas fa-file-pdf"></i> Generate Laporan</a></li>
            <li><a href="manajemen_user.php"><i class="fas fa-users-cog"></i> Manajemen User</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="page-header">
            <h1>Koreksi Data</h1>
            <p>Edit atau hapus data yang tidak valid</p>
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

        <div class="tabs">
            <a href="?tab=sortir" class="tab-link <?php echo $active_tab == 'sortir' ? 'active' : ''; ?>">
                <i class="fas fa-recycle"></i> Hasil Sortir
            </a>
            <a href="?tab=pengiriman" class="tab-link <?php echo $active_tab == 'pengiriman' ? 'active' : ''; ?>">
                <i class="fas fa-truck"></i> Data Pengiriman
            </a>
            <a href="?tab=produksi" class="tab-link <?php echo $active_tab == 'produksi' ? 'active' : ''; ?>">
                <i class="fas fa-industry"></i> Hasil Produksi
            </a>
        </div>

        <div class="table-container">
            <h3>
                <i class="fas fa-list"></i> 
                <?php 
                    echo $active_tab == 'sortir' ? 'Hasil Sortir' : 
                         ($active_tab == 'pengiriman' ? 'Data Pengiriman' : 'Hasil Produksi'); 
                ?>
            </h3>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <?php if($active_tab == 'sortir'): ?>
                            <th>Jenis</th>
                            <th>Kualitas</th>
                            <th>Jumlah (kg)</th>
                            <th>Tanggal</th>
                        <?php elseif($active_tab == 'pengiriman'): ?>
                            <th>Tanggal</th>
                            <th>Tujuan</th>
                            <th>Perusahaan</th>
                            <th>Jenis</th>
                            <th>Jumlah (kg)</th>
                            <th>Status</th>
                        <?php elseif($active_tab == 'produksi'): ?>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Mesin</th>
                            <th>Operator</th>
                            <th>Jumlah (kg)</th>
                            <th>Kondisi</th>
                        <?php endif; ?>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['user_nama']; ?></td>
                            
                            <?php if($active_tab == 'sortir'): ?>
                                <td><?php echo $row['jenis_plastik']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['kualitas']; ?>">
                                        <?php echo $row['kualitas']; ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($row['jumlah_layak_olah'], 2); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                            
                            <?php elseif($active_tab == 'pengiriman'): ?>
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
                            
                            <?php elseif($active_tab == 'produksi'): ?>
                                <td><?php echo date('d-m-Y', strtotime($row['tanggal_produksi'])); ?></td>
                                <td><?php echo $row['jenis_plastik']; ?></td>
                                <td><?php echo $row['nama_mesin']; ?></td>
                                <td><?php echo $row['operator']; ?></td>
                                <td><?php echo number_format($row['jumlah_hasil'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['kondisi_mesin'])); ?>">
                                        <?php echo $row['kondisi_mesin']; ?>
                                    </span>
                                </td>
                            <?php endif; ?>
                            
                            <td>
                                <button class="btn-edit" onclick="editData(<?php echo htmlspecialchars(json_encode($row)); ?>, '<?php echo $active_tab; ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="?tab=<?php echo $active_tab; ?>&delete=1&id=<?php echo $row['id']; ?>&type=<?php echo $active_tab; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center; color: #999; padding: 40px;">
                                <i class="fas fa-info-circle" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                                Tidak ada data untuk ditampilkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if(isset($total_pages) && $total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?tab=<?php echo $active_tab; ?>&page=<?php echo $page-1; ?>"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if($i == $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?tab=<?php echo $active_tab; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="?tab=<?php echo $active_tab; ?>&page=<?php echo $page+1; ?>">Next <i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Edit Data -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-edit"></i> Edit Data</h3>
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="type" id="edit_type">
                
                <div id="edit_fields"></div>
                
                <div>
                    <button type="submit" name="update" class="btn-simpan">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <button type="button" class="btn-tutup" onclick="closeModal()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editData(data, type) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_type').value = type;
            
            let fields = '';
            
            if(type == 'sortir') {
                fields = `
                    <div class="form-group">
                        <label>Jenis Plastik</label>
                        <select name="jenis_plastik" required>
                            <option value="PET" ${data.jenis_plastik == 'PET' ? 'selected' : ''}>PET</option>
                            <option value="HDPE" ${data.jenis_plastik == 'HDPE' ? 'selected' : ''}>HDPE</option>
                            <option value="PVC" ${data.jenis_plastik == 'PVC' ? 'selected' : ''}>PVC</option>
                            <option value="LDPE" ${data.jenis_plastik == 'LDPE' ? 'selected' : ''}>LDPE</option>
                            <option value="PP" ${data.jenis_plastik == 'PP' ? 'selected' : ''}>PP</option>
                            <option value="PS" ${data.jenis_plastik == 'PS' ? 'selected' : ''}>PS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kualitas</label>
                        <select name="kualitas" required>
                            <option value="A" ${data.kualitas == 'A' ? 'selected' : ''}>A - Sangat Baik</option>
                            <option value="B" ${data.kualitas == 'B' ? 'selected' : ''}>B - Baik</option>
                            <option value="C" ${data.kualitas == 'C' ? 'selected' : ''}>C - Cukup</option>
                            <option value="D" ${data.kualitas == 'D' ? 'selected' : ''}>D - Kurang</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah (kg)</label>
                        <input type="number" step="0.01" name="jumlah" value="${data.jumlah_layak_olah}" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="${data.tanggal}" required>
                    </div>
                `;
            } else if(type == 'pengiriman') {
                fields = `
                    <div class="form-group">
                        <label>Tanggal Pengiriman</label>
                        <input type="date" name="tanggal" value="${data.tanggal_pengiriman}" required>
                    </div>
                    <div class="form-group">
                        <label>Tujuan</label>
                        <input type="text" name="tujuan" value="${data.tujuan}" required>
                    </div>
                    <div class="form-group">
                        <label>Perusahaan</label>
                        <input type="text" name="perusahaan" value="${data.perusahaan}" required>
                    </div>
                    <div class="form-group">
                        <label>Jumlah (kg)</label>
                        <input type="number" step="0.01" name="jumlah" value="${data.jumlah_muatan}" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Plastik</label>
                        <select name="jenis_plastik" required>
                            <option value="PET" ${data.jenis_plastik == 'PET' ? 'selected' : ''}>PET</option>
                            <option value="HDPE" ${data.jenis_plastik == 'HDPE' ? 'selected' : ''}>HDPE</option>
                            <option value="PVC" ${data.jenis_plastik == 'PVC' ? 'selected' : ''}>PVC</option>
                            <option value="LDPE" ${data.jenis_plastik == 'LDPE' ? 'selected' : ''}>LDPE</option>
                            <option value="PP" ${data.jenis_plastik == 'PP' ? 'selected' : ''}>PP</option>
                            <option value="PS" ${data.jenis_plastik == 'PS' ? 'selected' : ''}>PS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Menunggu" ${data.status == 'Menunggu' ? 'selected' : ''}>Menunggu</option>
                            <option value="Dalam Perjalanan" ${data.status == 'Dalam Perjalanan' ? 'selected' : ''}>Dalam Perjalanan</option>
                            <option value="Selesai" ${data.status == 'Selesai' ? 'selected' : ''}>Selesai</option>
                        </select>
                    </div>
                `;
            } else if(type == 'produksi') {
                fields = `
                    <div class="form-group">
                        <label>Jenis Plastik</label>
                        <select name="jenis_plastik" required>
                            <option value="PET" ${data.jenis_plastik == 'PET' ? 'selected' : ''}>PET</option>
                            <option value="HDPE" ${data.jenis_plastik == 'HDPE' ? 'selected' : ''}>HDPE</option>
                            <option value="PVC" ${data.jenis_plastik == 'PVC' ? 'selected' : ''}>PVC</option>
                            <option value="LDPE" ${data.jenis_plastik == 'LDPE' ? 'selected' : ''}>LDPE</option>
                            <option value="PP" ${data.jenis_plastik == 'PP' ? 'selected' : ''}>PP</option>
                            <option value="PS" ${data.jenis_plastik == 'PS' ? 'selected' : ''}>PS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Mesin</label>
                        <input type="text" name="nama_mesin" value="${data.nama_mesin}" required>
                    </div>
                    <div class="form-group">
                        <label>Operator</label>
                        <input type="text" name="operator" value="${data.operator}" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Produksi</label>
                        <input type="date" name="tanggal" value="${data.tanggal_produksi}" required>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Hasil (kg)</label>
                        <input type="number" step="0.01" name="jumlah" value="${data.jumlah_hasil}" required>
                    </div>
                    <div class="form-group">
                        <label>Kondisi Mesin</label>
                        <select name="kondisi_mesin" required>
                            <option value="Baik" ${data.kondisi_mesin == 'Baik' ? 'selected' : ''}>Baik</option>
                            <option value="Perlu Maintenance" ${data.kondisi_mesin == 'Perlu Maintenance' ? 'selected' : ''}>Perlu Maintenance</option>
                            <option value="Rusak" ${data.kondisi_mesin == 'Rusak' ? 'selected' : ''}>Rusak</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" rows="3">${data.catatan || ''}</textarea>
                    </div>
                `;
            }
            
            document.getElementById('edit_fields').innerHTML = fields;
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>