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

    // Șterge contul utilizatorului
    if ($db->deleteUser($user['user_id'])) {
        $_SESSION['alert'] = "Contul a fost șters cu succes.";
        header("Location: /ProiectDaw/logout.php");
    } else {
        $_SESSION['alert'] = "Eroare la ștergerea contului.";
        header("Location: /ProiectDaw/user-login.php");
    }

    exit();
?>
