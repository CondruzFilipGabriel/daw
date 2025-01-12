<?php 
    include 'header.php'; 
    include 'mail.php'; 
    $mail = new Mail();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Folosim adresa de email a administratorului pentru a primi mesajul
        $data = json_decode(file_get_contents(__DIR__ . '/../credentials.json'), true);
        $admin_email = $data['admin_email'];
    
        // Verificam adresa de email introduse de user
        $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : $user['name'];
        $user_email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : $user['email'];
        if(!validate_email($user_email)){
            $_SESSION['alert'] = "Email invalid. Va rog corectati adresa de email.";
            header("Location: /ProiectDaw/modules/contact.php");
        }

        // Verificam ca mesajul sa fie ok
        $message = sanitize_input(htmlspecialchars($_POST['message']));
        if(strlen($message) < 1) {
            $_SESSION['alert'] = "Mesaj invalid..";
            header("Location: /ProiectDaw/modules/contact.php");
        }
    
        $subiect_site = "Mesaj noi de la $name ($user_email)";
        $body_site = "
                        Mesaj nou de la<br>
                        Nume: $name<br>
                        Email: $user_email<br><br><br>
                        <b>$message</b>
                    ";
                    
        $subiect_client = "Mesaj catre Sala REgala de Muzica expediat";
        $body_client = "
                        Va multumim pentru mesajul transmis.<br>
                        Daca nu ati transmis niciun mesaj, puteti trimite o sesizare la $admin_email.<br>
                        Va dorim o zi frumoasa in continuare!
                      ";
    
        // Verificam ca s-au trimis emailurile
        $sent1 = $mail->send($admin_email, $subiect_site, $body_site);
        $sent2 = $mail->send($user_email, $subiect_client, $body_client);
        if($sent1 && $sent2) {
            $_SESSION['alert'] = "Mesajul dumneavoastra a fost transmis. Veti primi o confirmare pe adresa de email.";
            header("Location: /ProiectDaw/index.php");
        } else {
            $_SESSION['alert'] = "Mesajul din formular nu a putut fi transmis";
            header("Location: /ProiectDaw/modules/contact.php");
        }
    }
?>

<h3><?= $alert ?></h3>

<div class="contactFormContainer">
    <h2>Contactati-ne</h2>
    <form id="contactForm" method="POST" action="">
        <?php if (!$user): ?>
            <input type="text" name="name" placeholder="Nume" required>
            <input type="email" name="email" placeholder="Email" required>
        <?php endif; ?>
        <textarea name="message" placeholder="Mesaj:" required></textarea>
        <button type="submit" class="event-buton-rezervare">Trimite mesaj</button>
    </form>
</div>

<?php include 'footer.php'; ?>