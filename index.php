<?php
session_start();
require_once 'config.php';
require 'page_info.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $website_name ?></title>
    <link rel="shortcut icon" href="<? $website_logo ?>" type="image/x-icon">

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/hero.css">

</head>

<body>

    <!-- NAVBAR -->
    <?php
    $basePath = "pages/";
    include "pages/components/navbar.php";
    ?>

    <!-- HERO SECTION -->

    <section class="hero">

        <div class="hero-left">

            <h1>
                Lost & Found <br>
                <span>With Ease</span>
            </h1>

            <p>
                Experience effortless recovery with our dedicated
                lost and found service.
            </p>

            <a href="pages/discover.php" class="btn discover">
                Find Items
                <img src="img/discover-icon.png">
            </a>

        </div>


        <div class="hero-right">

            <div class="action-buttons">

                <a href="<?= $basePath . $reportLostLink ?>" class="btn lost">
                    Report Lost
                    <img src="img/lost-icon.png">
                </a>

                <a href="<?= $basePath . $reportFoundLink ?>" class="btn found">
                    Report Found
                    <img src="img/found-icon.png">
                </a>

            </div>

            <div class="image-stack-container">
                <div class="image-stack">
                    <img src="img/lost and found img.jpg" class="img1">
                    <img src="img/found wallet img.jpg" class="img2">
                    <img src="img/lost phone img.jpg" class="img3">
                </div>
            </div>

        </div>

    </section>



    <!-- FEATURES SECTION -->

    <section class="features">

        <div class="features-header">
            <h2>Why use Lost & Found?</h2>

            <p>
                We make it easy to recover your belongings with powerful tools and a caring community.
            </p>

        </div>


        <div class="features-container">

            <div class="feature-card">
                <div class="feature-icon">⏱</div>
                <h3>Quick Reporting</h3>
                <p>
                    Report lost or found items in under a minute with our streamlined forms.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3>Smart Search</h3>
                <p>
                    Filter by category, location, and date to find matching items instantly.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">👥</div>
                <h3>Community Driven</h3>
                <p>
                    Connect with people who found your items and arrange safe returns.
                </p>
            </div>

        </div>

    </section>



    <!-- HOW IT WORKS -->

    <section class="how-it-works">

        <h2>How it works</h2>

        <div class="steps-container">

            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Report</h3>
                <p>
                    Submit details about your lost or found item with photos and location.
                </p>
            </div>

            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Match</h3>
                <p>
                    Our system helps connect lost items with found reports in the same area.
                </p>
            </div>

            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Recover</h3>
                <p>
                    Claim your item and coordinate a safe return with the finder.
                </p>
            </div>

        </div>

    </section>



    <!-- FOOTER -->
    <?php
    $basePath = "pages/";
    include "pages/components/footer.php";
    ?>


    <script src="script.js"></script>
</body>

</html>