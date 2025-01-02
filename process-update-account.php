<?php
    include_once 'modules/header.php';

    if (!$user) {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    $submitted_code = $_POST['verification_code'] ?? '';
    $stored_code = $_SESSION['verification_code'] ?? '';
    $code_expires = $_SESSION['verification_code_expires'] ?? 0;

    if ($submitted_code !== $stored_code || time() > $code_expires) {
        $_SESSION['alert'] = "Codul de verificare este invalid sau a expirat.";
        header("Location: /ProiectDaw/user-login.php");
        exit();
    }

    if ($db->updateUser($user['user_id'], $_SESSION['name'], $_SESSION['email'], $_SESSION['password'])) {
        $_SESSION['alert'] = "Contul a fost actualizat cu succes.";
        header("Location: /ProiectDaw/user-login.php");
    } else {
        $_SESSION['alert'] = "Eroare la actualizarea contului.";
        header("Location: /ProiectDaw/user-login.php");
    }

    exit();
?>