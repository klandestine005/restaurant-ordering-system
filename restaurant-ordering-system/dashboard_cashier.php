<?php
session_start();

// Validasi Gatekeeper: pastikan role-nya adalah cashier
if (!isset($_SESSION['employee_role']) || $_SESSION['employee_role'] !== 'cashier') {
    header("Location: login_employee.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "restaurant_db");

// Tangani aksi ketika Kasir menekan tombol "Pay / Done"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_payment') {
    $order_to_pay = $conn->real_escape_string($_POST['order_number']);
    
    // Update payment_status menjadi Paid
    $update_query = "UPDATE Orders SET payment_status = 'Paid' WHERE order_number = '$order_to_pay'";
    $conn->query($update_query);
    
    header("Location: dashboard_cashier.php");
    exit();
}

$search_keyword = "";
$search_condition = "WHERE payment_status = 'Unpaid'"; // Hanya bil yang belum lunas

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_keyword = $conn->real_escape_string(trim($_GET['search']));
    $search_condition .= " AND (order_number LIKE '%$search_keyword%' OR table_number LIKE '%$search_keyword%' OR customer_name LIKE '%$search_keyword%')";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cashier Counter - Orange Restaurant</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/kitchen.css"> </head>
<body>

    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="brand-logo"><img src="images/logo.png" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3081/3081976.png'"></div>
            <nav class="nav-menu">
                <a href="dashboard_cashier.php" class="nav-item active">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                </a>
                <a href="order_history_cashier.php" class="nav-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </a>
            </nav>
        </div>
        <div class="sidebar-bottom">
            <div class="avatar-circle small-avatar" style="background-color: #d81b60;">C</div>
            <span style="font-size: 11px; font-weight: bold; margin-top: -20px; color: #7f8c8d;">Cashier</span>
            <a href="logout.php" class="logout-btn"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></a>
        </div>
    </aside>

    <main class="main-content">
        <form method="GET" action="" class="search-box-container">
            <span class="search-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a4b0be" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
        <input type="text" name="search" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="Search unpaid bills..." autocomplete="off">
        </form>

        <h2 class="section-title">Unpaid Orders</h2>

        <div class="orders-grid">
            <?php
            $query = "SELECT * FROM Orders $search_condition ORDER BY date ASC";
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                while ($order = $result->fetch_assoc()) {
                    $order_no = $order['order_number'];
                    $short_id = sprintf("%04d", substr(preg_replace('/[^0-9]/', '', $order_no), -4));
                    ?>
                    
                    <div class="kitchen-order-card">
                        <div class="kitchen-card-header">
                            <p><strong>Order Number:</strong> <?php echo $short_id; ?></p>
                            <p class="order-table-meta"><strong>Table:</strong> <?php echo sprintf("%02d", $order['table_number']); ?> | <strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: bold; background: #eee; color: #666;">
                                Kitchen: <?php echo $order['status']; ?>
                            </span>
                        </div>
                        
                        <ul class="kitchen-items-list">
                            <?php
                            $details_query = "SELECT od.quantity, m.menu_name FROM Order_Details od JOIN Menu m ON od.menu_id = m.id WHERE od.order_number = '$order_no'";
                            $details_result = $conn->query($details_query);
                            while ($item = $details_result->fetch_assoc()) {
                                echo "<li><span class='item-name'>{$item['menu_name']}</span><span class='item-qty'>{$item['quantity']}x</span></li>";
                            }
                            ?>
                        </ul>
                        
                        <div class="kitchen-card-footer">
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="complete_payment">
                                <input type="hidden" name="order_number" value="<?php echo $order_no; ?>">
                                <button type="submit" class="btn-kitchen-done" style="background-color: #ff893b; box-shadow: 0 2px 8px rgba(76,209,55,0.2);">Process Payment</button>
                            </form>
                        </div>
                    </div>

                    <?php
                }
            } else {
                echo '<p class="no-data-msg">All table tabs have been closed.</p>';
            }
            ?>
        </div>
    </main>
</body>
</html>