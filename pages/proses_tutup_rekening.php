
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

$account_id = (int) ($_POST["account_id"] ?? 0);
$alasan = trim($_POST["alasan"] ?? "");

if ($account_id <= 0) {
    header("Location: accounts.php?error=tutup");
    exit;
}

mysqli_begin_transaction($conn);

try {
    // Pastikan rekening milik nasabah dan masih aktif
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM accounts
         WHERE id = ? AND user_id = ? AND status = 'Aktif'
         FOR UPDATE"
    );

    mysqli_stmt_bind_param($stmt, "ii", $account_id, $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $rekening = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$rekening) {
        throw new Exception("Rekening tidak ditemukan atau sudah tidak aktif.");
    }

    // Cegah pengajuan ganda yang masih menunggu
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM account_closure_applications
         WHERE account_id = ?
         AND status = 'Menunggu Persetujuan'
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $account_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $adaPengajuan = mysqli_num_rows($result) > 0;

    mysqli_stmt_close($stmt);

    if ($adaPengajuan) {
        throw new Exception("Pengajuan penutupan rekening ini masih diproses.");
    }

    // Simpan pengajuan penutupan
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO account_closure_applications
         (account_id, alasan, status)
         VALUES (?, ?, 'Menunggu Persetujuan')"
    );

    mysqli_stmt_bind_param($stmt, "is", $account_id, $alasan);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal mengirim pengajuan penutupan rekening.");
    }

    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    header("Location: accounts.php?tutup=berhasil");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($conn);

    $_SESSION["pesan_tutup_rekening"] = $e->getMessage();

    header("Location: accounts.php?tutup=gagal");
    exit;
}
?>
