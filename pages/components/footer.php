<?php
// If basePath not defined, default empty
if (!isset($basePath)) {
    $basePath = "";
}
?>

<link rel="stylesheet" href="<?= $basePath ?>../css/footer.css">

<footer>

    <div class="footer-container">

        <div class="footer-col">
            <h4>Site</h4>
            <a href="<?= $basePath ?>discover.php">Discover</a>
            <a href="<?= $basePath ?>report_lost.php">Report Lost</a>
            <a href="<?= $basePath ?>report_found.php">Report Found</a>
        </div>

        <div class="footer-col">
            <h4>Help</h4>
            <a href="<?= $basePath ?>term-condition.php">Terms & Conditions</a>
            <a href="#">Privacy Policy</a>
        </div>

        <div class="footer-logo">
            <a href="<?= $basePath ?>../index.php">
                <img src="<?= $basePath ?>../<?= $website_logo ?>">
            </a>
        </div>

        <div class="footer-col">
            <h4>Links</h4>
            <a href="https://github.com/rahuljiara">GitHub</a>
            <a href="https://github.com/rahuljiara">About</a>
            <a href="https://github.com/rahuljiara">Feedback</a>
        </div>

        <div class="footer-col">
            <h4>Contact</h4>
            <a href="tel:<?= $website_phone ?>"><?= $website_phone ?></a>
            <a href="mailto:<?= $website_email ?>"><?= $website_email ?></a>
        </div>

    </div>

    <div class="copyright">
        © <span id="copyright-year-container"><?= date("Y") ?></span> <?= $website_name ?>
    </div>

</footer>