<?php
require_once 'config/koneksi.php';

echo "Checking lapmas table structure:\n\n";

$result = mysqli_query($db, 'DESCRIBE lapmas');
if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . mysqli_error($db) . "\n";
}

echo "\n\nChecking sample data:\n\n";
$data = mysqli_query($db, 'SELECT * FROM lapmas LIMIT 1');
if ($data) {
    $sample = mysqli_fetch_assoc($data);
    if ($sample) {
        print_r($sample);
    } else {
        echo "No data in lapmas table\n";
    }
} else {
    echo "Error: " . mysqli_error($db) . "\n";
}

mysqli_close($db);
?>
