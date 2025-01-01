<?php
    include_once 'modules/header.php';

    include_once 'modules/mail.php';
    $mail = new Mail();

    require_once 'modules/captcha.php';
    $captcha = new Captcha();

    if (!$user) {
        include_once 'modules/login.php';
    } else {
        include_once 'modules/update-account.php';
    }

    include_once 'modules/footer.php';     
?>