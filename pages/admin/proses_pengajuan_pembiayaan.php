
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

function kembali($pesan)
{
    $_SESSION["pesan_pengajuan"] = $pesan;
    header("Location: pengajuan_pembiayaan.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pengajuan_pembiayaan.php");
    exit;
}

// Validasi CSRF
$token = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    kembali("Permintaan tidak valid. Silakan coba lagi.");
}

$pengajuanId = (int) ($_POST["pengajuan_id"] ?? 0);
$aksi = $_POST["aksi"] ?? "";

if (
    $pengajuanId <= 0 ||
    !in_array($aksi, ["setujui", "tolak"], true)
) {
    kembali("Data pengajuan tidak valid.");
}

mysqli_begin_transaction($conn);

try {

    // Kunci pengajuan agar tidak diproses dua kali
    $query = "SELECT *
              FROM financing_applications
              WHERE id = ?
              FOR UPDATE";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $pengajuanId);
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

    $statusBaru = "Ditolak";

    if ($aksi === "setujui") {

        $userId = (int) $pengajuan["user_id"];
        $accountId = (int) ($pengajuan["account_id"] ?? 0);

        $jenisAkad = $pengajuan["jenis_akad"];
        $plafon = (float) $pengajuan["plafon"];
        $margin = (float) $pengajuan["margin"];
        $tenor = (int) $pengajuan["tenor"];
        $angsuran = (float) $pengajuan["estimasi_angsuran"];

        if ($accountId <= 0 || $plafon <= 0) {
            throw new Exception(
                "Rekening pencairan tidak valid. Nasabah harus membuat pengajuan baru."
            );
        }

        // Cek pembiayaan aktif nasabah
        $queryCek = "SELECT id
                     FROM financings
                     WHERE user_id = ?
                     AND status = 'Aktif'
                     LIMIT 1
                     FOR UPDATE";

        $stmtCek = mysqli_prepare($conn, $queryCek);
        mysqli_stmt_bind_param($stmtCek, "i", $userId);
        mysqli_stmt_execute($stmtCek);

        $resultCek = mysqli_stmt_get_result($stmtCek);
        $pembiayaanAktif = mysqli_fetch_assoc($resultCek);
        mysqli_stmt_close($stmtCek);

        if ($pembiayaanAktif) {
            throw new Exception(
                "Nasabah masih memiliki pembiayaan aktif."
            );
        }

        // Ambil dan kunci rekening tujuan
        $queryAkun = "SELECT id, nomor_rekening
                      FROM accounts
                      WHERE id = ?
                      AND user_id = ?
                      AND status = 'Aktif'
                      FOR UPDATE";

        $stmtAkun = mysqli_prepare($conn, $queryAkun);
        mysqli_stmt_bind_param(
            $stmtAkun,
            "ii",
            $accountId,
            $userId
        );
        mysqli_stmt_execute($stmtAkun);

        $resultAkun = mysqli_stmt_get_result($stmtAkun);
        $rekening = mysqli_fetch_assoc($resultAkun);
        mysqli_stmt_close($stmtAkun);

        if (!$rekening) {
            throw new Exception(
                "Rekening tujuan tidak ditemukan atau tidak aktif."
            );
        }

        // Buat data pembiayaan aktif
        $queryInsert = "INSERT INTO financings
                        (user_id, jenis_akad, plafon, margin, tenor, angsuran, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'Aktif')";

        $stmtInsert = mysqli_prepare($conn, $queryInsert);

        mysqli_stmt_bind_param(
            $stmtInsert,
            "isddid",
            $userId,
            $jenisAkad,
            $plafon,
            $margin,
            $tenor,
            $angsuran
        );

        if (!mysqli_stmt_execute($stmtInsert)) {
            throw new Exception("Gagal membuat data pembiayaan.");
        }

        mysqli_stmt_close($stmtInsert);

        // Tambahkan dana pencairan ke saldo rekening
        $querySaldo = "UPDATE accounts
                       SET saldo = saldo + ?
                       WHERE id = ?
                       AND user_id = ?
                       AND status = 'Aktif'";

        $stmtSaldo = mysqli_prepare($conn, $querySaldo);

        mysqli_stmt_bind_param(
            $stmtSaldo,
            "dii",
            $plafon,
            $accountId,
            $userId
        );

        if (!mysqli_stmt_execute($stmtSaldo)) {
            throw new Exception("Gagal menambahkan saldo rekening.");
        }

        if (mysqli_stmt_affected_rows($stmtSaldo) !== 1) {
            throw new Exception("Saldo rekening gagal diperbarui.");
        }

        mysqli_stmt_close($stmtSaldo);

        // Catat transaksi pencairan
        $kodeTransaksi = "PBY-" . date("YmdHis") . "-" . random_int(1000, 9999);
        $keterangan = "Pencairan pembiayaan " . $jenisAkad;
        $jenisTransaksi = "masuk";

        $queryTransaksi = "INSERT INTO transactions
                           (account_id, kode_transaksi, keterangan, jenis, nominal, tanggal)
                           VALUES (?, ?, ?, ?, ?, NOW())";

        $stmtTransaksi = mysqli_prepare($conn, $queryTransaksi);

        mysqli_stmt_bind_param(
            $stmtTransaksi,
            "isssd",
            $accountId,
            $kodeTransaksi,
            $keterangan,
            $jenisTransaksi,
            $plafon
        );

        if (!mysqli_stmt_execute($stmtTransaksi)) {
            throw new Exception("Gagal mencatat transaksi pencairan.");
        }

        mysqli_stmt_close($stmtTransaksi);

        $statusBaru = "Disetujui";
    }

    // Perbarui status pengajuan
    $queryUpdate = "UPDATE financing_applications
                    SET status = ?
                    WHERE id = ?
                    AND status = 'Menunggu Persetujuan'";

    $stmtUpdate = mysqli_prepare($conn, $queryUpdate);

    mysqli_stmt_bind_param(
        $stmtUpdate,
        "si",
        $statusBaru,
        $pengajuanId
    );

    if (!mysqli_stmt_execute($stmtUpdate)) {
        throw new Exception("Gagal memperbarui status pengajuan.");
    }

    if (mysqli_stmt_affected_rows($stmtUpdate) !== 1) {
        throw new Exception("Status pengajuan gagal diperbarui.");
    }

    mysqli_stmt_close($stmtUpdate);

    mysqli_commit($conn);

    kembali(
        $aksi === "setujui"
            ? "Pengajuan berhasil disetujui dan dana berhasil dicairkan ke rekening nasabah."
            : "Pengajuan berhasil ditolak."
    );

} catch (Throwable $e) {

    mysqli_rollback($conn);

    kembali($e->getMessage());
}
