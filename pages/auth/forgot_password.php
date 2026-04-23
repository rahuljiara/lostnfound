<?php
session_start();
require '../../config.php';
require '../../page_info.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    if ($user) {

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['otp_expire'] = time() + 300; // OTP valid 5 minutes

        // Send OTP email
        $subject = "Password Reset OTP";
        $body = "Your OTP for password reset is: $otp. It is valid for 5 minutes.";
        $headers = "From: noreply@example.com\r\n";

        if (mail($email, $subject, $body, $headers)) {
            header("Location: verify_otp.php");
            exit;
        } else {
            $message = "Failed to send OTP. Try again later.";
        }

    } else {
        $message = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= $website_name ?> | Forgot Password</title>
    <link rel="shortcut icon" href="<?= "../../" . $website_logo ?>" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../.../style.css">
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

                <h2 class="text-3xl font-bold mb-2">Forgot Password</h2>
                <p class="text-gray-500 mb-6">
                    <a href="../../index.php" class="block mt-4 text-sm text-gray-600 hover:underline">
                        Go to Home
                    </a>
                </p>

                <?php if ($message) { ?>
                    <p class="text-red-500 mb-4"><?php echo htmlspecialchars($message); ?></p>
                <?php } ?>

                <form method="POST" class="space-y-5">
                    <input type="email" name="email" placeholder="Email Address" required
                        class="w-full p-3 rounded-full border focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <button class="w-full bg-blue-600 text-white py-3 rounded-full hover:bg-blue-700 transition">
                        Send OTP
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