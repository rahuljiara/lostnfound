<?php
session_start();
require_once "../config.php";
require_once "../page_info.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";


// Fetch logged-in user's full name
$user_id = $_SESSION['user_id'];
$stmtUser = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();
$userFullName = $user['full_name'] ?? '';
$stmtUser->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tracking_id = "LF" . strtoupper(substr(md5(uniqid()), 0, 8));

    $product_name = htmlspecialchars($_POST['product_name']);
    $product_type = htmlspecialchars($_POST['product_type']);
    $missing_date = $_POST['missing_date'];
    $unique_marks = htmlspecialchars($_POST['unique_marks']);
    $last_seen_location = htmlspecialchars($_POST['last_seen_location']);
    $description = htmlspecialchars($_POST['description']);
    $image_url = "";

    // Handle image upload via imgbb
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $error = "❌ Only JPG, PNG, WEBP images allowed.";
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $error = "❌ Image must be under 2MB.";
        }

        if (!$error) {
            $image_data = base64_encode(file_get_contents($_FILES['image']['tmp_name']));
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.imgbb.com/1/upload?key=$imgbb_api_key",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => [
                    "image" => $image_data,
                    "name" => preg_replace('/\s+/', '_', $product_name)
                ],
                CURLOPT_RETURNTRANSFER => true
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error = "❌ Image upload error: " . curl_error($ch);
            }

            curl_close($ch);

            if (!$error) {
                $resp_json = json_decode($response, true);
                if (isset($resp_json['data']['url'])) {
                    $image_url = $resp_json['data']['url'];
                } else {
                    $error = "❌ Image upload failed.";
                }
            }
        }
    }

    // Insert into database without user_id
    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO lost_items
            (product_name, product_type, lost_by, missing_date, unique_marks, last_seen_location, description, image_url, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Open')");

        $stmt->bind_param(
            "ssssssss",
            $product_name,
            $product_type,
            $userFullName,
            $missing_date,
            $unique_marks,
            $last_seen_location,
            $description,
            $image_url
        );

        if ($stmt->execute()) {
            $success = "✅ Lost item reported successfully! Tracking ID: <b>$tracking_id</b>";
        } else {
            $error = "❌ Database Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $website_name ?> | Report Lost</title>
    <link rel="shortcut icon" href="<?= "../" . $website_logo ?>" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>

    <?php $basePath = "";
    include "components/navbar.php"; ?>

    <div class="bg-gradient-to-r from-[#0f172a]  to-[#334155] min-h-screen flex items-center justify-center p-6">


        <div class="bg-white bg-opacity-95 shadow-2xl rounded-xl w-full max-w-3xl p-10">
            <h2 class="text-4xl font-extrabold text-center mb-8 text-gray-800">Report Lost Item</h2>

            <?php if ($success) { ?>
                <div class="bg-green-100 text-green-800 p-3 rounded mb-6 text-center font-semibold">
                    <?= $success ?>
                </div>
            <?php } ?>

            <?php if ($error) { ?>
                <div class="bg-red-100 text-red-800 p-3 rounded mb-6 text-center font-semibold">
                    <?= $error ?>
                </div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Product Name</label>
                        <input type="text" name="product_name" required
                            class="w-full mt-1 px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Product Type</label>
                        <input type="text" name="product_type" required
                            class="w-full mt-1 px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Lost By</label>
                        <input type="text" name="lost_by" value="<?= htmlspecialchars($userFullName) ?>" readonly
                            class="w-full mt-1 px-4 py-3 border border-gray-300 rounded-lg shadow-sm bg-gray-100 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Missing Date</label>
                        <input type="date" name="missing_date" required
                            class="w-full mt-1 px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Unique Marks</label>
                    <input type="text" name="unique_marks"
                        class="w-full mt-1 px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Last Seen Location</label>
                    <input type="text" name="last_seen_location" required
                        class="w-full mt-1 px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" required
                        class="w-full mt-1 px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Upload Image</label>
                    <input type="file" name="image"
                        class="w-full mt-1 text-sm border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <div class="text-center pt-4">
                    <button type="submit"
                        class="bg-indigo-600 text-white px-10 py-3 rounded-full hover:bg-indigo-700 transition duration-300 font-bold shadow-lg hover:shadow-xl">
                        Submit Report
                    </button>
                </div>

            </form>
        </div>
    </div>

    <?php $basePath = "";
    include "components/footer.php"; ?>
</body>

</html>