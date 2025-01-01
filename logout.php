<?php
    include_once __DIR__ . '/modules/header.php';

    // Call the session's sign_out method to destroy the session and log the user out
    $session->sign_out();

    // Redirect to the login page
    header("Location: /ProiectDaw/index.php");
    exit();
?>