<?php
session_start();
require '../../config.php';
require '../../page_info.php';

$message = "";

if (!isset($_SESSION['reset_email'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $otp = trim($_POST['otp']);
    $new_password = $_POST['password'];

    // Check OTP expiry
    if (time() > $_SESSION['otp_expire']) {
        $message = "OTP expired. Please request again.";
        session_unset();

        // Check OTP match
    } elseif ($otp === (string) $_SESSION['reset_otp']) {

        // Prevent empty password
        if (empty($new_password)) {
            $message = "Password cannot be empty.";
        } else {

            // Hash password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $email = $_SESSION['reset_email'];

            // ✅ FIXED: MySQLi query
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);

            if (!$stmt->execute()) {
                die("DB Error: " . $stmt->error);
            }

            $stmt->close();

            // Clear session after success
            session_unset();

            $message = "Password reset successful.";
        }

    } else {
        $message = "Invalid OTP.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= $website_name ?> | Verify OTP</title>
    <link rel="shortcut icon" href="<?= "../../" . $website_logo ?>" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen">
    <?php
    $basePath = "../";
    include "../components/navbar.php";
    ?>
    <div class="min-h-[calc(100vh-50px)] grid md:grid-cols-2">

        <div class="hidden md:flex items-center justify-center bg-gray-100">
            <img src="../../img/logo.png" class="max-w-md w-3/4">
        </div>

        <div class="flex items-center justify-center bg-sky-600 px-6 py-10">
            <div class="bg-gray-100 p-8 md:p-10 rounded-2xl shadow-xl w-full max-w-md">

                <h2 class="text-3xl font-bold mb-2">Verify OTP</h2>
                <p class="text-gray-500 mb-6">
                    <a href="../../index.php" class="block mt-4 text-sm text-gray-600 hover:underline">
                        Go to Home
                    </a>
                </p>

                <?php if ($message) { ?>
                    <p class="text-green-500 mb-4">
                        <?php echo $message; ?>
                    </p>
                <?php } ?>

                <form method="POST" class="space-y-5">
                    <input type="text" name="otp" placeholder="Enter OTP" required
                        class="w-full p-3 rounded-full border focus:outline-none focus:ring-2 focus:ring-blue-400">

                    <input type="password" name="password" placeholder="New Password" required
                        class="w-full p-3 rounded-full border focus:outline-none focus:ring-2 focus:ring-blue-400">

                    <button class="w-full bg-blue-600 text-white py-3 rounded-full hover:bg-blue-700 transition">
                        Reset Password
                    </button>
                </form>

                <div class="flex justify-around mt-4">
                    <a href="login.php" class="text-sm text-gray-600 hover:underline">Back to Login</a>
                </div>

            </div>
        </div>

    </div>
    <?php
    $basePath = "../";
    include "../components/footer.php";
    ?>
</body>

</html>