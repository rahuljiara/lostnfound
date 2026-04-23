<?php
session_start();
require_once("../config.php");

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch current user name from DB
$userQuery = $conn->prepare("SELECT full_name, username FROM users WHERE id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userData = $userResult->fetch_assoc();

// Prefer full name, fallback to username
$username = $userData['full_name'] ?: $userData['username'] ?: 'User';

// Main query
$query = "
SELECT 
    c.tracking_id,
    c.item_type,
    c.status AS claim_status,
    c.created_at,

    li.product_name AS lost_product_name,
    li.description AS lost_description,
    li.last_seen_location,

    fi.product_name AS found_product_name,
    fi.description AS found_description,
    fi.found_location

FROM claims c
LEFT JOIN lost_items li 
    ON c.item_id = li.id AND c.item_type = 'Lost'
LEFT JOIN found_items fi 
    ON c.item_id = fi.id AND c.item_type = 'Found'

WHERE c.user_id = ?
ORDER BY c.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Tracking</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Line Clamp Plugin -->
    <script>
        tailwind.config = {
            plugins: [tailwindcssLineClamp],
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/line-clamp@0.4.4/dist/index.min.js"></script>
</head>

<body class="bg-gray-100">
    <?php
    $basePath = "";
    include "components/navbar.php";
    ?>

    <div class="max-w-7xl mx-auto p-6 min-h-screen">

        <!-- Header -->
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">
            Tracking Records -
            <span class="text-green-600 font-semibold">
                <?php echo htmlspecialchars($username); ?>
            </span>
        </h2>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left table-auto">

                    <thead class="bg-green-500 text-white">
                        <tr>
                            <th class="px-4 py-3">Tracking ID</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                        <?php if ($result->num_rows == 0): ?>
                            <tr>
                                <td colspan="8" class="text-center py-6 text-gray-500">
                                    No tracking records found.
                                </td>
                            </tr>
                        <?php else: ?>

                            <?php while ($row = $result->fetch_assoc()):

                                $name = $row['lost_product_name'] ?? $row['found_product_name'] ?? 'N/A';
                                $desc = $row['lost_description'] ?? $row['found_description'] ?? 'No description';
                                $location = $row['last_seen_location'] ?? $row['found_location'] ?? 'N/A';

                                $status = $row['claim_status'];

                                $statusColor = match ($status) {
                                    'Approved' => 'bg-green-500',
                                    'Rejected' => 'bg-red-500',
                                    'Under Review' => 'bg-blue-500',
                                    default => 'bg-yellow-500'
                                };
                                ?>

                                <tr class="hover:bg-gray-50 transition">

                                    <td class="px-4 py-3 font-medium whitespace-nowrap">
                                        <?php echo htmlspecialchars($row['tracking_id']); ?>
                                    </td>

                                    <td class="px-4 py-3">
                                        <?php echo htmlspecialchars($row['item_type']); ?>
                                    </td>

                                    <td class="px-4 py-3">
                                        <?php echo htmlspecialchars($name); ?>
                                    </td>

                                   <td class="px-4 py-3 max-w-xs whitespace-normal break-words">
                                       <?php echo htmlspecialchars($desc); ?>
                                   </td>

                                    <td class="px-4 py-3">
                                        <?php echo htmlspecialchars($location); ?>
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="text-white text-xs px-3 py-1 rounded-full <?php echo $statusColor; ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <?php echo date("d M Y", strtotime($row['created_at'])); ?>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                       <a href="view_claim.php?tracking_id=<?= urlencode($row['tracking_id']) ?>"
   										  class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1.5 rounded-md text-sm font-medium">
  													 View
										</a>
                                    </td>

                                </tr>

                            <?php endwhile; ?>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <?php
    $basePath = "";
    include "components/footer.php";
    ?>

</body>

</html>