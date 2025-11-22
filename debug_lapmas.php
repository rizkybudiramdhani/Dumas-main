<?php
session_start();
require_once 'config/koneksi.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Lapmas</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .error { color: red; }
        .success { color: green; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>Debug Lapmas Table</h2>

    <h3>1. Session Info:</h3>
    <pre><?php print_r($_SESSION); ?></pre>

    <h3>2. Database Connection:</h3>
    <?php if (isset($db) && $db): ?>
        <p class="success">✓ Database connected</p>
    <?php else: ?>
        <p class="error">✗ Database NOT connected</p>
    <?php endif; ?>

    <h3>3. Lapmas Table Structure:</h3>
    <pre><?php
    $result = mysqli_query($db, 'DESCRIBE lapmas');
    if ($result) {
        echo "Field\t\t\tType\t\t\tNull\tKey\tDefault\n";
        echo str_repeat("-", 80) . "\n";
        while($row = mysqli_fetch_assoc($result)) {
            printf("%-20s\t%-20s\t%-5s\t%-5s\t%-10s\n",
                $row['Field'],
                $row['Type'],
                $row['Null'],
                $row['Key'],
                $row['Default']
            );
        }
    } else {
        echo "Error: " . mysqli_error($db);
    }
    ?></pre>

    <h3>4. Sample Data from Lapmas:</h3>
    <pre><?php
    $query = "SELECT * FROM lapmas LIMIT 3";
    $result = mysqli_query($db, $query);
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                print_r($row);
                echo "\n";
            }
        } else {
            echo "No data found in lapmas table";
        }
    } else {
        echo "Error: " . mysqli_error($db);
    }
    ?></pre>

    <h3>5. Test get_all_lapmas.php query:</h3>
    <?php if (isset($_SESSION['user_id'])): ?>
        <p class="info">User ID: <?php echo $_SESSION['user_id']; ?></p>
        <pre><?php
        $user_id = $_SESSION['user_id'];
        $query = "SELECT
                    id_lapmas,
                    judul,
                    desk,
                    lokasi,
                    upload,
                    tanggal_lapor,
                    balasan
                  FROM lapmas
                  WHERE Id_akun = ?
                  ORDER BY tanggal_lapor DESC";

        $stmt = mysqli_prepare($db, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result) {
                $count = mysqli_num_rows($result);
                echo "Found $count records for Id_akun $user_id\n\n";

                while($row = mysqli_fetch_assoc($result)) {
                    print_r($row);
                    echo "\n";
                }
            } else {
                echo "Error executing query: " . mysqli_error($db);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error preparing statement: " . mysqli_error($db);
        }
        ?></pre>
    <?php else: ?>
        <p class="error">No user logged in (no user_id in session)</p>
    <?php endif; ?>

    <h3>6. Direct Fetch Test:</h3>
    <button onclick="testFetch()">Test Fetch get_all_lapmas.php</button>
    <pre id="fetchResult"></pre>

    <script>
    function testFetch() {
        document.getElementById('fetchResult').innerHTML = 'Loading...';
        fetch('get_all_lapmas.php')
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.text();
            })
            .then(text => {
                document.getElementById('fetchResult').innerHTML = 'Raw response:\n' + text + '\n\nParsed JSON:\n';
                try {
                    const json = JSON.parse(text);
                    document.getElementById('fetchResult').innerHTML += JSON.stringify(json, null, 2);
                } catch(e) {
                    document.getElementById('fetchResult').innerHTML += 'Error parsing JSON: ' + e.message;
                }
            })
            .catch(error => {
                document.getElementById('fetchResult').innerHTML = 'Fetch error: ' + error;
            });
    }
    </script>
</body>
</html>
