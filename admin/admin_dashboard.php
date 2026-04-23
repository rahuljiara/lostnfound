<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

require_once '../config.php';

if (!$conn) {
    die("DB connection failed");
}

// -------------------- ACTION HANDLERS --------------------

// DELETE
if (isset($_GET['delete']) && isset($_GET['table'])) {
    $table = in_array($_GET['table'], ['lost_items', 'found_items', 'claims']) ? $_GET['table'] : '';
    $id = intval($_GET['delete']);

    if ($table) {
        $conn->query("DELETE FROM $table WHERE id=$id") or die($conn->error);
        echo "<script>alert('✅ Record deleted successfully!'); window.location='admin_dashboard.php';</script>";
        exit;
    }
}

// APPROVE
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE claims SET status='Approved' WHERE id=$id") or die($conn->error);
    echo "<script>alert('✅ Claim approved!'); window.location='admin_dashboard.php';</script>";
    exit;
}

// REJECT
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $conn->query("UPDATE claims SET status='Rejected' WHERE id=$id") or die($conn->error);
    echo "<script>alert('❌ Claim rejected!'); window.location='admin_dashboard.php';</script>";
    exit;
}

// STATUS UPDATE
if (isset($_GET['update_status']) && isset($_GET['table']) && isset($_GET['status'])) {
    $table = in_array($_GET['table'], ['lost_items', 'found_items']) ? $_GET['table'] : '';
    $id = intval($_GET['update_status']);
    $status = $conn->real_escape_string($_GET['status']);

    if ($table) {
        $conn->query("UPDATE $table SET status='$status' WHERE id=$id") or die($conn->error);
        echo "<script>alert('✅ Status updated successfully!'); window.location='admin_dashboard.php';</script>";
        exit;
    }
}

// -------------------- FETCH DATA --------------------
$lost = $conn->query("SELECT * FROM lost_items ORDER BY id DESC") or die($conn->error);
$found = $conn->query("SELECT * FROM found_items ORDER BY id DESC") or die($conn->error);
$claims = $conn->query("SELECT * FROM claims ORDER BY id DESC") or die($conn->error);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Lost&Found - Admin Dashboard</title>
    <link rel="shortcut icon" href="../img/logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <header>
        <h1>Admin Panel</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span>Welcome, <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong></span>
            <a href="logout.php"><button>Logout</button></a>
        </div>
    </header>

    <div class="container">

        <h2>Add New Lost Item</h2>
        <form method="POST" enctype="multipart/form-data" class="add-item">
            <input type="text" name="product_name" placeholder="Product Name" required>
            <input type="text" name="product_type" placeholder="Type (e.g. Phone, Bag)" required>
            <input type="text" name="lost_by" placeholder="Lost By" required>
            <input type="date" name="missing_date" required>
            <input type="text" name="unique_marks" placeholder="Unique Marks">
            <input type="text" name="last_seen_location" placeholder="Last Seen Location">
            <input type="text" name="description" placeholder="Description">
            <input type="file" name="image" required>
            <button type="submit" name="add_lost" class="submit-btn">Add Lost Item</button>
        </form>

        <?php
        if (isset($_POST['add_lost'])) {

            $product_name = $_POST['product_name'];
            $product_type = $_POST['product_type'];
            $lost_by = $_POST['lost_by'];
            $missing_date = $_POST['missing_date'];
            $unique_marks = $_POST['unique_marks'];
            $last_seen = $_POST['last_seen_location'];
            $description = $_POST['description'];

            // ✅ SAFE LOCAL IMAGE UPLOAD
            $image_url = "";

            if (!empty($_FILES['image']['name'])) {

                $upload_dir = "uploads/";

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_name = time() . "_" . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = $target_file;
                } else {
                    echo "<script>alert('⚠️ Image upload failed');</script>";
                }
            }

            if (!empty($image_url)) {

                $stmt = $conn->prepare("INSERT INTO lost_items 
                (product_name, product_type, lost_by, missing_date, unique_marks, last_seen_location, description, image_url, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Open')");

                $stmt->bind_param("ssssssss", $product_name, $product_type, $lost_by, $missing_date, $unique_marks, $last_seen, $description, $image_url);

                if ($stmt->execute()) {
                    echo "<script>alert('✅ Lost item added successfully!'); window.location='admin_dashboard.php';</script>";
                } else {
                    die("DB ERROR: " . $stmt->error);
                }

                $stmt->close();
            }
        }
        ?>

        <h2>Lost Items</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $lost->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['product_type']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <a class="btn btn-approve" href="?update_status=<?= $row['id'] ?>&table=lost_items&status=Found">Mark as Found</a>
                        <a class="btn btn-reject" href="?update_status=<?= $row['id'] ?>&table=lost_items&status=Closed">Close</a>
                        <a class="btn btn-delete" href="?delete=<?= $row['id'] ?>&table=lost_items">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h2>Found Items</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $found->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['product_type']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <a class="btn btn-approve" href="?update_status=<?= $row['id'] ?>&table=found_items&status=Claimed">Mark as Claimed</a>
                        <a class="btn btn-reject" href="?update_status=<?= $row['id'] ?>&table=found_items&status=Returned">Mark as Returned</a>
                        <a class="btn btn-delete" href="?delete=<?= $row['id'] ?>&table=found_items">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h2>Claims Management</h2>
        <table>
            <tr>
                <th>Item ID</th>
                <th>Tracking ID</th>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $claims->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['item_id'] ?></td>
                    <td><?= $row['tracking_id'] ?></td>
                    <td><?= $row['user_id'] ?></td>
                    <td><?= $row['user_name'] ?></td>
                    <td><?= $row['user_email'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td>
                        <a class="btn btn-approve" href="?approve=<?= $row['id'] ?>">Approve</a>
                        <a class="btn btn-reject" href="?reject=<?= $row['id'] ?>">Reject</a>
                        <a class="btn btn-delete" href="?delete=<?= $row['id'] ?>&table=claims">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

    </div>
</body>
</html>