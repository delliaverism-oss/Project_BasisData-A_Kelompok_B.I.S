
<?php
session_start();

require_once __DIR__ . "/../config/database.php";

/* Pastikan request berasal dari form login */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

/* Ambil data login */
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";
$role = $_POST["role"] ?? "";

/* Validasi role */
if (!in_array($role, ["admin", "nasabah"], true)) {
    header("Location: ../index.php?error=1");
    exit;
}

/* Cari pengguna berdasarkan username */
$query = "SELECT * FROM users WHERE username = ? LIMIT 1";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: ../index.php?error=database");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $username);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/* Validasi username, password, dan role */
if (
    !$user ||
    !password_verify($password, $user["password"]) ||
    $user["role"] !== $role
) {
    header("Location: ../index.php?error=1");
    exit;
}

/* Regenerasi session */
session_regenerate_id(true);

/* Bersihkan data OTP login sebelumnya */
unset(
    $_SESSION["pending_user_id"],
    $_SESSION["pending_nama"],
    $_SESSION["pending_username"],
    $_SESSION["otp_hash"],
    $_SESSION["otp_expiry"],
    $_SESSION["otp_attempts"],
    $_SESSION["demo_otp"],
    $_SESSION["otp_resend_after"],
    $_SESSION["login_success_flash"]
);

/* Simpan data pengguna sementara */
$_SESSION["pending_user_id"] = $user["id"];
$_SESSION["pending_nama"] = $user["nama"];
$_SESSION["pending_username"] = $user["username"];

/* Buat OTP 6 digit */
$otp = (string) random_int(100000, 999999);

/* Simpan OTP dalam bentuk hash */
$_SESSION["otp_hash"] = password_hash(
    $otp,
    PASSWORD_DEFAULT
);

/* Masa berlaku OTP 5 menit */
$_SESSION["otp_expiry"] = time() + 300;

/* Reset percobaan verifikasi */
$_SESSION["otp_attempts"] = 0;

/* OTP demo */
$_SESSION["demo_otp"] = $otp;

/* Cooldown kirim ulang 30 detik */
$_SESSION["otp_resend_after"] = time() + 30;

/* Masuk ke halaman verifikasi */
header("Location: otp.php?sent=1");
exit;
?>
