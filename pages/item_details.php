<?php
session_start();
require_once "../config.php";
require_once "../page_info.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmtUser = $conn->prepare("SELECT full_name,email,phone FROM users WHERE id=?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (!in_array($type, ['lost', 'found']) || $id <= 0) {
    die("Invalid request");
}

$table = $type == 'lost' ? 'lost_items' : 'found_items';

$stmt = $conn->prepare("SELECT * FROM $table WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Item not found");
}

$image = !empty($item['image_url'])
    ? $item['image_url']
    : "https://via.placeholder.com/500";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $website_name ?> | Item Details</title>
    <link rel="shortcut icon" href="<?= "../" . $website_logo ?>" type="image/x-icon">

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 min-h-screen">
    <?php $basePath = "";
    include "components/navbar.php"; ?>

    <div class="min-h-screen flex items-center justify-center bg-red-100">
        <div class="max-w-6xl w-full mx-auto p-6 h-[100%]">

            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">

                <div class="grid md:grid-cols-2">

                    <!-- IMAGE -->

                    <div class="bg-gray-50 flex items-center justify-center p-6">
                        <img src="<?= $image ?>" class="rounded-xl shadow-md max-h-[420px] object-contain">
                    </div>


                    <!-- DETAILS -->

                    <div class="p-8">

                        <h1 class="text-3xl font-bold text-gray-800 mb-6">
                            <?= htmlspecialchars($item['product_name']) ?>
                        </h1>

                        <div class="space-y-3 text-gray-700">

                            <p>
                                <span class="font-semibold">Type:</span>
                                <?= htmlspecialchars($item['product_type']) ?>
                            </p>

                            <p>
                                <span class="font-semibold">Location:</span>
                                <?= $type == 'lost' ? $item['last_seen_location'] : $item['found_location'] ?>
                            </p>

                            <p>
                                <span class="font-semibold">Date:</span>
                                <?= $type == 'lost' ? $item['missing_date'] : $item['found_date'] ?>
                            </p>

                            <p>
                                <span class="font-semibold">Unique Marks:</span>
                                <?= $item['unique_marks'] ?: "N/A" ?>
                            </p>

                        </div>

                        <div class="mt-6">

                            <p class="font-semibold text-gray-800 mb-1">
                                Description
                            </p>

                            <p class="text-gray-600 leading-relaxed">
                                <?= $item['description'] ?: "No description provided." ?>
                            </p>

                        </div>


                        <div class="mt-6">

                            <span class="font-semibold">Status:</span>

                            <span class="ml-2 text-green-600 font-semibold">
                                <?= $item['status'] ?>
                            </span>

                        </div>


                        <a href="claim_items.php?type=<?= $type ?>&id=<?= $id ?>"
                            class="mt-8 block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">

                            Claim this Item

                        </a>

                    </div>

                </div>

            </div>
        </div>
    </div>
    <?php $basePath = "";
    include "components/footer.php"; ?>

</body>

</html>