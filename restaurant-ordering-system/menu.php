<?php
session_start();

// Validasi dua sesi ini
if (!isset($_SESSION['customer_name']) || !isset($_SESSION['table_number'])) {
    header("Location: login_customer.php");
    exit();
}

// Koneksi Database
$conn = new mysqli("localhost", "root", "", "restaurant_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$table_number = $_SESSION['table_number'];
$customer_name = $_SESSION['customer_name'];
$table_initial = "T" . sprintf("%02d", $table_number);

// Proses simpan order ketika tombol "Confirm Order" ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_order') {
    $cart_data = json_decode($_POST['cart_items'], true);
    
    if (!empty($cart_data)) {
        $table_number = $_SESSION['table_number'];
        $customer_name = $_SESSION['customer_name'];
        
        // 1. GENERATE NOMOR ORDER BARU DI SINI (SETIAP KALI PEMESANAN)
        $order_number = "ORD-" . date("Ymd") . "-" . strtoupper(substr(md5(uniqid()), 0, 5));
        
        // 2. INSERT INDUKNYA DULU KE TABEL Orders
        $query_order = "INSERT INTO Orders (order_number, table_number, customer_name, status, payment_status) 
                        VALUES ('$order_number', '$table_number', '$customer_name', 'On Progress', 'Unpaid')";
        
        if ($conn->query($query_order)) {
            // 3. LOOP DATA KERANJANG DAN MASUKKAN KE Order_Details
            foreach ($cart_data as $menu_id => $item) {
                $qty = (int)$item['qty'];
                $menu_id = (int)$menu_id;
                
                $stmt = $conn->prepare("INSERT INTO Order_Details (order_number, menu_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $order_number, $menu_id, $qty);
                $stmt->execute();
            }
            
            // Set notifikasi sukses, lalu lempar balik ke index.php
            $_SESSION['success_msg'] = "Order received. Please wait for your order!";
            header("Location: index.php");
            exit();
        } else {
            echo "Gagal membuat pesanan baru: " . $conn->error;
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Menu - Orange Restaurant</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="brand-logo">
                <img src="images/logo.png" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3081/3081976.png'">
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </a>
                <a href="menu.php" class="nav-item active">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                </a>
            </nav>
        </div>
        <div class="sidebar-bottom">
            <div class="avatar-circle small-avatar"><?php echo $table_initial; ?></div>
            <a href="logout.php" class="logout-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </a>
        </div>
    </aside>

    <main class="menu-main-content">
        <div class="search-box-container">
            <span class="search-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a4b0be" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
            <input type="text" id="search-input" placeholder="What do you want to order?" onkeyup="filterMenu()">
        </div>

        <div class="categories-container">
            <button class="category-card active" onclick="selectCategory('all', this)">
                <div class="cat-icon-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                </div>
                <span>All</span>
            </button>
            <button class="category-card" onclick="selectCategory('food', this)">
                <div class="cat-icon-box">🍱</div>
                <span>Food</span>
            </button>
            <button class="category-card" onclick="selectCategory('drink', this)">
                <div class="cat-icon-box">🥤</div>
                <span>Drink</span>
            </button>
            <button class="category-card" onclick="selectCategory('snack', this)">
                <div class="cat-icon-box">🍕</div>
                <span>Snack</span>
            </button>
        </div>

        <h2 class="menu-section-title" id="category-title">All</h2>

        <div class="menu-products-grid">
            <?php
            $menu_query = "SELECT * FROM Menu";
            $menu_result = $conn->query($menu_query);
            if ($menu_result && $menu_result->num_rows > 0) {
                while ($menu = $menu_result->fetch_assoc()) {
                    ?>
                    <div class="product-item-card" data-category="<?php echo $menu['category']; ?>" data-name="<?php echo strtolower($menu['menu_name']); ?>">
                        <div class="product-img-wrapper">
                            <img src="<?php echo $menu['image']; ?>" alt="<?php echo $menu['menu_name']; ?>" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2921/2921822.png'">
                        </div>
                        <div class="product-details">
                            <h3><?php echo htmlspecialchars($menu['menu_name']); ?></h3>
                            <p class="product-price">Rp <?php echo number_format($menu['price'], 0, ',', '.'); ?></p>
                            
                            <div class="action-control-box" id="ctrl-<?php echo $menu['id']; ?>">
                                <button class="btn-add-initial" onclick="addToCart(<?php echo $menu['id']; ?>, '<?php echo addslashes($menu['menu_name']); ?>', <?php echo $menu['price']; ?>, '<?php echo $menu['image']; ?>')">+</button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </main>

    <aside class="cart-panel">
        <h2 class="cart-title">Cart</h2>
        
        <div class="cart-items-wrapper" id="cart-container">
            <div class="empty-cart-text">Your cart is empty</div>
        </div>

        <div class="cart-summary-section">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="subtotal-val">Rp 0</span>
            </div>
            <div class="summary-row">
                <span>PB (10%)</span>
                <span id="tax-val">Rp 0</span>
            </div>
            <div class="summary-row total-row">
                <span>Total</span>
                <span id="total-val">Rp 0</span>
            </div>
        </div>

        <div class="payment-options-grid">
            <button class="pay-method-btn" onclick="setPaymentMethod('qris', this)" disabled>
                🏦 <span>QRIS</span>
            </button>
            <button class="pay-method-btn active" onclick="setPaymentMethod('cashier', this)">
                🛎️ <span>Pay at the Cashier</span>
            </button>
        </div>

        <form action="" method="POST" onsubmit="return validateCartSubmit()">
            <input type="hidden" name="action" value="confirm_order">
            <input type="hidden" name="cart_items" id="cart-hidden-input">
            <button type="submit" class="btn-confirm-order-submit">Confirm Order</button>
        </form>
    </aside>

    <script>
        let cart = {};
        let currentCategory = 'all';

        function addToCart(id, name, price, image) {
            if (!cart[id]) {
                cart[id] = { id: id, name: name, price: price, image: image, qty: 1 };
            }
            updateCartUI();
        }

        function changeQty(id, delta) {
            if (cart[id]) {
                cart[id].qty += delta;
                if (cart[id].qty <= 0) {
                    delete cart[id];
                }
            }
            updateCartUI();
        }

        function updateCartUI() {
            const container = document.getElementById('cart-container');
            container.innerHTML = '';
            
            let subtotal = 0;
            let hasItems = false;

            for (let id in cart) {
                hasItems = true;
                let item = cart[id];
                let itemTotal = item.price * item.qty;
                subtotal += itemTotal;

                container.innerHTML += `
                    <div class="cart-item-row">
                        <img src="${item.image}" alt="${item.name}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2921/2921822.png'">
                        <div class="cart-item-info">
                            <h4>${item.name}</h4>
                            <p>Rp ${item.price.toLocaleString('id-ID')}</p>
                        </div>
                        <div class="qty-counter-control">
                            <button onclick="changeQty(${id}, -1)">-</button>
                            <span>${item.qty}</span>
                            <button onclick="changeQty(${id}, 1)">+</button>
                        </div>
                    </div>
                `;

                // Update tombol kontrol di grid tengah
                const ctrlBox = document.getElementById(`ctrl-${id}`);
                if (ctrlBox) {
                    ctrlBox.innerHTML = `
                        <div class="qty-counter-control inline-control">
                            <button onclick="changeQty(${id}, -1)">-</button>
                            <span>${item.qty}</span>
                            <button onclick="changeQty(${id}, 1)">+</button>
                        </div>
                    `;
                }
            }

            // Kembalikan tombol '+' jika item dihapus dari keranjang
            const allCards = document.querySelectorAll('.product-item-card');
            allCards.forEach(card => {
                const id = card.querySelector('.action-control-box').id.split('-')[1];
                if (!cart[id]) {
                    const name = card.querySelector('h3').innerText;
                    const price = parseInt(card.querySelector('.product-price').innerText.replace(/[^0-9]/g, ''));
                    const image = card.querySelector('img').getAttribute('src');
                    document.getElementById(`ctrl-${id}`).innerHTML = `
                        <button class="btn-add-initial" onclick="addToCart(${id}, '${name.replace(/'/g, "\\'")}', ${price}, '${image}')">+</button>
                    `;
                }
            });

            if (!hasItems) {
                container.innerHTML = '<div class="empty-cart-text">Your cart is empty</div>';
            }

            // Hitung Pajak & Total
            let tax = subtotal * 0.10;
            let total = subtotal + tax;

            document.getElementById('subtotal-val').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('tax-val').innerText = 'Rp ' + tax.toLocaleString('id-ID');
            document.getElementById('total-val').innerText = 'Rp ' + total.toLocaleString('id-ID');
            
            // Simpan objek data ke elemen form hidden input
            document.getElementById('cart-hidden-input').value = JSON.stringify(cart);
        }

        function filterMenu() {
            let searchKeyword = document.getElementById('search-input').value.toLowerCase();
            let items = document.querySelectorAll('.product-item-card');

            items.forEach(item => {
                let cat = item.getAttribute('data-category');
                let name = item.getAttribute('data-name');
                
                let matchesCategory = (currentCategory === 'all' || cat === currentCategory);
                let matchesSearch = name.includes(searchKeyword);

                if (matchesCategory && matchesSearch) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function selectCategory(category, element) {
            document.querySelectorAll('.category-card').forEach(btn => btn.classList.remove('active'));
            element.classList.remove('active');
            element.classList.add('active');

            currentCategory = category;
            document.getElementById('category-title').innerText = category.charAt(0).toUpperCase() + category.slice(1);
            filterMenu();
        }

        function setPaymentMethod(method, element) {
            document.querySelectorAll('.pay-method-btn').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');
        }

        function validateCartSubmit() {
            if (Object.keys(cart).length === 0) {
                alert('Keranjang Anda masih kosong. Silakan pilih menu!');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>