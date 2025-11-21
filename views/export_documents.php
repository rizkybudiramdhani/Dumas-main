<?php
ob_start();
require_once CONFIG_PATH . '/path.php';
include CONFIG_PATH . '/db.php';
include LINK_PATH . '/header.php';
include CONFIG_PATH . '/gate.php';

$id_user = $_SESSION['Id_user'] ?? 0;

$role = mysqli_query($conn, "SELECT Dept, Pos, Nama FROM akun WHERE Dept = '" . $_SESSION['dept'] . "' AND Pos = '" . $_SESSION['pos'] . "'  AND Nama = '" . $_SESSION['nama'] . "' ");
$r = mysqli_fetch_array($role);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="document_archive_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Create Excel content
echo "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">";
echo "<head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
echo "<style>";
echo "table { border-collapse: collapse; width: 100%; }";
echo "th, td { border: 1px solid #000; padding: 5px; text-align: left; }";
echo "th { background-color: #f0f0f0; font-weight: bold; }";
echo ".text-center { text-align: center; }";
echo ".badge { padding: 3px 8px; border-radius: 3px; font-size: 12px; }";
echo ".badge-warning { background-color: #ffc107; color: #000; }";
echo ".badge-info { background-color: #17a2b8; color: #fff; }";
echo ".badge-primary { background-color: #007bff; color: #fff; }";
echo ".badge-success { background-color: #28a745; color: #fff; }";
echo ".badge-danger { background-color: #dc3545; color: #fff; }";
echo "</style>";
echo "</head>";
echo "<body>";

// Title
echo "<h2>Document Archive Export</h2>";
echo "<p>Export Date: " . date('d-m-Y H:i:s') . "</p>";
echo "<p>Exported by: " . htmlspecialchars($r['Nama']) . " (" . htmlspecialchars($r['Dept']) . " - " . htmlspecialchars($r['Pos']) . ")</p>";
echo "<br>";

// Query to get all documents
$queryAll = "SELECT
    d.Id_doc,
    d.no_doc,
    d.jenis_doc,
    d.tipe_doc,
    a.Nama,
    d.deskripsi_doc,
    d.status,
    d.tgl_pengajuan,
    d.lampiran_doc
FROM
    doc AS d
JOIN
    akun AS a ON d.data_pemohon = a.Id_user
ORDER BY
    d.tgl_pengajuan DESC";

$stmt = $conn->prepare($queryAll);
$stmt->execute();
$resultAll = $stmt->get_result();

// Status mapping for display
$statusMap = [
    'PendingDH' => ['class' => 'warning', 'text' => 'Pending DH'],
    'PendingMR' => ['class' => 'info', 'text' => 'Pending MR'],
    'PendingFM' => ['class' => 'primary', 'text' => 'Pending FM'],
    'PendingDCC' => ['class' => 'info', 'text' => 'Pending DCC'],
    'Approved' => ['class' => 'success', 'text' => 'Disetujui'],
    'Done' => ['class' => 'success', 'text' => 'Selesai'],
    'Rejected' => ['class' => 'danger', 'text' => 'Ditolak'],
];

// Create table
echo "<table>";
echo "<thead>";
echo "<tr>";
echo "<th>No</th>";
echo "<th>Document Number</th>";
echo "<th>Document Type</th>";
echo "<th>Request Type</th>";
echo "<th>Applicant Name</th>";
echo "<th>Description</th>";
echo "<th>Status</th>";
echo "<th>Submission Date</th>";
echo "<th>Attachments</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

if ($resultAll && $resultAll->num_rows > 0) {
    $no = 1;
    while ($row = $resultAll->fetch_assoc()) {
        // Decode lampiran_doc
        $lampiranText = '';
        if (!empty($row['lampiran_doc'])) {
            $decodedLampiran = json_decode($row['lampiran_doc'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedLampiran)) {
                $lampiranText = count($decodedLampiran) . ' file(s)';
            } else {
                $lampiranText = '1 file';
            }
        } else {
            $lampiranText = 'No attachments';
        }

        $status = $statusMap[$row['status']] ?? ['class' => 'secondary', 'text' => $row['status']];

        echo "<tr>";
        echo "<td class='text-center'>" . $no . "</td>";
        echo "<td>" . htmlspecialchars($row['no_doc']) . "</td>";
        echo "<td>" . htmlspecialchars($row['jenis_doc']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tipe_doc']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Nama']) . "</td>";
        echo "<td>" . htmlspecialchars($row['deskripsi_doc']) . "</td>";
        echo "<td><span class='badge badge-" . $status['class'] . "'>" . htmlspecialchars($status['text']) . "</span></td>";
        echo "<td>" . date('d-m-Y', strtotime($row['tgl_pengajuan'])) . "</td>";
        echo "<td>" . $lampiranText . "</td>";
        echo "</tr>";

        $no++;
    }
} else {
    echo "<tr>";
    echo "<td colspan='9' class='text-center'>No documents found</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

// Summary statistics
$totalDocs = $resultAll ? $resultAll->num_rows : 0;

// Count by status
$statusCounts = [];
if ($resultAll) {
    $resultAll->data_seek(0); // Reset pointer
    while ($row = $resultAll->fetch_assoc()) {
        $status = $row['status'];
        if (!isset($statusCounts[$status])) {
            $statusCounts[$status] = 0;
        }
        $statusCounts[$status]++;
    }
}

echo "<br><br>";
echo "<h3>Summary Statistics</h3>";
echo "<table>";
echo "<thead>";
echo "<tr>";
echo "<th>Status</th>";
echo "<th>Count</th>";
echo "<th>Percentage</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($statusMap as $statusKey => $statusInfo) {
    $count = $statusCounts[$statusKey] ?? 0;
    $percentage = $totalDocs > 0 ? round(($count / $totalDocs) * 100, 1) : 0;

    echo "<tr>";
    echo "<td><span class='badge badge-" . $statusInfo['class'] . "'>" . htmlspecialchars($statusInfo['text']) . "</span></td>";
    echo "<td class='text-center'>" . $count . "</td>";
    echo "<td class='text-center'>" . $percentage . "%</td>";
    echo "</tr>";
}

echo "<tr style='font-weight: bold; background-color: #f0f0f0;'>";
echo "<td>Total Documents</td>";
echo "<td class='text-center'>" . $totalDocs . "</td>";
echo "<td class='text-center'>100%</td>";
echo "</tr>";

echo "</tbody>";
echo "</table>";

echo "</body>";
echo "</html>";

// Close database connection
if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}

ob_end_flush();
exit();
?>
