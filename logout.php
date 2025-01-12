<?php
    include_once __DIR__ . '/modules/header.php';

    if (!$user) {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    $session->sign_out();

    header("Location: /ProiectDaw/index.php");
    exit();
?>