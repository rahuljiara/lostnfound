<?php
session_start();
require_once '../config.php';
require_once '../page_info.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$profileUser_id = $_SESSION['user_id'];

/* ================= FETCH USER ================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $profileUser_id);
$stmt->execute();
$result = $stmt->get_result();
$profileUser = $result->fetch_assoc();
$stmt->close();

$imgbbApiKey = '090c3fc643a2e18242465613d1dcff0b';

/* ================= UPDATE PROFILE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {

    // Safe fetching (prevents undefined index issues)
    $full_name = $_POST['full_name'] ?? '';
    $profileUsername = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $country = $_POST['country'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : NULL;

    // ✅ Password handling (only update if provided)
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    } else {
        $password = $profileUser['password'];
    }

    // ✅ Keep old image by default
    $profile_image_url = $profileUser['profile_image'];

    // ✅ Image upload fix
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {

        $file_tmp = $_FILES['profile_image']['tmp_name'];

        // Ensure file exists
        if (is_uploaded_file($file_tmp)) {

            $file_base64 = base64_encode(file_get_contents($file_tmp));

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.imgbb.com/1/upload?key=$imgbbApiKey",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => ['image' => $file_base64],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                error_log("cURL Error: " . curl_error($ch));
            }

            curl_close($ch);

            $res = json_decode($response, true);

            if (!empty($res['success']) && !empty($res['data']['url'])) {
                $profile_image_url = $res['data']['url'];
            }
            // ❌ No die() here → don't break user update if image fails
        }
    }

    // ✅ Update query (same as yours, just stable)
    $stmt = $conn->prepare("
        UPDATE users SET 
        full_name=?, 
        username=?, 
        email=?, 
        phone=?, 
        gender=?, 
        country=?, 
        address=?, 
        city=?, 
        state=?, 
        postal_code=?, 
        date_of_birth=?, 
        password=?, 
        profile_image=?, 
        updated_at=CURRENT_TIMESTAMP 
        WHERE id=?
    ");

    $stmt->bind_param(
        "sssssssssssssi",
        $full_name,
        $profileUsername,
        $email,
        $phone,
        $gender,
        $country,
        $address,
        $city,
        $state,
        $postal_code,
        $date_of_birth,
        $password,
        $profile_image_url,
        $profileUser_id
    );

    if (!$stmt->execute()) {
        die("DB Error: " . $stmt->error);
    }

    $stmt->close();

    $_SESSION['user_image'] = $profile_image_url;
    $success = "Profile updated successfully!";

    // Refresh user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $profileUser_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $profileUser = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $website_name ?> | Profile</title>
    <link rel="shortcut icon" href="<?= "../" . $website_logo ?>" type="image/x-icon">
    <link rel="stylesheet" href="../css/nav.css">
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body>
    <!-- NAVBAR -->
    <?php
    $basePath = "";
    include "components/navbar.php";
    ?>

    <div class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-3xl bg-white shadow-xl rounded-xl p-8">

            <div class="flex justify-between items-center mb-6">

                <div class="flex flex-col gap-3 mb-6">

                    <!-- Full Name -->
                    <h2 class="text-2xl font-bold text-gray-800">
                        <?= htmlspecialchars($profileUser['full_name']) ?>
                    </h2>

                    <a href="my_tracking.php"
                        class="inline-flex items-center gap-2 w-fit bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow-md transition">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V7a2 2 0 00-2-2h-3m-4 0H6a2 2 0 00-2 2v6m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4" />
                        </svg>

                        My Tracking
                    </a>

                </div>

                <a href="?logout=true"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    Sign Out
                </a>

            </div>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col items-center mb-6">

                <img src="<?= $profileUser['profile_image'] ?: '../img/default.png' ?>"
                    class="w-24 h-24 rounded-full object-cover border mb-3">

                <p class="text-gray-600 text-sm">
                    <?= $profileUser['username'] ?>
                </p>

            </div>

            <form method="POST" enctype="multipart/form-data" class="space-y-4">

                <div class="grid md:grid-cols-2 gap-4">

                    <div>
                        <label class="text-sm text-gray-600">Full Name</label>
                        <input type="text" name="full_name" value="<?= $profileUser['full_name'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Username</label>
                        <input type="text" name="username" value="<?= $profileUser['username'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                </div>

                <div class="grid md:grid-cols-2 gap-4">

                    <div>
                        <label class="text-sm text-gray-600">Email</label>
                        <input type="email" name="email" value="<?= $profileUser['email'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Phone</label>
                        <input type="text" name="phone" value="<?= $profileUser['phone'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                </div>

                <div class="grid md:grid-cols-2 gap-4">

                    <div>
                        <label class="text-sm text-gray-600">Gender</label>
                        <select name="gender"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">

                            <option value="Male" <?= $profileUser['gender'] == "Male" ? "selected" : "" ?>>Male</option>
                            <option value="Female" <?= $profileUser['gender'] == "Female" ? "selected" : "" ?>>Female
                            </option>
                            <option value="Other" <?= $profileUser['gender'] == "Other" ? "selected" : "" ?>>Other</option>

                        </select>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= $profileUser['date_of_birth'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Address</label>
                        <input type="text" name="address" value="<?= $profileUser['address'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">City</label>
                        <input type="text" name="city" value="<?= $profileUser['city'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">State</label>
                        <input type="text" name="state" value="<?= $profileUser['state'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Country</label>
                        <input type="text" name="country" value="<?= $profileUser['country'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Pin Code</label>
                        <input type="text" name="postal_code" value="<?= $profileUser['postal_code'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Password</label>
                        <input type="password" name="password" value="<?= $profileUser['password'] ?>"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>

                </div>

                <div>

                    <label class="text-sm text-gray-600">Profile Image</label>

                    <input type="file" name="profile_image" class="w-full text-sm border rounded-lg p-2">

                </div>

                <div class="pt-3">

                    <button type="submit" name="update_profile"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-medium transition">
                        Update Profile
                    </button>

                </div>

            </form>
        </div>
    </div>

</body>

</html>