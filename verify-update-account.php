<?php
    include_once 'modules/header.php';
    include_once 'modules/mail.php';

    if (!$user) {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $name = sanitize_input($_POST['name']);
        if(!validate_name($name)) {
            $_SESSION['alert'] = "Numele nu este valid. Numele poate contine doar litere mari, mici si spatiu si maxim 50 de caractere";
            header("Location: user-login.php");
            exit();
        }
        $_SESSION['name'] = $name;

        $email = sanitize_input($_POST['email']);
        if(!validate_email($email)) {
            $_SESSION['alert'] = "Emailul nu este valid";
            header("Location: user-login.php");
            exit();
        }

        if ($email !== $user['email'] && $db->emailExists($email)) {
            $_SESSION['alert'] = "Exista deja un cont cu aceasta adresa de email.";
            header("Location: user-login.php");
            exit();
        }        
        $_SESSION['email'] = $email;

        // Sanitize inputs
        $password = trim($_POST['pass1']);
        $confirmPassword = trim($_POST['pass2']);

        // Validam parolele
        if ($password && $confirmPassword && $password !== $confirmPassword) {
            $_SESSION['alert'] = "Parolele nu se potrivesc.";
            header("Location: user-login.php");
            exit();
        }

        if ($password && $confirmPassword && !validate_password($password)) {
            $_SESSION['alert'] = "Parola este prea simpla (1 litera mica, 1 litera mare, 1 cifra si un alt semn).";
            header("Location: user-login.php");
            exit();
        }

        // Hash the password
        $_SESSION['password'] = password_hash($password, PASSWORD_DEFAULT);

        // Generează un cod de verificare
        $verification_code = bin2hex(random_bytes(4));
        $_SESSION['verification_code'] = $verification_code;
        $_SESSION['verification_code_expires'] = time() + 300; // Cod valabil 5 minute

        // Trimite codul de verificare pe email
        $mail = new Mail();
        $subject = "Cod de verificare - Actualizare cont";
        $body = "Codul dumneavoastră de verificare este: <strong>$verification_code</strong>. Este valabil timp de 5 minute.";
        $mail->send($user['email'], $subject, $body);
    }
?>

<h3><?= $alert ?></h3>

<div class="login-form">
    <h3>Introduceți codul de verificare primit pe email pentru a continua.</h3>
    <form action="/ProiectDaw/process-update-account.php" method="POST">
        <label for="verification_code">Cod de verificare:</label>
        <input type="text" id="verification_code" name="verification_code" required>
        <br><br>
        <button type="submit" class="event-buton-rezervare">Confirmă</button>
    </form>
</div>
<?php
    include_once 'modules/footer.php';
?>
