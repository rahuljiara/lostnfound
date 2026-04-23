<?php
session_start();
require_once '../config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

// CREATE ADMIN
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
  $stmt->bind_param("ss", $username, $password);

  if ($stmt->execute()) {
    $message = "Admin created successfully!";
  } else {
    $message = "Error: " . $stmt->error;
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Create Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-900 to-gray-800 flex items-center justify-center h-screen text-white">

  <div class="bg-gray-900/80 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-96 border border-gray-700">

    <h2 class="text-2xl font-bold text-center mb-6">⚙️ Create Admin</h2>

    <form method="POST" class="space-y-4">

      <input name="username" placeholder="Username"
        class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500">

      <input type="password" name="password" placeholder="Password"
        class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500">

      <button class="w-full bg-green-600 hover:bg-green-700 transition p-3 rounded font-semibold">
        Create Admin
      </button>

    </form>

    <p class="mt-5 text-center text-sm text-gray-400">
      Already have account?
      <a href="admin_login.php" class="text-blue-400 hover:underline">Login</a>
    </p>

    <?php if ($message): ?>
      <p class="mt-4 text-center text-green-400"><?= $message ?></p>
    <?php endif; ?>

  </div>

</body>

</html>