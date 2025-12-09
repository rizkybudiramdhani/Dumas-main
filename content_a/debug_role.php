<?php
session_start();
require_once('../config/koneksi.php');

echo "<h3>Debug Session Info:</h3>";
echo "<pre>";
echo "Role: " . ($_SESSION['Role'] ?? 'NOT SET') . "\n";
echo "Role Type: " . gettype($_SESSION['Role'] ?? null) . "\n";
echo "Id_akun: " . ($_SESSION['Id_akun'] ?? 'NOT SET') . "\n";
echo "Nama: " . ($_SESSION['Nama'] ?? 'NOT SET') . "\n";
echo "\n--- All Session Data ---\n";
print_r($_SESSION);
echo "</pre>";
?>
