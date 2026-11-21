
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: financing.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$account_id = (int) ($_POST["account_id"] ?? 0);
$jenis_akad = $_POST["jenis_akad"] ?? "";
$plafon = (float) ($_POST["plafon"] ?? 0);
$tenor = (int) ($_POST["tenor"] ?? 0);

$akad_valid = ["Murabahah", "Mudharabah", "Musyarakah"];
$tenor_valid = [12, 24, 36, 48, 60];

if (
    $account_id <= 0 ||
    !in_array($jenis_akad, $akad_valid, true) ||
    $plafon < 1000000 ||
    $plafon > 100000000 ||
    !in_array($tenor, $tenor_valid, true)
) {
    $_SESSION["pesan_pembiayaan"] = "Data pengajuan tidak valid.";
    header("Location: financing.php");
    exit;
}

// Pastikan rekening aktif tersebut benar-benar milik nasabah
$queryAkun = "SELECT id
              FROM accounts
              WHERE id = ?
              AND user_id = ?
              AND status = 'Aktif'
              LIMIT 1";

$stmtAkun = mysqli_prepare($conn, $queryAkun);
mysqli_stmt_bind_param($stmtAkun, "ii", $account_id, $user_id);
mysqli_stmt_execute($stmtAkun);

$resultAkun = mysqli_stmt_get_result($stmtAkun);
$akun = mysqli_fetch_assoc($resultAkun);

mysqli_stmt_close($stmtAkun);

if (!$akun) {
    $_SESSION["pesan_pembiayaan"] = "Rekening tujuan tidak valid atau tidak aktif.";
    header("Location: financing.php");
    exit;
}

// Simulasi margin tetap 8% per tahun
$margin = 8;
$total_pembayaran = $plafon * (1 + ($margin / 100 * $tenor / 12));
$estimasi_angsuran = $total_pembayaran / $tenor;

// Simpan pengajuan beserta rekening tujuan
$query = "INSERT INTO financing_applications
          (user_id, account_id, jenis_akad, plafon, tenor, margin, estimasi_angsuran)
          VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "iisdidd",
    $user_id,
    $account_id,
    $jenis_akad,
    $plafon,
    $tenor,
    $margin,
    $estimasi_angsuran
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION["pesan_pembiayaan"] =
        "Pengajuan berhasil disimpan dan menunggu persetujuan.";
} else {
    $_SESSION["pesan_pembiayaan"] = "Pengajuan gagal disimpan.";
}

mysqli_stmt_close($stmt);

header("Location: financing.php");
exit;
