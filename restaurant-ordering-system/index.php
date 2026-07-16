<?php
session_start();

// Validasi Gatekeeper: pastikan user sudah mengisi nama dan nomor meja
if (!isset($_SESSION['customer_name']) || !isset($_SESSION['table_number'])) {
    header("Location: login_customer.php");
    exit();
}

// Koneksi Database
$conn = new mysqli("localhost", "root", "", "restaurant_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$customer_name = $_SESSION['customer_name'];
$table_number = $_SESSION['table_number'];

// Inisialisasi inisial lingkaran (contoh: Louis -> L atau Table 02 -> T2)
$table_initial = "T" . sprintf("%02d", $table_number);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Customer - Orange Restaurant</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div id="toast-notif" style="
            position: fixed; 
            top: 25px; 
            left: 50%; 
            transform: translateX(-50%); 
            background-color: #4cd137; 
            color: white; 
            padding: 15px 35px; 
            border-radius: 30px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
            font-weight: bold; 
            z-index: 9999; 
            transition: opacity 0.5s ease;">
            ⚡ <?php echo $_SESSION['success_msg']; ?>
        </div>
        <script>
            // Menghilangkan notifikasi otomatis setelah 4 detik
            setTimeout(function() {
                const toast = document.getElementById('toast-notif');
                if(toast) {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 500);
                }
            }, 4000);
        </script>
        <?php unset($_SESSION['success_msg']); // Hapus flag pesan setelah ditampilkan ?>
    <?php endif; ?>

    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="brand-logo">
                <img src="images/logo.png" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3081/3081976.png'">
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item active">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </a>
                <a href="menu.php" class="nav-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                </a>
            </nav>
        </div>
        
        <div class="sidebar-bottom">
            <div class="user-avatar-box">
                <div class="avatar-circle small-avatar"><?php echo $table_initial; ?></div>
                <div class="avatar-info">
                    <span class="avatar-table">Table <?php echo sprintf("%02d", $table_number); ?></span>
                    <span class="avatar-name"><?php echo htmlspecialchars($customer_name); ?></span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn" title="Sign Out">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </a>
        </div>
    </aside>

    <main class="main-content">
        
        <section class="banner-container">
            <img src="images/banner.png" alt="Promo Banner" class="promo-banner" onerror="this.replaceWith('Banner Area (images/banner.png)')">
        </section>

        <section class="table-bar">
            <div class="table-info-left">
                <div class="avatar-circle pink-avatar"><?php echo $table_initial; ?></div>
                <div class="table-text">
                    <h2>Table <?php echo sprintf("%02d", $table_number); ?></h2>
                    <p><?php echo htmlspecialchars($customer_name); ?></p>
                </div>
            </div>
            <a href="menu.php" class="btn-add-order">Add Order +</a>
        </section>

        <h2 class="section-title">Your Orders</h2>

       <?php
            // 1. Lakukan query untuk mengecek apakah sudah ada pesanan untuk meja ini
            $check_orders_query = "SELECT COUNT(*) as total FROM Orders WHERE table_number = '$table_number' AND customer_name = '$customer_name'";
            $check_orders_result = $conn->query($check_orders_query);
            $orders_count = $check_orders_result->fetch_assoc()['total'];

            // 2. Jika total pesanan lebih dari 0, tampilkan area "Your Orders"
            if ($orders_count > 0): 
        ?>
        <section class="orders-section">
            <div class="orders-grid">
                <?php
                // Mengambil data pesanan khusus nomor meja saat ini
                $orders_query = "SELECT * FROM Orders WHERE table_number = '$table_number' AND customer_name = '$customer_name' ORDER BY date DESC";
                $orders_result = $conn->query($orders_query);

                if ($orders_result && $orders_result->num_rows > 0) {
                    while ($order = $orders_result->fetch_assoc()) {
                        $current_order_no = $order['order_number'];
                        $formatted_date = date("d/m/Y - H:i:s", strtotime($order['date']));
                        
                        // Pemetaan class status untuk CSS
                        $status_class = ($order['status'] == 'Done') ? 'status-done' : 'status-progress';
                        
                        // Menampilkan nomor order pendek untuk keperluan visual interface (misal: 0002)
                        $display_number = sprintf("%04d", substr(preg_replace('/[^0-9]/', '', $current_order_no), -4));
                        ?>
                        
                        <div class="order-card">
                            <div class="order-card-header">
                                <div class="order-meta">
                                    <p><strong>Order Number:</strong> <?php echo $display_number; ?></p>
                                    <p class="order-date"><strong>Date - Time:</strong> <?php echo $formatted_date; ?></p>
                                </div>
                                <span class="status-badge <?php echo $status_class; ?>"><?php echo $order['status']; ?></span>
                            </div>
                            
                            <ul class="order-items-list">
                                <?php
                                // Query mengambil menu yang dibeli di dalam order ini melalui table Order_Details
                                $details_query = "SELECT od.quantity, m.menu_name FROM Order_Details od 
                                                  JOIN Menu m ON od.menu_id = m.id 
                                                  WHERE od.order_number = '$current_order_no'";
                                $details_result = $conn->query($details_query);
                                
                                if ($details_result && $details_result->num_rows > 0) {
                                    while ($item = $details_result->fetch_assoc()) {
                                        echo '<li>';
                                        echo '<span class="item-name">' . htmlspecialchars($item['menu_name']) . '</span>';
                                        echo '<span class="item-qty">' . $item['quantity'] . 'x</span>';
                                        echo '</li>';
                                    }
                                } else {
                                    echo '<li class="empty-item">No item chosen.</li>';
                                }
                                ?>
                            </ul>
                        </div>

                        <?php
                    }
                } else {
                    echo '<p class="no-data">No order yet.</p>';
                }
                ?>
            </div>
        </section>
    <?php 
        else: 
            echo '<p class="no-data">No order yet.</p>';
        endif; 
    ?>
    </main>

</body>
</html>