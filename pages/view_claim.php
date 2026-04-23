<?php
session_start();
require_once("../config.php");

// ---------------- LOGIN CHECK ----------------
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ---------------- VALIDATE TRACKING ID ----------------
$tracking_id = $_GET['tracking_id'] ?? '';
if (!$tracking_id) {
    die("Invalid tracking ID.");
}

// ---------------- FETCH CLAIM ----------------
$stmt = $conn->prepare("
    SELECT 
        c.*,
        li.product_name AS lost_product_name,
        li.description AS lost_description,
        li.last_seen_location AS lost_location,
        fi.product_name AS found_product_name,
        fi.description AS found_description,
        fi.found_location AS found_location
    FROM claims c
    LEFT JOIN lost_items li ON c.item_id = li.id AND c.item_type='Lost'
    LEFT JOIN found_items fi ON c.item_id = fi.id AND c.item_type='Found'
    WHERE c.tracking_id=? AND c.user_id=?
    LIMIT 1
");
$stmt->bind_param("si", $tracking_id, $user_id);
$stmt->execute();
$claim = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$claim) {
    die("Claim not found or you do not have access.");
}

// Determine item details dynamically
$item_name = $claim['lost_product_name'] ?? $claim['found_product_name'] ?? 'N/A';
$item_desc = $claim['lost_description'] ?? $claim['found_description'] ?? 'No description';
$item_location = $claim['lost_location'] ?? $claim['found_location'] ?? 'N/A';
$item_type = $claim['item_type'];
$claim_status = $claim['status'];
$proof_image = $claim['proof_image'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Claim - <?= htmlspecialchars($tracking_id) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php
    $basePath = "";
    include "components/navbar.php";
    ?>

    <div class="max-w-4xl mx-auto p-6 min-h-screen">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">
            Claim Details - <span class="text-green-600"><?= htmlspecialchars($tracking_id) ?></span>
        </h2>

        <div class="bg-white rounded-lg shadow-lg p-6 space-y-4">

            <div class="grid md:grid-cols-2 gap-6">

                <!-- ITEM DETAILS -->
                <div class="space-y-3">
                    <h3 class="text-xl font-bold">Item Information</h3>
                    <p><strong>Type:</strong> <?= htmlspecialchars($item_type) ?></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($item_name) ?></p>
                    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($item_desc)) ?></p>
                    <p><strong>Last Seen / Found Location:</strong> <?= htmlspecialchars($item_location) ?></p>
                </div>

                <!-- CLAIM INFO -->
                <div class="space-y-3">
                    <h3 class="text-xl font-bold">Claim Information</h3>
                    <p><strong>Status:</strong>
                        <span class="px-2 py-1 rounded text-white <?= match($claim_status) {
                            'Approved' => 'bg-green-500',
                            'Rejected' => 'bg-red-500',
                            'Under Review' => 'bg-blue-500',
                            default => 'bg-yellow-500'
                        } ?>"><?= htmlspecialchars($claim_status) ?></span>
                    </p>
                    <p><strong>Reason / Proof:</strong></p>
                    <p class="whitespace-pre-line"><?= htmlspecialchars($claim['reason']) ?></p>

                    <?php if ($proof_image): ?>
                        <div>
                            <strong>Proof Image:</strong>
                            <img src="<?= htmlspecialchars($proof_image) ?>" alt="Proof Image" class="mt-2 rounded-lg w-full object-cover">
                        </div>
                    <?php endif; ?>

                    <p><strong>Submitted At:</strong> <?= date("d M Y, H:i", strtotime($claim['created_at'])) ?></p>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="my_tracking.php"
                   class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition">
                    ← Back to Tracking
                </a>
            </div>
        </div>
    </div>

    <?php
    $basePath = "";
    include "components/footer.php";
    ?>
</body>

</html>