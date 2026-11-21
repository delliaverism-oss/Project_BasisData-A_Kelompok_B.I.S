
<?php
session_start();

require_once __DIR__ . "/../config/database.php";

/* Pastikan pengguna sedang dalam proses verifikasi */
if (!isset($_SESSION["pending_user_id"])) {
    header("Location: ../index.php");
    exit;
}

/* Pastikan proses menggunakan POST */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: otp.php");
    exit;
}

/* Periksa masa berlaku OTP */
if (time() >= ($_SESSION["otp_expiry"] ?? 0)) {
    header("Location: otp.php?error=expired");
    exit;
}

/* Ambil kode OTP dari form */
$otp = trim($_POST["otp"] ?? "");

/* Validasi format dan hash OTP */
if (
    !isset($_SESSION["otp_hash"]) ||
    !preg_match('/^[0-9]{6}$/', $otp)
) {
    $otpValid = false;
} else {
    $otpValid = password_verify(
        $otp,
        $_SESSION["otp_hash"]
    );
}

/* Hitung percobaan verifikasi */
$_SESSION["otp_attempts"] =
    ($_SESSION["otp_attempts"] ?? 0) + 1;

/* Jika OTP salah */
if (!$otpValid) {

    /* Maksimal 5 percobaan */
    if ($_SESSION["otp_attempts"] >= 5) {

        session_unset();
        session_destroy();

        header("Location: ../index.php?error=otp_limit");
        exit;
    }

    header("Location: otp.php?error=1");
    exit;
}

/* OTP benar, ambil data pengguna */
$userId = (int) $_SESSION["pending_user_id"];

$query = "SELECT id, nama, username, role
          FROM users
          WHERE id = ?
          LIMIT 1";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {

    session_unset();
    session_destroy();

    header("Location: ../index.php?error=database");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $userId);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/* Validasi data pengguna */
if (
    !$user ||
    !in_array($user["role"], ["admin", "nasabah"], true)
) {

    session_unset();
    session_destroy();

    header("Location: ../index.php?error=user");
    exit;
}

/* Regenerasi session setelah login berhasil */
session_regenerate_id(true);

/* Simpan data login */
$_SESSION["user_id"] = $user["id"];
$_SESSION["nama"] = $user["nama"];
$_SESSION["username"] = $user["username"];
$_SESSION["role"] = $user["role"];

/* Notifikasi login berhasil */
$_SESSION["login_success_flash"] =
    "Verifikasi OTP berhasil. Selamat datang kembali, " .
    $user["nama"] . "!";

/* Hapus session OTP sementara */
unset(
    $_SESSION["pending_user_id"],
    $_SESSION["pending_nama"],
    $_SESSION["pending_username"],
    $_SESSION["otp_hash"],
    $_SESSION["otp_expiry"],
    $_SESSION["otp_attempts"],
    $_SESSION["demo_otp"],
    $_SESSION["otp_resend_after"]
);

/* Redirect berdasarkan role */
if ($_SESSION["role"] === "admin") {

    header("Location: ../pages/admin/dashboard.php");
    exit;

}

/* Redirect Nasabah */
header("Location: ../pages/dashboard.php");
exit;
?>
