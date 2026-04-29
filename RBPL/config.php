<?php
// config.php - Letakkan di ROOT folder
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_pengolahan_plastik');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Function to check login
function checkLogin() {
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Function to check role - PERBAIKAN: cek role dengan ketat
function checkRole($allowed_roles) {
    if(!isset($_SESSION['role'])) {
        header("Location: ../login.php");
        exit();
    }
    
    $current_role = $_SESSION['role'];
    if(!in_array($current_role, $allowed_roles)) {
        // Jika role tidak sesuai, redirect ke dashboard masing-masing
        switch($current_role) {
            case 'penyortir':
                header("Location: ../penyortir/hasil_sortir.php");
                break;
            case 'sopir':
                header("Location: ../sopir/data_pengiriman.php");
                break;
            case 'operator_mesin':
                header("Location: ../operator/hasil_produksi.php");
                break;
            case 'kepala_produksi':
                header("Location: ../kepala/rekap_data.php");
                break;
            default:
                header("Location: ../dashboard.php");
        }
        exit();
    }
}

// Function to get user data
function getUserData($conn, $user_id) {
    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($query);
    if($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}
?>