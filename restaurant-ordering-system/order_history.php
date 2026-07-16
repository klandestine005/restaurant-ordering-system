<?php
session_start();

if (!isset($_SESSION['employee_role']) || $_SESSION['employee_role'] !== 'kitchen') {
    header("Location: login_employee.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "restaurant_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Inisialisasi variabel filter
$search_keyword = "";
$date_filter = "";

// 1. Ambil Query Pencarian Kata Kunci
// Tentukan role dasar untuk query (sesuaikan status untuk file history kitchen/cashier)
// Untuk Kitchen: status = 'Done' atau cooking_status = 'Done'
// Untuk Cashier: payment_status = 'Paid'
$search_condition = "WHERE status = 'Done'"; 

// 1. Baca input search box
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_keyword = $conn->real_escape_string(trim($_GET['search']));
    $search_condition .= " AND (order_number LIKE '%$search_keyword%' OR table_number LIKE '%$search_keyword%' OR customer_name LIKE '%$search_keyword%')";
}

// 2. Ambil Query Filter Tanggal
if (isset($_GET['filter_date']) && !empty($_GET['filter_date'])) {
    $date_filter = $conn->real_escape_string($_GET['filter_date']);
    $search_condition .= " AND DATE(date) = '$date_filter'";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Orders - Orange Restaurant</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/kitchen.css">
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
            <span style="font-size: 11px; font-weight: bold; margin-top: -20px; color: #7f8c8d;">Kitchen</span>
            <a href="logout.php" class="logout-btn"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></a>
        </div>
    </aside>

    <main class="main-content">
        
        <form method="GET" action="" style="width: 100%;">
            <div class="search-box-container">
                <span class="search-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a4b0be" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="Search order, number tables, or customer name" onchange="this.form.submit()">
            </div>

            
            <h2 class="section-title">History</h2>
            
            <div class="history-filter-bar">
                <label for="filter_date">Date</label>
                <div class="date-input-wrapper">
                    <input type="date" id="filter_date" name="filter_date" value="<?php echo isset($_GET['filter_date']) ? htmlspecialchars($_GET['filter_date']) : ''; ?>" onchange="this.form.submit()">
                </div>
            </div>
        </form>

        <div class="table-responsive-container">
            <table class="history-data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Date</th>
                        <th>Order Number</th>
                        <th>Table Number</th>
                        <th>Customer Name</th>
                        <th>Menu</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $history_query = "SELECT * FROM Orders $search_condition ORDER BY date DESC";
                    $history_result = $conn->query($history_query);
                    
                    if ($history_result && $history_result->num_rows > 0) {
                        $no = 1;
                        while ($row = $history_result->fetch_assoc()) {
                            $current_no = $row['order_number'];
                            $short_id = sprintf("%04d", substr(preg_replace('/[^0-9]/', '', $current_no), -4));
                            $date_formatted = date("d/m/Y H:i:s", strtotime($row['date']));
                            
                            // Ambil list menu untuk kolom Menu ringkasan (Maksimal 2 item untuk preview)
                            $items_array = [];
                            $m_query = "SELECT od.quantity, m.menu_name FROM Order_Details od JOIN Menu m ON od.menu_id = m.id WHERE od.order_number = '$current_no'";
                            $m_result = $conn->query($m_query);

                            $total_items_type = $m_result->num_rows; // Hitung total jenis menu yang dipesan
                            $counter = 0;

                            while ($m_row = $m_result->fetch_assoc()) {
                                if ($counter < 2) {
                                    // Masukkan maksimal 2 menu pertama ke dalam preview array
                                    $items_array[] = htmlspecialchars($m_row['menu_name']) . " (" . $m_row['quantity'] . "x)";
                                }
                                $counter++;
                            }

                            $menu_summary = implode(", ", $items_array);

                            // Jika jenis menu lebih dari 2, tambahkan label +N di belakangnya
                            if ($total_items_type > 2) {
                                $remaining = $total_items_type - 2;
                                $menu_summary .= ", +" . $remaining;
                            }
                            ?>
                            
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td class="muted-text"><?php echo $date_formatted; ?></td>
                                <td><strong><?php echo $short_id; ?></strong></td>
                                <td><?php echo sprintf("%02d", $row['table_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td class="menu-cell-ellipsis" title="<?php echo $menu_summary; ?>"><?php echo $menu_summary; ?></td>
                                <td style="text-align: right;"><a href="order_detail_view.php?id=<?php echo $current_no; ?>" class="detail-link-btn">Detail &gt;&gt;</a></td>
                            </tr>
                            
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="7" class="empty-table-msg">Tidak ada riwayat transaksi ditemukan.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>