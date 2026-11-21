
<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: financing.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$financing_id = (int) ($_POST["financing_id"] ?? 0);
$account_id = (int) ($_POST["account_id"] ?? 0);

$_SESSION["pesan_pembiayaan"] = "";

try {
    mysqli_begin_transaction($conn);

    // Ambil pembiayaan dan kunci datanya
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM financings
         WHERE id = ? AND user_id = ?
         FOR UPDATE"
    );

    mysqli_stmt_bind_param($stmt, "ii", $financing_id, $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $pembiayaan = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$pembiayaan || $pembiayaan["status"] !== "Aktif") {
        throw new Exception("Pembiayaan tidak aktif atau tidak ditemukan.");
    }

    $tenor = (int) $pembiayaan["tenor"];
    $nominal = (float) $pembiayaan["angsuran"];

    if ($tenor <= 0 || $nominal <= 0) {
        throw new Exception("Data angsuran tidak valid.");
    }

    // Hitung jumlah angsuran yang sudah dibayar
    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM financing_payments
         WHERE financing_id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $financing_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $dataBayar = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $terbayar = (int) $dataBayar["total"];

    if ($terbayar >= $tenor) {
        throw new Exception("Semua angsuran sudah lunas.");
    }

    $angsuranKe = $terbayar + 1;

    // Ambil rekening milik nasabah dan kunci saldonya
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, saldo FROM accounts
         WHERE id = ? AND user_id = ?
         FOR UPDATE"
    );

    mysqli_stmt_bind_param($stmt, "ii", $account_id, $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $rekening = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$rekening) {
        throw new Exception("Rekening tidak ditemukan.");
    }

    if ((float) $rekening["saldo"] < $nominal) {
        throw new Exception("Saldo rekening tidak mencukupi.");
    }

    // Kurangi saldo rekening
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE accounts SET saldo = saldo - ?
         WHERE id = ? AND user_id = ?"
    );

    mysqli_stmt_bind_param($stmt, "dii", $nominal, $account_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Catat transaksi
    $kode = "ANG" . date("YmdHis") . random_int(100, 999);
    $keterangan = "Pembayaran angsuran ke-" . $angsuranKe;

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO transactions
         (account_id, kode_transaksi, keterangan, jenis, nominal, tanggal)
         VALUES (?, ?, ?, 'keluar', ?, NOW())"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "issd",
        $account_id,
        $kode,
        $keterangan,
        $nominal
    );

    mysqli_stmt_execute($stmt);
    $transaction_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Simpan riwayat pembayaran
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO financing_payments
         (financing_id, user_id, account_id, angsuran_ke, nominal, transaction_id)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iiiidi",
        $financing_id,
        $user_id,
        $account_id,
        $angsuranKe,
        $nominal,
        $transaction_id
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Jika seluruh angsuran sudah dibayar, ubah status menjadi Lunas
    if ($angsuranKe >= $tenor) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE financings SET status = 'Lunas'
             WHERE id = ? AND user_id = ?"
        );

        mysqli_stmt_bind_param($stmt, "ii", $financing_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($conn);

    $_SESSION["pesan_pembiayaan"] =
        "Pembayaran angsuran ke-" . $angsuranKe . " berhasil.";

} catch (Throwable $e) {
    mysqli_rollback($conn);

    $_SESSION["pesan_pembiayaan"] =
        "Pembayaran gagal: " . $e->getMessage();
}

header("Location: financing.php");
exit;
?>
