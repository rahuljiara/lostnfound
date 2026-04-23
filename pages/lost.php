<?php
require_once '../config.php';
require_once '../page_info.php';

$sql = "SELECT * FROM lost_items ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $website_name ?> | Lost Items</title>
  <link rel="shortcut icon" href="<?= "../" . $website_logo ?>" type="image/x-icon">
  <link rel="stylesheet" href="../style.css">
  <style>
    .page-title {
      text-align: center;
      color: #007bff;
      font-size: 2rem;
      margin-top: 20px;
    }

    /* Container layout */
    .container {
      display: flex;
      flex-wrap: wrap;
      /* ✅ allows cards to wrap on smaller screens */
      justify-content: center;
      align-items: flex-start;
      gap: 20px;
      margin: 20px;
    }

    /* Card styling */
    .card {
      max-width: 300px;
      width: 90%;
      height: fit-content;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
    }

    /* Image */
    .card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    /* Card content */
    .card-content {
      padding: 15px;
    }

    .card h3 {
      margin: 5px 0;
      color: #007bff;
      font-size: 1.2rem;
    }

    .card p {
      font-size: 0.95em;
      color: #333;
      margin: 4px 0;
      line-height: 1.4;
    }

    /* Status tags */
    .status {
      display: inline-block;
      padding: 4px 10px;
      font-size: 12px;
      border-radius: 6px;
      color: #fff;
      margin-bottom: 8px;
    }

    .status.open {
      background-color: #e74c3c;
      /* red */
    }

    .status.found {
      background-color: #27ae60;
      /* green */
    }

    .status.closed {
      background-color: #95a5a6;
      /* grey */
    }

    /* Button */
    .button {
      display: inline-block;
      margin-top: 10px;
      background: #007bff;
      color: white;
      padding: 8px 14px;
      text-decoration: none;
      border-radius: 6px;
      font-size: 0.9em;
      transition: background 0.3s ease;
    }

    .button:hover {
      background: #0056b3;
    }

    /* ------------------ RESPONSIVE DESIGN ------------------ */

    /* Tablet screens (up to 768px) */
    @media (max-width: 768px) {
      h1 {
        font-size: 1.7rem;
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
    }

    /* Mobile screens (up to 480px) */
    @media (max-width: 480px) {
      h1 {
        font-size: 1.4rem;
      }

      .container {
        flex-direction: column;
        align-items: center;
        margin: 10px;
        gap: 15px;
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
        padding: 8px 12px;
      }
    }

    @media (max-width: 280px) {
      .card {
        zoom: .5;
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
      <li><a href="lost.php" onclick="showSection('search')" class="active">Lost Items</a></li>
      <li><a href="found.php" onclick="showSection('search')">Found Items</a></li>
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


  <h1 class="page-title">Lost Items</h1>
  <div class="container">
    <?php
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $statusClass = strtolower($row['status']);
        echo "
        <div class='card'>
            <img src='" . (!empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/300x180?text=No+Image') . "' alt='Lost Item'>
            <div class='card-content'>
                <span class='status $statusClass'>{$row['status']}</span>
                <h3>{$row['product_name']}</h3>
                <p><strong>Type:</strong> {$row['product_type']}</p>
                <p><strong>Lost By:</strong> {$row['lost_by']}</p>
                <p><strong>Last Seen:</strong> {$row['last_seen_location']}</p>
                <p><strong>Date:</strong> {$row['missing_date']}</p>
                <p><strong>Marks:</strong> {$row['unique_marks']}</p>
                <p><strong>Description:</strong> {$row['description']}</p>
            </div>
        </div>";
      }
    } else {
      echo "<p style='text-align:center;'>No lost items reported yet.</p>";
    }
    $conn->close();
    ?>
  </div>

  <footer class="flex">
    <p>&copy; 2025 Student Lost & Found
      <br>
      admin@university.edu
    </p>
  </footer>

  <script src="../script.js"></script>
</body>

</html>