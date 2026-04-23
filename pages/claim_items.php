<?php
session_start();
require_once "../config.php";
require_once "../page_info.php";

/* ---------------- LOGIN CHECK ---------------- */
if (!isset($_SESSION['user_id'])) {
  header("Location: auth/login.php");
  exit();
}
$user_id = $_SESSION['user_id'];

/* ---------------- FETCH USER INFO ---------------- */
$stmtUser = $conn->prepare("SELECT full_name,email,phone FROM users WHERE id=?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();
$stmtUser->close();

if (!$user)
  die("User not found");

/* ---------------- VALIDATE ITEM ---------------- */
$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);
if (!in_array($type, ['lost', 'found']) || $id <= 0)
  die("Invalid request");
$table = $type === 'lost' ? 'lost_items' : 'found_items';

/* ---------------- FETCH ITEM ---------------- */
$stmt = $conn->prepare("SELECT * FROM $table WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$item)
  die("Item not found");

/* ---------------- CLAIM HANDLER ---------------- */
$success = "";
$error = "";
$tracking_id = "";
if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $reason = trim($_POST['reason'] ?? '');

  if (!$reason) {
    $error = "Please explain how this item belongs to you.";
  } else {

    $item_type_value = $type === 'lost' ? 'Lost' : 'Found';

    $stmtCheck = $conn->prepare("SELECT id FROM claims WHERE user_id=? AND item_id=? AND item_type=?");
    $stmtCheck->bind_param("iis", $user_id, $id, $item_type_value);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
      $error = "You have already submitted a claim for this item.";
    } else {

      // ✅ FIXED TRACKING ID
      $tracking_id = "CLM-" . strtoupper(substr(md5(uniqid()), 0, 8));

      $proofUrl = null;

      if (isset($_FILES['proof_image']) && !empty($_FILES['proof_image']['name'])) {

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($_FILES['proof_image']['type'], $allowedTypes)) {
          $error = "Only JPG and PNG images allowed.";
        } elseif ($_FILES['proof_image']['size'] > 2 * 1024 * 1024) {
          $error = "File must be less than 2MB.";
        } else {

          $imageData = base64_encode(file_get_contents($_FILES['proof_image']['tmp_name']));

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, "https://api.imgbb.com/1/upload?key=$imgbb_api_key");
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $imageData]);

          $response = curl_exec($ch);

          if ($response === false) {
            $error = "Image upload failed.";
          }

          curl_close($ch);

          $result = json_decode($response, true);

          if (isset($result['data']['url'])) {
            $proofUrl = $result['data']['url'];
          }
        }
      }

      if (!$error) {
        $stmt = $conn->prepare("
          INSERT INTO claims
          (item_id, item_type, user_id, user_name, user_email, reason, proof_image, tracking_id, status)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");

        $stmt->bind_param(
          "isisssss",
          $id,
          $item_type_value,
          $user_id,
          $user['full_name'],
          $user['email'],
          $reason,
          $proofUrl,
          $tracking_id
        );

        if ($stmt->execute()) {
          $success = "✅ Claim submitted successfully! Store this ID for tracking.";
        } else {
          $error = "Database error.";
        }

        $stmt->close();
      }
    }

    $stmtCheck->close();
  }
}

/* ---------------- IMAGE ---------------- */
$image = $item['image_url'] ?: "https://via.placeholder.com/400";

/* ---------------- SAFE FUNCTION ---------------- */
function safe($value)
{
  return htmlspecialchars($value ?? '');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $website_name ?> | Claim Item</title>
  <link rel="shortcut icon" href="<?= "../" . $website_logo ?>" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-500">

  <?php include "components/navbar.php"; ?>

  <main class="flex justify-center items-center min-h-[calc(100vh-80px)] px-6 py-12">
    <div class="max-w-3xl w-full">
      <?php if ($success): ?>
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
          <h2 class="text-3xl font-bold text-gray-800 mb-4">Tracking ID Generated</h2>
          <p class="text-green-700 mb-4"><?= safe($success) ?></p>
          <?php if ($tracking_id): ?>
            <div class="flex justify-center gap-3 mt-4">
              <input id="trackingId" value="<?= safe($tracking_id) ?>" readonly
                class="border px-4 py-2 rounded-lg text-center w-60">
              <button onclick="copyTrackingId()"
                class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Copy
              </button>
            </div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="grid md:grid-cols-2 gap-10 items-start">
          <!-- ITEM CARD -->
          <div class="bg-white/20 backdrop-blur-lg text-white rounded-2xl p-6 shadow-xl">
            <h2 class="text-3xl font-bold mb-4">Item Details</h2>
            <img src="<?= safe($image) ?>" class="w-full h-64 object-cover rounded-lg mb-4">
            <h3 class="text-2xl font-semibold"><?= safe($item['product_name']) ?></h3>
            <p class="mt-2"><strong>Type:</strong> <?= safe($item['product_type']) ?></p>
            <p class="mt-2"><strong>Status:</strong> <?= safe($item['status']) ?></p>
            <p class="mt-2"><strong>Marks:</strong> <?= safe($item['unique_marks'] ?? 'N/A') ?></p>
            <p class="mt-2 text-sm"><?= safe($item['description']) ?></p>
          </div>

          <!-- CLAIM FORM -->
          <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Claim This Item</h2>
            <?php if ($error): ?>
              <div class="bg-red-100 text-red-600 p-4 rounded-lg text-center mb-4"><?= safe($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-5">
              <div>
                <label class="block font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" value="<?= htmlspecialchars($user['full_name'] ?? 'Name') ?>" readonly
                  class="w-full border rounded-lg px-4 py-2 bg-gray-100">
              </div>
              <div>
                <label class="block font-medium text-gray-700 mb-1">Email</label>
                <input type="email" value="<?= htmlspecialchars($user['email'] ?? 'Email') ?>" readonly
                  class="w-full border rounded-lg px-4 py-2 bg-gray-100">
              </div>
              <div>
                <label class="block font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" value="<?= htmlspecialchars($user['phone'] ?? 'Phone') ?>" readonly
                  class="w-full border rounded-lg px-4 py-2 bg-gray-100">
              </div>
              <div>
                <label class="block font-medium text-gray-700 mb-1">Reason / Identification Proof</label>
                <textarea name="reason" required rows="4" placeholder="Explain how this item belongs to you..."
                  class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"><?= safe($_POST['reason'] ?? '') ?></textarea>
              </div>
              <div>
                <label class="block font-medium text-gray-700 mb-1">Upload Proof Image (optional)</label>
                <input type="file" name="proof_image" class="w-full border rounded-lg px-4 py-2">
              </div>
              <button type="submit"
                class="w-full bg-gradient-to-r from-blue-500 to-teal-500 text-white py-3 rounded-lg font-semibold hover:scale-105 transition">
                Submit Claim
              </button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php include "components/footer.php"; ?>

  <script>
    function copyTrackingId() {
      const copyText = document.getElementById("trackingId");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      document.execCommand("copy");
      alert("Tracking ID copied: " + copyText.value);
    }
  </script>

</body>

</html>