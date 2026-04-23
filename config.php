<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "";
$user = "";
$password = "";
$db = "";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$imgbb_api_key = "";
?>

