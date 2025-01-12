<?php    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once __DIR__ . '/header.php';

        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['alert'] = "Session expired or invalid request. Please try again.";
            header("Location: /ProiectDaw/user-login.php");
            exit();
        }

        // Sanitize user inputs
        $sanitized_email = sanitize_input($_POST['email']);
        $sanitized_pass = trim($_POST['password']);

        // login
        $user = $session->login($sanitized_email, $sanitized_pass);
    
        // verificam: login success sau nu?
        if ($user) {
            header("Location: /ProiectDaw/index.php");
            $_SESSION['alert'] = null;
            exit();
        } else {
            $_SESSION['alert'] = "Invalid username and/or password!";
            header("Location: /ProiectDaw/user-login.php");
            exit();
        }
    }
?>

<div class="login-form">

    <h3><?= $alert ?></h3>

    <h1>User Login</h1>

    <form action="/ProiectDaw/modules/login.php" method="POST" id="login-form">
        <label for="email">Email:</label>
        <input type="text" id="email" name="email" required><br><br>
    
        <label for="password">Parola:</label>
        <input type="password" id="password" name="password" required><br><br>

        <!-- CSRF Token Inside Form -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    
        <button type="submit" class="event-buton-rezervare">Login</button>
    
    </form>    

    <h5><a href="/ProiectDaw/new-pass.php">
        Am uitat parola...
    </a></h5>
    
    <h4><a href="/ProiectDaw/cont-nou.php">
        Creaza un cont nou
    </a></h4>
</div>