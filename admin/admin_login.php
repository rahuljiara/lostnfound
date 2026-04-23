<?php
session_start();
require_once '../config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

// LOGIN
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username=?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    if (password_verify($password, $admin['password'])) {
      session_regenerate_id(true);

      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_id'] = $admin['id'];
      $_SESSION['admin_username'] = $admin['username'];

      header("Location: admin_dashboard.php");
      exit;
    } else {
      $message = "Invalid password";
    }
  } else {
    $message = "User not found";
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-900 to-gray-800 flex items-center justify-center h-screen text-white">

  <div class="bg-gray-900/80 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-96 border border-gray-700">

    <h2 class="text-2xl font-bold text-center mb-6">🔐 Admin Login</h2>

    <form method="POST" class="space-y-4">

      <input name="username" placeholder="Username"
        class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">

      <input type="password" name="password" placeholder="Password"
        class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">

      <button class="w-full bg-blue-600 hover:bg-blue-700 transition p-3 rounded font-semibold">
        Login
      </button>

    </form>

    <p class="mt-5 text-center text-sm text-gray-400">
      No admin yet?
      <a href="create_admin.php" class="text-blue-400 hover:underline">Create one</a>
    </p>

    <?php if ($message): ?>
      <p class="mt-4 text-center text-red-400"><?= $message ?></p>
    <?php endif; ?>

  </div>

</body>

</html>