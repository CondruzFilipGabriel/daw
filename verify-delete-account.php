<?php
    include_once 'modules/header.php';
    
    include_once 'modules/mail.php';

    // Verifică dacă utilizatorul este autentificat
    if (!$user) {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    // Generează un cod de verificare
    $verification_code = bin2hex(random_bytes(4));
    $_SESSION['verification_code'] = $verification_code;
    $_SESSION['verification_code_expires'] = time() + 300; // Cod valabil 5 minute
    

    // Trimite codul de verificare pe email
    $mail = new Mail();
    $subject = "Cod de verificare - Stergere cont";
    $body = "Codul dumneavoastra de verificare este: <strong>$verification_code</strong>. Este valabil timp de 5 minute.";
    $mail->send($user['email'], $subject, $body);

    // Afișează formularul de confirmare a codului
?>

<h3><?= $alert ?></h3>

<div class="login-form">
    <h3>Introduceți codul de verificare primit pe email pentru a confirma ștergerea contului.</h3>
    <form action="/ProiectDaw/process-delete-account.php" method="POST">
        <label for="verification_code">Cod de verificare:</label>
        <input type="text" id="verification_code" name="verification_code" required>
        <br><br>
        <button type="submit" class="event-buton-rezervare red-text">Confirmă ștergerea</button>
    </form>
</div>
<?php
    include_once 'modules/footer.php';
?>
