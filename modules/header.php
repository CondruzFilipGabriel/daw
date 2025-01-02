<?php
    require_once __DIR__ . '/debug.php';

    include_once __DIR__ . '/../db/db.php';
    $db = DB::getInstance();

    require_once __DIR__ . '/sessions.php';
    $session = new Session();
    $user = $session->retrieve_user_data();

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        // Debug::log("header.php: Generated CSRF Token: " . $_SESSION['csrf_token']);
    }
    
    
    $alert = isset($_SESSION['alert']) ? $_SESSION['alert'] : null;
    unset($_SESSION['alert']); // Clear it after reading
    
    function sanitize_input($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    function validate_name($name) {
        return preg_match('/^[a-zA-Z\s]{1,50}$/', $name);
    }

    function validate_email($email) {
        return preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email);
    }

    function validate_password($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sala Regală de Muzică</title>
    <link rel="icon" href="/ProiectDaw/favicon.ico" type="image/x-icon.">
    <link rel="stylesheet" href="/ProiectDaw/css/header.css">
    <link rel="stylesheet" href="/ProiectDaw/css/index.css">
    <link rel="stylesheet" href="/ProiectDaw/css/footer.css">
    <!-- <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css"> -->
</head>

<body>
    <div class="navbar">
        <a href="index.php">
            <img id="logo" src="/ProiectDaw/img/logo.png" />
        </a>
        <div class="meniu">
            <a href="/ProiectDaw/index.php">
                <h1 id="titlu">Sala Regală de Muzică</h1>
            </a>
            <div id="menu-button">
                <a href="/ProiectDaw/user-login.php" id="user-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="1em" height="1em" fill="var(--shadowcolor)" preserveAspectRatio="xMidYMid meet">
                        <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>