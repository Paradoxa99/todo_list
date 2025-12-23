<?php
require_once 'functions.php';

// Check if user is logged in
if (isLoggedIn()) {
    // If logged in, redirect to dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // If not logged in, redirect to login page
    header('Location: login.php');
    exit;
}
?>
