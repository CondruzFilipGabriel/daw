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

        // Validate inputs if needed
        if (!$db->updateUser($id, $name, $email, $password, $rights)) {
            $_SESSION['alert'] = "Failed to update user with ID $id.";
        } else {
            $_SESSION['alert'] = "User with ID $id updated successfully.";
        }

        header("Location: /ProiectDaw/user-login.php");
        exit();
    }
?>
