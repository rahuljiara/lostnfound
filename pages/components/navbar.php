<?php
$isLoggedIn = isset($_SESSION['user_id']);
require_once __DIR__ . '/../../page_info.php';

$navUserImage = "img/default.png";

if (!isset($basePath)) {
    $basePath = "";
}

if ($isLoggedIn):

    $navUser_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->bind_param("i", $navUser_id);
    $stmt->execute();

    $navUserResult = $stmt->get_result();   // FIXED VARIABLE
    $navUser = $navUserResult->fetch_assoc();

    $stmt->close();

    if (!empty($navUser['profile_image'])):
        $navUserImage = $navUser['profile_image'];
    endif;

endif;
$reportLostLink = $isLoggedIn ? "report_lost.php" : "auth/login.php";
$reportFoundLink = $isLoggedIn ? "report_found.php" : "auth/login.php";

?>


<script src="https://cdn.tailwindcss.com"></script>
<nav class="bg-white shadow-md sticky top-0 z-50 min-h-[50px]">

    <div class="max-w-7xl mx-auto px-4">

        <div class="flex justify-between items-center h-16">

            <!-- LOGO -->
            <a href="<?= $basePath ?>../index.php" class="flex items-center space-x-2">

                <img src="<?= $basePath ?>../<?= $website_logo ?>" class="w-9 h-9 rounded-full object-cover">

                <span class="text-xl font-bold text-blue-600">
                    <? $website_name ?>
                </span>

            </a>


            <!-- DESKTOP NAVIGATION -->
            <ul class="hidden md:flex items-center space-x-6 text-gray-700 font-medium">

                <li>
                    <a href="<?= $basePath ?>../index.php" class="hover:text-blue-600">
                        Home
                    </a>
                </li>

                <li>
                    <a href="<?= $basePath ?>discover.php" class="hover:text-blue-600">
                        Discover
                    </a>
                </li>

                <li>
                    <a href="<?= $basePath . $reportLostLink ?>" class="hover:text-blue-600">
                        Report Lost
                    </a>
                </li>

                <li>
                    <a href="<?= $basePath . $reportFoundLink ?>" class="hover:text-blue-600">
                        Report Found
                    </a>
                </li>

                <li>
                    <a href="<?= $basePath ?>track_claim.php" class="hover:text-blue-600">
                        Tracking
                    </a>
                </li>

            </ul>


            <!-- LOGIN / PROFILE -->
            <div class="hidden md:flex items-center">

                <?php if ($isLoggedIn): ?>

                    <a href="<?= $basePath ?>profile.php">

                        <img src="<?= $navUserImage ?>" class="w-9 h-9 rounded-full object-cover border">

                    </a>

                <?php else: ?>

                    <a href="<?= $basePath ?>auth/login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg">

                        Login

                    </a>

                <?php endif; ?>

            </div>


            <!-- MOBILE MENU BUTTON -->
            <button id="menuBtn" class="md:hidden text-2xl text-gray-700">
                ☰
            </button>

        </div>

    </div>


    <!-- MOBILE MENU -->
    <div id="mobileMenu" class="hidden md:hidden bg-white border-t">

        <ul class="flex flex-col p-4 space-y-3">

            <li><a href="<?= $basePath ?>../index.php">Home</a></li>
            <li><a href="<?= $basePath ?>discover.php">Discover</a></li>
            <li><a href="<?= $basePath ?>report_lost.php">Report Lost</a></li>
            <li><a href="<?= $basePath ?>report_found.php">Report Found</a></li>
            <li><a href="<?= $basePath ?>track_claim.php">Tracking</a></li>

        </ul>

    </div>

</nav>

<script>
    const menuBtn = document.getElementById("menuBtn");
    const mobileMenu = document.getElementById("mobileMenu");

    menuBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
    });
</script>