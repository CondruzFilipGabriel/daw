<?php
    include_once 'header.php';

    // Admins only
    if (!$user || $user['rights'] != 'admin') {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    // Validam si stergem eventul
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $eventId = intval($_POST['id']);
        $uploadDir = __DIR__ . '/../img/events/';
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // Stergem eventuala imagine dedicata a eventului
        foreach ($allowedExtensions as $ext) {
            $filePath = $uploadDir . $eventId . '.' . $ext;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Stergem eventul din BD
        if ($db->deleteEvent($eventId)) {
            header("Location: /ProiectDaw/user-login.php");
            exit();
        } else {
            Debug::log("Eroare la stergerea eventului.");
        }
    }
?>