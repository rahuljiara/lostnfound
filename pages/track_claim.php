<?php
require_once "../config.php";
require_once "../page_info.php";

$status = "";
$claim = null;
$tracking_id = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $tracking_id = strtoupper(trim($_POST['tracking_id']));

  if (!empty($tracking_id)) {

    $stmt = $conn->prepare("
            SELECT c.tracking_id,
                   c.reason,
                   c.status,
                   c.created_at,
                   COALESCE(l.product_name, f.product_name) AS product_name,
                   COALESCE(l.product_type, f.product_type) AS product_type,
                   COALESCE(l.missing_date, f.found_date) AS item_date,
                   COALESCE(l.lost_by, u.full_name) AS person
            FROM claims c
            LEFT JOIN lost_items l ON c.item_id = l.id
            LEFT JOIN found_items f ON c.item_id = f.id
            LEFT JOIN users u ON f.user_id = u.id
            WHERE c.tracking_id = ?
            LIMIT 1
        ");

    $stmt->bind_param("s", $tracking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $claim = $result->fetch_assoc();
    } else {
      $status = "❌ No claim found with this tracking ID.";
    }

    $stmt->close();

  } else {
    $status = "⚠️ Please enter a valid tracking ID.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $website_name ?> | Track Claims</title>
  <link rel="shortcut icon" href="<?= "../" . $website_logo ?>" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-blue-900 via-blue-600 to-cyan-900 min-h-screen flex flex-col">

  <?php $basePath = "";
  include "components/navbar.php"; ?>

  <header class="text-center text-white py-10">
    <h1 class="text-4xl font-bold">Lost & Found Tracking System</h1>
    <p class="mt-2 text-indigo-100">Enter your tracking ID to see the status of your claim</p>
  </header>

  <main class="flex-grow flex justify-center items-start px-4 pb-10">
    <div class="bg-white shadow-2xl rounded-xl w-full max-w-xl p-8">

      <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Track Your Claim</h2>

      <form method="POST" class="flex flex-col md:flex-row gap-4 mb-6">
        <input type="text" name="tracking_id" placeholder="Enter Tracking ID" required
          value="<?= htmlspecialchars($tracking_id) ?>"
          class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <button type="submit"
          class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
          Track
        </button>
      </form>

      <?php if ($status): ?>
        <p class="text-red-600 text-center font-medium mb-4"><?= htmlspecialchars($status) ?></p>
      <?php endif; ?>

      <?php if ($claim): ?>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 shadow-md">
          <h3 class="text-xl font-bold text-gray-800 mb-4">Claim Details</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">

            <div><span class="font-semibold">Tracking ID:</span> <?= htmlspecialchars($claim['tracking_id']) ?></div>
            <div><span class="font-semibold">Item Name:</span> <?= htmlspecialchars($claim['product_name'] ?? '-') ?>
            </div>
            <div><span class="font-semibold">Item Type:</span> <?= htmlspecialchars($claim['product_type'] ?? '-') ?>
            </div>
            <div><span class="font-semibold">Reported By:</span> <?= htmlspecialchars($claim['person'] ?? '-') ?></div>
            <div class="md:col-span-2"><span class="font-semibold">Reason:</span>
              <?= htmlspecialchars($claim['reason']) ?></div>
            <div class="md:col-span-1">
              <span class="font-semibold">Status:</span>
              <span class="px-2 py-1 rounded text-white font-semibold
                            <?= ($claim['status'] == 'Pending') ? 'bg-yellow-500' : '' ?>
                            <?= ($claim['status'] == 'Approved') ? 'bg-green-500' : '' ?>
                            <?= ($claim['status'] == 'Rejected') ? 'bg-red-500' : '' ?>">
                <?= htmlspecialchars($claim['status']) ?>
              </span>
            </div>
            <div class="md:col-span-1"><span class="font-semibold">Claimed Date:</span>
              <?= htmlspecialchars($claim['created_at']) ?></div>
            <div class="md:col-span-2"><span class="font-semibold">Item Date:</span>
              <?= htmlspecialchars($claim['item_date'] ?? '-') ?></div>

          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <?php $basePath = "";
  include "components/footer.php"; ?>

</body>

</html>