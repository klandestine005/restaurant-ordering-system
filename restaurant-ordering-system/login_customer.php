<?php
session_start();
// Jika sudah login, langsung lempar ke halaman pemesanan makanan
if (isset($_SESSION['customer_name'])) {
    header("Location: index.php");
    exit();
}

// Koneksi Database
$conn = new mysqli("localhost", "root", "", "restaurant_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $table_number = $conn->real_escape_string($_POST['table_number']);
    $customer_name = $conn->real_escape_string($_POST['customer_name']);

    if (!empty($table_number) && !empty($customer_name)) {
        // Generate nomor order unik (Contoh: ORD-20260713-XYZ)
        $order_number = "ORD-" . date("Ymd") . "-" . strtoupper(substr(md5(uniqid()), 0, 5));
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $table_number = $conn->real_escape_string($_POST['table_number']);
            $customer_name = $conn->real_escape_string($_POST['customer_name']);

            if (!empty($table_number) && !empty($customer_name)) {
                // Cukup simpan identitas pelanggan ke Session, tidak perlu INSERT ke SQL dulu
                $_SESSION['table_number'] = $table_number;
                $_SESSION['customer_name'] = $customer_name;
                
                header("Location: index.php"); 
                exit();
            } else {
                $error = "Semua kolom wajib diisi!";
            }
        }
    }   
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Orange Restaurant</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-container">
    <div class="logo-box">
        <img src="images/logo.png" alt="Orange Restaurant Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3081/3081976.png'">
    </div>
    
    <h1 class="title">Welcome to Orange Restaurant</h1>
    <p class="subtitle">Enter your name and table number to start ordering</p>

    <div class="card">
        <?php if(!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="table_number">Table Number</label>
                <input type="number" id="table_number" name="table_number" class="form-control" placeholder="e.g. 5" required>
            </div>
            
            <div class="form-group">
                <label for="customer_name">Name</label>
                <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="e.g. John Doe" required>
            </div>

            <button type="submit" class="btn-orange">Sign In</button>
        </form>
        
        <a href="login_employee.php" class="switch-link">Employee Login Panel &rarr;</a>
    </div>
</div>

</body>
</html>