<?php
require_once '../config.php';
require_once '../page_info.php';

$sql = "SELECT * FROM found_items ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $website_name ?> | Found Items </title>
  <link rel="shortcut icon" href="<?= "../" . $website_logo ?>" type="image/x-icon">
  <link rel="stylesheet" href="../style.css">

  <style>
    .page-title {
      text-align: center;
      color: #27ae60;
      font-size: 2rem;
      margin: 20px 0;
    }

    /* Container layout */
    .container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: flex-start;
      gap: 20px;
      margin: 20px;
    }

    /* Card styling */
    .card {
      background: #fff;
      width: 300px;
      height: fit-content;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
    }

    .card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .card-content {
      padding: 15px;
    }

    .card h3 {
      margin: 5px 0;
      color: #27ae60;
      font-size: 1.2rem;
    }

    .card p {
      font-size: 0.95em;
      color: #333;
      margin: 4px 0;
      line-height: 1.4;
    }

    /* Status labels */
    .status {
      display: inline-block;
      padding: 4px 10px;
      font-size: 12px;
      border-radius: 6px;
      color: #fff;
      margin-bottom: 8px;
    }

    .status.unclaimed {
      background-color: #e67e22;
    }

    .status.claimed {
      background-color: #2ecc71;
    }

    /* Buttons */
    .button {
      display: inline-block;
      margin-top: 10px;
      background: #27ae60;
      color: white;
      padding: 8px 14px;
      text-decoration: none;
      border-radius: 6px;
      font-size: 0.9em;
      transition: background 0.3s ease;
    }

    .button:hover {
      background: #1e8d4c;
    }

    /* Disabled button */
    .button.disabled {
      background: gray;
      cursor: not-allowed;
      pointer-events: none;
    }

    /* ------------------ RESPONSIVE DESIGN ------------------ */
    @media (max-width: 768px) {
      .page-title {
        font-size: 1.6rem;
        margin: 15px 0;
      }

      .container {
        gap: 15px;
        margin: 15px;
      }

      .card {
        width: 45%;
        min-width: 250px;
      }

      .card img {
        height: 160px;
      }

      .card h3 {
        font-size: 1.1rem;
      }

      .card p {
        font-size: 0.9em;
      }

      .button {
        font-size: 0.9em;
        padding: 8px 12px;
      }
    }

    @media (max-width: 480px) {
      .page-title {
        font-size: 1.3rem;
        margin: 10px 0;
      }

      .container {
        flex-direction: column;
        align-items: center;
        gap: 15px;
        margin: 10px;
      }

      .card {
        width: 90%;
        max-width: 360px;
      }

      .card img {
        height: 150px;
      }

      .card-content {
        padding: 12px;
      }

      .card h3 {
        font-size: 1rem;
      }

      .card p {
        font-size: 0.85em;
      }

      .button {
        font-size: 0.85em;
        padding: 8px 10px;
      }
    }
  </style>
</head>

<body>

  <nav>
    <div class="logo flex">
      <img src="../img/logo.jpg" alt="Logo">
    </div>

    <ul id="nav-links">
      <li><a href="../index.html" onclick="showSection('home')">Home</a></li>
      <li><a href="report_lost.php" onclick="showSection('report-lost')">Report Lost</a></li>
      <li><a href="report_found.php" onclick="showSection('report-found')">Report Found</a></li>
      <li><a href="discover.php" onclick="showSection('search')">Discover</a></li>
      <li><a href="lost.php" onclick="showSection('search')">Lost Items</a></li>
      <li><a href="found.php" onclick="showSection('search')" class="active">Found Items</a></li>
      <li><a href="track_claim.php" onclick="showSection('search')">Tracking</a></li>
    </ul>

    <div class="menu-toggle" onclick="toggleMenu()">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </nav>


  <header class="flex">
    <div>
      <h1>Lost & Found System</h1>
      <p>Report lost items, submit found items, and help reunite them with their owners!</p>
    </div>
  </header>

  <h1 class="page-title">Found Items</h1>

  <div class="container">
    <?php
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $statusClass = strtolower($row['status']);
        echo "
        <div class='card'>
            <img src='" . (!empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/300x180?text=No+Image') . "' alt='Found Item'>
            <div class='card-content'>
                <span class='status $statusClass'>{$row['status']}</span>
                <h3>{$row['product_name']}</h3>
                <p><strong>Type:</strong> {$row['product_type']}</p>
                <p><strong>Found By:</strong> {$row['found_by']}</p>
                <p><strong>Found Location:</strong> {$row['found_location']}</p>
                <p><strong>Found Date:</strong> {$row['found_date']}</p>
                <p><strong>Unique Marks:</strong> {$row['unique_marks']}</p>
                <p><strong>Description:</strong> {$row['description']}</p>";

        // ✅ Disable or hide claim button when status is "Claimed"
        if (strtolower($row['status']) === 'claimed') {
          echo "<button class='button disabled' title='This item has already been claimed'>Already Claimed</button>";
        } else {
          echo "<a href='claim_item.php?type=found&id={$row['id']}' class='button'>Claim Item</a>";
        }

        echo "
            </div>
        </div>";
      }
    } else {
      echo "<p style='text-align:center;'>No found items have been reported yet.</p>";
    }
    $conn->close();
    ?>
  </div>

  <footer class="flex">
    <p>&copy; 2025 Student Lost & Found<br>admin@university.edu</p>
  </footer>

  <script src="../script.js"></script>
</body>

</html>