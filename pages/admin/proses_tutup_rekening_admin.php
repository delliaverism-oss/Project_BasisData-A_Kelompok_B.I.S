
<?php
session_start();

require_once __DIR__ . "/../../config/database.php";

// Proteksi admin
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pengajuan_tutup_rekening.php");
    exit;
}

// Validasi CSRF
$csrf = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $csrf)
) {
    $_SESSION["pesan_tutup_admin"] = "Permintaan tidak valid.";
    header("Location: pengajuan_tutup_rekening.php");
    exit;
}

$pengajuan_id = (int) ($_POST["pengajuan_id"] ?? 0);
$aksi = $_POST["aksi"] ?? "";

if (
    $pengajuan_id <= 0 ||
    !in_array($aksi, ["setujui", "tolak"], true)
) {
    $_SESSION["pesan_tutup_admin"] = "Data pengajuan tidak valid.";
    header("Location: pengajuan_tutup_rekening.php");
    exit;
}

mysqli_begin_transaction($conn);

try {
    // Ambil pengajuan dan kunci baris
    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            aca.id,
            aca.account_id,
            aca.status,
            a.status AS status_rekening,
            a.saldo
         FROM account_closure_applications aca
         INNER JOIN accounts a ON aca.account_id = a.id
         WHERE aca.id = ?
         FOR UPDATE"
    );

    mysqli_stmt_bind_param($stmt, "i", $pengajuan_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $pengajuan = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$pengajuan) {
        throw new Exception("Pengajuan tidak ditemukan.");
    }

    if ($pengajuan["status"] !== "Menunggu Persetujuan") {
        throw new Exception("Pengajuan ini sudah diproses sebelumnya.");
    }

    if ($aksi === "setujui") {

        // Rekening hanya bisa ditutup jika saldo sudah nol
        if ((float) $pengajuan["saldo"] != 0) {
            throw new Exception(
                "Penutupan ditolak. Saldo rekening harus Rp0 terlebih dahulu."
            );
        }

        if ($pengajuan["status_rekening"] !== "Aktif") {
            throw new Exception("Rekening sudah tidak aktif.");
        }

        // Nonaktifkan rekening, bukan menghapusnya
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE accounts SET status = 'Nonaktif'
             WHERE id = ? AND status = 'Aktif'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $pengajuan["account_id"]
        );

        if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) !== 1) {
            throw new Exception("Gagal menonaktifkan rekening.");
        }

        mysqli_stmt_close($stmt);

        $statusBaru = "Disetujui";

    } else {
        $statusBaru = "Ditolak";
    }

    // Perbarui status pengajuan
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE account_closure_applications
         SET status = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $statusBaru, $pengajuan_id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal memperbarui status pengajuan.");
    }

    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    $_SESSION["pesan_tutup_admin"] = ($aksi === "setujui")
        ? "Pengajuan disetujui. Rekening berhasil dinonaktifkan."
        : "Pengajuan penutupan rekening berhasil ditolak.";

} catch (Throwable $e) {
    mysqli_rollback($conn);
    $_SESSION["pesan_tutup_admin"] = $e->getMessage();
}

header("Location: pengajuan_tutup_rekening.php");
exit;
?>
