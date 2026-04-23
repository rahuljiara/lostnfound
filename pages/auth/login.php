<?php
session_start();
require '../../config.php';
require '../../page_info.php';


$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare statement with ? placeholder (MySQLi style)
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");

    // Bind the email parameter
    $stmt->bind_param("s", $email);

    // Execute the statement
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

    if ($user) {

        if ($user['account_status'] !== 'Active') {
            $message = "Account is not active.";
        } elseif ($user['login_attempts'] >= 5) {
            $message = "Too many failed login attempts.";
        } elseif (password_verify($password, $user['password'])) {

            // Reset login attempts & update last_login
            $reset = $conn->prepare("UPDATE users SET login_attempts = 0, last_login = NOW() WHERE id = ?");
            $reset->bind_param("i", $user['id']);
            $reset->execute();

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];

            header("Location: ../../index.php");
            exit;

        } else {

            // Increment login attempts
            $update = $conn->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?");
            $update->bind_param("i", $user['id']);
            $update->execute();

            $message = "Invalid credentials.";
        }

    } else {
        $message = "Invalid credentials.";
    }

}
?>

<!DOCTYPE html>
<html>

<head>

    <title><?= $website_name ?> | Login</title>
    <link rel="shortcut icon" href="<?= "../../" . $website_logo ?>" type="image/x-icon">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="min-h-screen">
    <?php
    $basePath = "../";
    include "../components/navbar.php";
    ?>

    <div class="min-h-[calc(100vh-50px)] grid md:grid-cols-2">

        <!-- LEFT SIDE -->
        <div class="hidden md:flex items-center justify-center bg-gray-100">

            <img src="../../img/logo.png" class="max-w-md w-3/4">

        </div>


        <!-- RIGHT SIDE -->
        <div class="flex items-center justify-center bg-sky-600 px-6 py-10">

            <div class="bg-gray-100 p-8 md:p-10 rounded-2xl shadow-xl w-full max-w-md">

                <h2 class="text-3xl font-bold mb-2">Hello!</h2>

                <p class="text-gray-500 mb-6">
                    <a href="../../index.php" class="block mt-4 text-sm text-gray-600 hover:underline">
                        Go to Home
                    </a>
                </p>

                <?php if ($message) { ?>

                    <p class="text-red-500 mb-4">
                        <?php echo htmlspecialchars($message); ?>
                    </p>

                <?php } ?>

                <form method="POST" class="space-y-5">

                    <input type="email" name="email" placeholder="Email Address" required
                        class="w-full p-3 rounded-full border focus:outline-none focus:ring-2 focus:ring-blue-400">

                    <input type="password" name="password" placeholder="Password" required
                        class="w-full p-3 rounded-full border focus:outline-none focus:ring-2 focus:ring-blue-400">

                    <button class="w-full bg-blue-600 text-white py-3 rounded-full hover:bg-blue-700 transition">

                        Login

                    </button>

                </form>

                <div class="flex justify-around">
                    <a href="forgot_password.php" class="block mt-4 text-sm text-gray-600 hover:underline">
                        Forgot Password
                    </a>
                    <a href="signup.php" class="block mt-4 text-sm text-gray-600 hover:underline">
                        Sign up
                    </a>
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