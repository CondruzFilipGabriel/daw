<?php
    include_once 'modules/header.php';

    if (!$user || $user['rights'] != 'admin') {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) $_POST['id'];
        $name = !empty($_POST['name']) ? sanitize_input($_POST['name']) : null;
        $email = !empty($_POST['email']) ? sanitize_input($_POST['email']) : null;
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        $rights = !empty($_POST['rights']) ? sanitize_input($_POST['rights']) : null;

        // Validam inputurile
        if (!$db->updateUser($id, $name, $email, $password, $rights)) {
            $_SESSION['alert'] = "Eroare in actualizarea userului cu ID $id.";
        }

        header("Location: /ProiectDaw/user-login.php");
        exit();
    }
?>
