<?php
    include_once 'modules/header.php';

    include_once 'modules/mail.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = sanitize_input($_POST['name']);
        if(!validate_name($name)) {
            $_SESSION['alert'] = "Numele nu este valid. Numele poate contine doar litere mari, mici si spatiu si maxim 50 de caractere";
            header("Location: cont-nou.php");
            exit();
        }
        $_SESSION['name'] = $name;

        $email = sanitize_input($_POST['email']);
        if(!validate_email($email)) {
            $_SESSION['alert'] = "Emailul nu este valid";
            header("Location: cont-nou.php");
            exit();
        }

        if ($db->emailExists($email)) {
            $_SESSION['alert'] = "Exista deja un cont cu aceasta adresa de email.";
            header("Location: /ProiectDaw/cont-nou.php");
            exit();
        }
        $_SESSION['email'] = $email;

        // Generate verification code
        $verificationCode = rand(100000, 999999);
        $_SESSION['security_code'] = $verificationCode;
        $_SESSION['security_code_expiry'] = time() + 300; // Valid for 5 minutes
        
        // Send email
        $mail = new Mail();
        $subject = "Cod de verificare pentru creare utilizator";
        $body = "Buna, $name! <br><br> Codul tau de verificare este: <b>$verificationCode</b><br><br> Este valabil timp de 5 minute.";
        
        if ($mail->send($email, $subject, $body)) {
            header("Location: signup-final.php");
            exit();
        } else {
            $_SESSION['alert'] = "Nu s-a reusit trimiterea emailului. Va rugam reincercati.";
        }
    }
?>

<div class="login-form">    
    <h3><?= $alert ?></h3>

    <h3>Creaza utilizator nou</h3>
    <form method="POST">
        <label for="name">Nume:</label>
        <input type="text" id="name" name="name" required><br><br>
    
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
    
        <button type="submit" class="event-buton-rezervare">Trimite email de verificare</button>
    </form>
</div>

<?php
    include_once 'modules/footer.php';     
?>