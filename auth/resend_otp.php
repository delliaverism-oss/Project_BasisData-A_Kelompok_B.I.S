
<?php
session_start();

/* Pastikan ada proses login yang sedang berjalan */
if (!isset($_SESSION["pending_user_id"])) {
    header("Location: ../index.php");
    exit;
}

/* Hanya izinkan request POST */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: otp.php");
    exit;
}

/* Cek cooldown kirim ulang */
$cooldown = $_SESSION["otp_resend_after"] ?? 0;

if (time() < $cooldown) {
    header("Location: otp.php");
    exit;
}

/* Buat OTP baru 6 digit */
$otp = (string) random_int(100000, 999999);

/* Simpan OTP dalam bentuk hash */
$_SESSION["otp_hash"] = password_hash(
    $otp,
    PASSWORD_DEFAULT
);

/* OTP untuk simulasi/demo */
$_SESSION["demo_otp"] = $otp;

/* Masa berlaku OTP 5 menit */
$_SESSION["otp_expiry"] = time() + 300;

/* Reset percobaan OTP */
$_SESSION["otp_attempts"] = 0;

/* Cooldown kirim ulang 30 detik */
$_SESSION["otp_resend_after"] = time() + 30;

/* Redirect kembali ke halaman OTP */
header("Location: otp.php?resent=1");
exit;
?>
