<?php
    include_once 'header.php';

    // Restrict access to admins only
    if (!$user || $user['rights'] != 'admin') {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    // Validate and delete the event
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $eventId = intval($_POST['id']);
        $uploadDir = __DIR__ . '/../img/events/';
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // Delete image files related to the event
        foreach ($allowedExtensions as $ext) {
            $filePath = $uploadDir . $eventId . '.' . $ext;
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    Debug::log("Deleted image: " . $filePath);
                } else {
                    Debug::log("Failed to delete image: " . $filePath);
                }
            }
        }

        // Delete event from the database
        if ($db->deleteEvent($eventId)) {
            header("Location: /ProiectDaw/user-login.php");
            exit();
        } else {
            Debug::log("Error deleting event.");
        }
    }
?>