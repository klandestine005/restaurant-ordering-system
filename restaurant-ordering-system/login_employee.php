<?php
session_start();
if (isset($_SESSION['employee_role'])) {
    header("Location: dashboard_" . $_SESSION['employee_role'] . ".php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "restaurant_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Trim digunakan untuk menghapus spasi gaib di awal/akhir inputan user
    $username = trim($conn->real_escape_string($_POST['username']));
    $password = trim($_POST['password']);
    $role = trim($conn->real_escape_string($_POST['role']));

    if (!empty($username) && !empty($password) && !empty($role)) {
        // Cari HANYA berdasarkan username agar lebih akurat
        $query = "SELECT * FROM Users WHERE username = '$username' LIMIT 1";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // 1. Verifikasi Password
            if ($password === $user['password']) {
                
                // 2. Verifikasi apakah Role yang dipilih sesuai dengan di database
                if ($user['role'] === $role) {
                    $_SESSION['employee_id'] = $user['id'];
                    $_SESSION['employee_user'] = $user['username'];
                    $_SESSION['employee_role'] = $user['role'];

                    if ($user['role'] == 'kitchen') {
                        header("Location: dashboard_kitchen.php");
                    } else {
                        header("Location: dashboard_cashier.php");
                    }
                    exit();
                } else {
                    $error = "Role yang Anda pilih tidak sesuai dengan otoritas akun!";
                }
                
            } else {
                $error = "Password yang Anda masukkan salah!";
            }
        } else {
            $error = "Username tidak terdaftar!";
        }
    } else {
        $error = "Semua bidang formulir wajib diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login - Orange Restaurant</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-container">
    <div class="logo-box">
        <img src="images/logo.png" alt="Orange Restaurant Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3081/3081976.png'">
    </div>
    
    <h1 class="title">Welcome to Orange Restaurant</h1>
    <p class="subtitle">Enter your employee credentials to access system</p>

    <div class="card">
        <?php if(!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="" disabled selected>Select role</option>
                    <option value="kitchen">Kitchen</option>
                    <option value="cashier">Cashier</option>
                </select>
            </div>

            <button type="submit" class="btn-orange">Sign In</button>
        </form>
        
        <a href="login_customer.php" class="switch-link">&larr; Customer Order Window</a>
    </div>
</div>

</body>
</html>