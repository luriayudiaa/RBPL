<?php
// login.php - Letakkan di ROOT folder
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Query untuk cek user
    $query = "SELECT id, username, password, nama, role FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($query);
    
    if($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];
        
        // Redirect berdasarkan role
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIP Pengolahan Plastik</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: #3B55FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .logo i {
            font-size: 40px;
            color: white;
        }

        .login-header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            border-color: #3B55FF;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59,85,255,0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #3B55FF;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #2a3fd1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59,85,255,0.3);
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border-left: 4px solid #c62828;
        }

        .info-box {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            font-size: 13px;
            color: #666;
            border: 1px solid #e0e0e0;
        }

        .info-box p {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box i {
            color: #3B55FF;
            width: 20px;
        }

        .demo-account {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px dashed #3B55FF;
        }

        .demo-account p {
            margin-bottom: 5px;
        }

        .demo-account small {
            display: block;
            color: #999;
            margin-top: 10px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-recycle"></i>
            </div>
            <h1>Selamat Datang</h1>
            <p>Sistem Informasi Pengolahan Limbah Plastik</p>
        </div>
        
        <?php if($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Username</label>
                <input type="text" name="username" placeholder="Masukkan Username" required>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" placeholder="Masukkan Password" required>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> LOGIN
            </button>
        </form>

        <!-- <div class="info-box">
            <p><i class="fas fa-info-circle"></i> <strong>Akun Demo:</strong></p>
            <div class="demo-account">
                <p><i class="fas fa-user-tie"></i> Kepala Produksi: kepala1 / kepala123 (Siti Nurhaliza)</p>
                <p><i class="fas fa-sort"></i> Penyortir: penyortir1 / penyortir123 (Budi Santoso)</p>
                <p><i class="fas fa-truck"></i> Sopir: sopir1 / sopir123 (Ahmad Supriyadi)</p>
                <p><i class="fas fa-cogs"></i> Operator: operator1 / operator123 (Rudi Hermawan)</p>
                <small>*Gunakan akun sesuai dengan role Anda</small>
            </div> -->
        </div>
    </div>
</body>
</html>