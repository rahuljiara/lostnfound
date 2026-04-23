<?php
session_start();
require '../../config.php';
require '../../page_info.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if email or username already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Email or username already exists.";
    } else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $message = "Registration failed. Please try again.";
        }

    }

    $check->close();
    $stmt = null;
}
?>

<!DOCTYPE html>

<html>

<head>

    <title><?= $website_name ?> | Create an Account</title>
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

                <h2 class="text-3xl font-bold mb-2">Create Account</h2>

                <p class="text-gray-500 mb-6">
                    <a href="../../index.php" class="block mt-4 text-sm text-gray-600 hover:underline">
                        Go to Home
                    </a>
                </p>

                <?php if ($message) { ?>

                    <p class="text-red-500 mb-4"><?php echo htmlspecialchars($message); ?></p>

                <?php } ?>

                <form method="POST" class="space-y-4">

                    <input type="text" name="full_name" placeholder="Full Name" required
                        class="w-full p-3 rounded-full border focus:outline-none focus:ring-2 focus:ring-blue-400">

                    <input type="text" name="username" placeholder="Username" required
                        class="w-full p-3 rounded-full border focus:outline-none focus:ring-2 focus:ring-blue-400">

                    <input type="email" name="email" placeholder="Email Address" required
                        class="w-full p-3 rounded-full border focus:outline-none focus:ring-2 focus:ring-blue-400">

                    <input type="password" name="password" placeholder="Password" required
                        class="w-full p-3 rounded-full border focus:outline-none focus:ring-2 focus:ring-blue-400">

                    <button class="w-full bg-blue-600 text-white py-3 rounded-full hover:bg-blue-700">

                        Create Account

                    </button>

                </form>

                <div class="flex justify-center mt-4 text-sm">

                    <a href="login.php" class="text-gray-600 hover:underline">
                        Already have an account?
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