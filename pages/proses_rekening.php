
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = (int) $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: accounts.php");
    exit;
}

$jenis_akad = $_POST["jenis_akad"] ?? "";

if (!in_array($jenis_akad, ["Wadiah", "Mudharabah"], true)) {
    header("Location: accounts.php?error=akad");
    exit;
}

// Cek apakah masih ada pengajuan yang menunggu persetujuan
$cek = mysqli_prepare(
    $conn,
    "SELECT id FROM account_applications
     WHERE user_id = ? AND status = 'Menunggu Persetujuan'
     LIMIT 1"
);

mysqli_stmt_bind_param($cek, "i", $user_id);
mysqli_stmt_execute($cek);

$hasil = mysqli_stmt_get_result($cek);
$adaPengajuan = mysqli_num_rows($hasil) > 0;

mysqli_stmt_close($cek);

if ($adaPengajuan) {
    header("Location: accounts.php?pending=1");
    exit;
}

// Simpan pengajuan, rekening belum dibuat
$query = "INSERT INTO account_applications
          (user_id, jenis_akad, status)
          VALUES (?, ?, 'Menunggu Persetujuan')";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: accounts.php?error=gagal");
    exit;
}

mysqli_stmt_bind_param($stmt, "is", $user_id, $jenis_akad);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: accounts.php?diajukan=1");
    exit;
}

mysqli_stmt_close($stmt);

header("Location: accounts.php?error=gagal");
exit;
?>
