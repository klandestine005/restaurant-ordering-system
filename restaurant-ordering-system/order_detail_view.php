<?php
session_start();


// Tentukan siapa saja yang boleh melihat halaman ini
$allowed_roles = ['kitchen', 'cashier'];

if (!isset($_SESSION['employee_role']) || !in_array($_SESSION['employee_role'], $allowed_roles)) {
    // Jika role user tidak ada di dalam daftar array di atas, tendang keluar
    header("Location: login_employee.php");
    exit();
}

// Koneksi Database
$conn = new mysqli("localhost", "root", "", "restaurant_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Validasi parameter ID di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: order_history.php");
    exit();
}

$order_number = $conn->real_escape_string($_GET['id']);

// Ambil data utama Order
$order_query = "SELECT * FROM Orders WHERE order_number = '$order_number' LIMIT 1";
$order_result = $conn->query($order_query);

if (!$order_result || $order_result->num_rows === 0) {
    echo "<h3>Data pesanan tidak ditemukan.</h3><a href='order_history.php'>Kembali ke Riwayat</a>";
    exit();
}

$order = $order_result->fetch_assoc();
$short_id = sprintf("%04d", substr(preg_replace('/[^0-9]/', '', $order['order_number']), -4));

$back_url = "order_history.php"; // Default jika yang masuk adalah kitchen

if (isset($_SESSION['employee_role']) && $_SESSION['employee_role'] === 'cashier') {
    $back_url = "order_history_cashier.php"; // Belokkan ke riwayat kasir
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Order #<?php echo $short_id; ?> - Orange Restaurant</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/kitchen.css">
    <style>
        .detail-card { background: #ffffff; padding: 35px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #ebebeb; max-width: 700px; margin: 20px 0; }
        .detail-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; border-bottom: 1px dashed #eaeaea; padding-bottom: 20px; }
        .detail-meta-grid p { margin: 0; font-size: 14.5px; color: #2c3e50; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .invoice-table th { background-color: #f8f9fa; padding: 12px 15px; text-align: left; font-weight: bold; color: #333; border-bottom: 2px solid #eaeaea; }
        .invoice-table td { padding: 14px 15px; border-bottom: 1px solid #f5f5f5; color: #2c3e50; }
        .right-text { text-align: right; }
        .invoice-summary { margin-top: 20px; display: flex; flex-direction: column; gap: 10px; align-items: flex-end; }
        .summary-item { width: 280px; display: flex; justify-content: space-between; font-size: 14.5px; color: #7f8c8d; }
        .summary-item.grand-total { font-size: 18px; font-weight: bold; color: #000; border-top: 1px dashed #dcdde1; padding-top: 10px; margin-top: 5px; }
        .back-link-btn { display: inline-flex; align-items: center; gap: 8px; color: #ff893b; text-decoration: none; font-weight: bold; margin-bottom: 20px; transition: color 0.2s; }
        .back-link-btn:hover { color: #e67327; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="brand-logo">
                <img src="images/logo.png" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3081/3081976.png'">
            </div>
            <nav class="nav-menu">
                <a href="dashboard_kitchen.php" class="nav-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                </a>
                <a href="order_history.php" class="nav-item active">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </a>
            </nav>
        </div>
        <div class="sidebar-bottom">
            <div class="avatar-circle small-avatar" style="background-color: #d81b60;">K</div>
            <a href="logout.php" class="logout-btn"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></a>
        </div>
    </aside>

    <main class="main-content">
        <a href="<?php echo $back_url; ?>" class="back-link-btn">
            &larr; Back to History
        </a>

        <h2 class="section-title">Order Details #<?php echo $short_id; ?></h2>

        <div class="detail-card">
            <div class="detail-meta-grid">
                <div>
                    <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p style="margin-top: 8px;"><strong>Table Number:</strong> Meja <?php echo sprintf("%02d", $order['table_number']); ?></p>
                </div>
                <div>
                    <p><strong>Date & Time:</strong> <?php echo date("d F Y - H:i:s", strtotime($order['date'])); ?></p>
                    <p style="margin-top: 8px;"><strong>Full UID:</strong> <code><?php echo $order['order_number']; ?></code></p>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Menu Name</th>
                        <th class="right-text" style="width: 100px;">Price</th>
                        <th class="right-text" style="width: 80px;">Qty</th>
                        <th class="right-text" style="width: 130px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $subtotal = 0;
                    $items_query = "SELECT od.quantity, m.menu_name, m.price FROM Order_Details od 
                                    JOIN Menu m ON od.menu_id = m.id 
                                    WHERE od.order_number = '$order_number'";
                    $items_result = $conn->query($items_query);

                    while ($item = $items_result->fetch_assoc()) {
                        $item_total = $item['price'] * $item['quantity'];
                        $subtotal += $item_total;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['menu_name']); ?></strong></td>
                            <td class="right-text">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td class="right-text"><?php echo $item['quantity']; ?>x</td>
                            <td class="right-text">Rp <?php echo number_format($item_total, 0, ',', '.'); ?></td>
                        </tr>
                        <?php
                    }
                    
                    $tax = $subtotal * 0.10;
                    $grand_total = $subtotal + $tax;
                    ?>
                </tbody>
            </table>

            <div class="invoice-summary">
                <div class="summary-item">
                    <span>Subtotal</span>
                    <strong>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></strong>
                </div>
                <div class="summary-item">
                    <span>PB (10%)</span>
                    <strong>Rp <?php echo number_format($tax, 0, ',', '.'); ?></strong>
                </div>
                <div class="summary-item grand-total">
                    <span>Grand Total</span>
                    <span style="color: #ff893b;">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </main>

</body>
</html>