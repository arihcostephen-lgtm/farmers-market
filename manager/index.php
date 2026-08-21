<?php
session_start();
ob_start();

if (empty($_SESSION['user_id']) || empty($_SESSION['user_email']) || empty($_SESSION['role']) || !in_array((int) $_SESSION['role'], [4, 5], true)) {
    header('Location: login.php');
    exit;
}

header('Location: dashboard.php');
exit;
