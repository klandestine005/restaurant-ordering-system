<?php
session_start();
session_unset();
session_destroy();

// Setelah session dihancurkan, kembalikan ke login customer
header("Location: login_customer.php");
exit();