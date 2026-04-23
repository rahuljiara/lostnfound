<?php
session_start();
require_once '../config.php';
require_once '../page_info.php';

$isLoggedIn = isset($_SESSION['user_id']);

$userImage = "img/default.png";

if ($isLoggedIn):

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    if (!empty($user['profile_image'])):
        $userImage = $user['profile_image'];   // URL from database
    endif;

endif;

$reportLostLink = $isLoggedIn ? "report_lost.php" : "auth/login.php";
$reportFoundLink = $isLoggedIn ? "report_found.php" : "auth/login.php";
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $website_name ?> | Terms & Conditions</title>
    <link rel="shortcut icon" href="<?= "../" . $website_logo ?>" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>


</head>

<body class="bg-gray-50 text-gray-700">

    <?php $basePath = "";
    include "components/navbar.php"; ?>

    <!-- Hero -->
    <section class="bg-indigo-600 text-white py-16">
        <div class="max-w-4xl mx-auto text-center px-6">
            <h2 class="text-4xl font-bold mb-4">Terms & Conditions</h2>
            <p class="text-lg opacity-90">
                Please read these terms carefully before using our platform.
                By accessing our services, you agree to follow the rules described below.
            </p>
        </div>
    </section>

    <!-- Content -->
    <section class="max-w-5xl mx-auto px-6 py-12 space-y-10">

        <div>
            <h3 class="text-2xl font-semibold mb-3">1. Acceptance of Terms</h3>
            <p>
                By accessing or using this platform, you agree to comply with these Terms and
                Conditions. If you do not agree with any part of these terms, you must not use
                our services.
            </p>
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-3">2. Description of Service</h3>
            <p>
                Our platform provides a system for users to report lost items and found items.
                We help connect people who have lost belongings with individuals who have
                found them. We do not guarantee that lost items will be recovered.
            </p>
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-3">3. User Responsibilities</h3>
            <ul class="list-disc pl-6 space-y-2">
                <li>You must provide accurate information when reporting items.</li>
                <li>You must not post false, misleading, or fraudulent content.</li>
                <li>You must respect other users and avoid abusive or illegal behavior.</li>
                <li>You are responsible for maintaining the confidentiality of your account.</li>
            </ul>
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-3">4. Data We Collect</h3>
            <p>We may collect the following types of information:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Name and contact details</li>
                <li>Account information</li>
                <li>Uploaded images of lost or found items</li>
                <li>Location details where the item was lost or found</li>
                <li>Technical information such as IP address and browser type</li>
            </ul>
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-3">5. How We Use Your Data</h3>
            <p>Your information may be used to:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Operate and improve our services</li>
                <li>Match lost and found items</li>
                <li>Provide customer support</li>
                <li>Prevent fraud or misuse of the platform</li>
                <li>Send important notifications related to your account</li>
            </ul>
            <p class="mt-3">
                In the future, anonymized data may be used for analytics, research,
                and improving service performance.
            </p>
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-3">6. Data Protection</h3>
            <p>
                We take reasonable security measures to protect your data. However,
                no internet-based system can guarantee complete security.
                Users should avoid sharing sensitive personal information.
            </p>
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-3">7. Intellectual Property</h3>
            <p>
                All content on this platform including design, logos, and software
                belongs to the website owners. You may not copy, distribute,
                or reproduce any material without permission.
            </p>
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-3">8. Limitation of Liability</h3>
            <p>
                We are not responsible for any loss, damage, or dispute arising
                between users. The platform acts only as an intermediary to
                facilitate communication.
            </p>
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-3">9. Account Termination</h3>
            <p>
                We reserve the right to suspend or terminate accounts that violate
                these terms, provide false information, or misuse the platform.
            </p>
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-3">10. Changes to Terms</h3>
            <p>
                These terms may be updated periodically. Continued use of the
                platform after updates means you accept the revised terms.
            </p>
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-3">11. Contact Us</h3>
            <p>
                If you have questions about these Terms & Conditions, please contact us
                at: <a href="mailto:<?= $website_email ?>"><span class="font-medium text-indigo-600">
                        <?= $website_email ?>
                    </span></a>
            </p>
        </div>

    </section>

    <?php $basePath = "";
    include "components/footer.php"; ?>

    <script src="../script.js"></script>

</body>

</html>