
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

// Hanya menerima POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pengajuan_rekening.php");
    exit;
}

// Validasi CSRF
$csrf = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $csrf)
) {
    $_SESSION["pesan_rekening"] = "Permintaan tidak valid. Silakan coba lagi.";
    header("Location: pengajuan_rekening.php");
    exit;
}

$pengajuan_id = (int) ($_POST["pengajuan_id"] ?? 0);
$aksi = $_POST["aksi"] ?? "";

if (
    $pengajuan_id <= 0 ||
    !in_array($aksi, ["setujui", "tolak"], true)
) {
    $_SESSION["pesan_rekening"] = "Data pengajuan tidak valid.";
    header("Location: pengajuan_rekening.php");
    exit;
}

// Mulai transaksi database
mysqli_begin_transaction($conn);

try {

    // Ambil pengajuan dan kunci baris selama proses
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, user_id, jenis_akad, status
         FROM account_applications
         WHERE id = ?
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

        // Buat nomor rekening unik
        do {
            $nomor_rekening = (string) random_int(1000000000, 9999999999);

            $cek = mysqli_prepare(
                $conn,
                "SELECT id FROM accounts WHERE nomor_rekening = ? LIMIT 1"
            );

            mysqli_stmt_bind_param($cek, "s", $nomor_rekening);
            mysqli_stmt_execute($cek);

            $hasilCek = mysqli_stmt_get_result($cek);
            $nomorAda = mysqli_num_rows($hasilCek) > 0;

            mysqli_stmt_close($cek);

        } while ($nomorAda);

        // Buat rekening nasabah
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO accounts
             (user_id, nomor_rekening, jenis_akad, saldo, status)
             VALUES (?, ?, ?, 0, 'Aktif')"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iss",
            $pengajuan["user_id"],
            $nomor_rekening,
            $pengajuan["jenis_akad"]
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal membuat rekening nasabah.");
        }

        mysqli_stmt_close($stmt);

        // Ubah status pengajuan
        $statusBaru = "Disetujui";

    } else {

        // Jika ditolak
        $statusBaru = "Ditolak";
    }

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE account_applications
         SET status = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $statusBaru, $pengajuan_id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal memperbarui status pengajuan.");
    }

    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    if ($aksi === "setujui") {
        $_SESSION["pesan_rekening"] =
            "Pengajuan berhasil disetujui. Rekening nasabah telah dibuat.";
    } else {
        $_SESSION["pesan_rekening"] =
            "Pengajuan pembukaan rekening berhasil ditolak.";
    }

} catch (Throwable $e) {

    mysqli_rollback($conn);

    $_SESSION["pesan_rekening"] = $e->getMessage();
}

header("Location: pengajuan_rekening.php");
exit;
?>
