<?php
require_once "../config.php";
require_once "../page_info.php";

/* ---------------- FETCH LOST AND FOUND ITEMS ---------------- */

// Lost items query
$lost_sql = "
SELECT 
    id,
    product_name,
    product_type,
    lost_by AS person,
    missing_date AS date,
    unique_marks,
    last_seen_location AS location,
    description,
    image_url,
    status,
    'Lost' AS category
FROM lost_items
";

// Found items query (FIXED)
$found_sql = "
SELECT 
    f.id,
    f.product_name,
    f.product_type,
    IFNULL(u.full_name, 'Unknown') AS person,
    f.found_date AS date,
    f.unique_marks,
    f.found_location AS location,
    f.description,
    f.image_url,
    f.status,
    'Found' AS category
FROM found_items f
LEFT JOIN users u ON f.user_id = u.id
";

// Combine using UNION ALL (FIXED)
$sql = "
($lost_sql)
UNION ALL
($found_sql)
ORDER BY date DESC
";

$result = $conn->query($sql);

if (!$result) {
  die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $website_name ?> | Discover</title>
  <link rel="shortcut icon" href="<?="../". $website_logo ?>" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

  <?php include "./components/navbar.php"; ?>

  <header class="bg-blue-600 text-white py-10 text-center shadow">
    <h1 class="text-3xl font-bold">Lost & Found System</h1>
    <p class="mt-2 text-blue-100">
      Report lost items, submit found items, and help reunite them with their owners!
    </p>
  </header>

  <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">

    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <?php
        $image = !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : "https://via.placeholder.com/300x200";
        $type = strtolower($row['category']);
        $badgeClass = $row['category'] === 'Lost' ? "bg-red-100 text-red-600" : "bg-green-100 text-green-600";
        ?>
        <a href="item_details.php?type=<?= $type ?>&id=<?= $row['id'] ?>">
          <div class="bg-white rounded-xl shadow-md hover:shadow-xl hover:-translate-y-1 transition overflow-hidden">
            <img src="<?= $image ?>" class="w-full h-48 object-cover">
            <div class="p-4">
              <span class="text-xs font-semibold px-2 py-1 rounded <?= $badgeClass ?>">
                <?= $row['category'] ?>
              </span>
              <h3 class="text-lg font-semibold mt-2"><?= $row['product_name'] ?></h3>
              <p class="text-sm text-gray-600 mt-1">Type: <?= $row['product_type'] ?></p>
              <p class="text-sm text-gray-600">Reported By: <?= $row['person'] ?></p>
              <p class="text-sm text-gray-600">Location: <?= $row['location'] ?></p>
              <p class="text-sm text-gray-600">Date: <?= $row['date'] ?></p>
              <p class="text-sm font-semibold mt-1 text-gray-800">Status: <?= $row['status'] ?></p>
            </div>
          </div>
        </a>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="col-span-full text-center text-gray-600">No items available yet</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
  </div>

  <?php include "components/footer.php"; ?>

</body>

</html>