<?php
    include_once 'header.php';

    // Restrict access to admins only
    if (!$user || $user['rights'] != 'admin') {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    // Validate and update the event
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $eventId = intval($_POST['id']);
        $name = trim($_POST['name']);
        $date = trim($_POST['date']);
        $startHour = trim($_POST['start_hour']);
        $price = floatval($_POST['price']);
        $categoryId = intval($_POST['category_id']);

        // Handle image upload (no need to update the DB)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../img/events/';
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = $eventId . '.' . $fileExtension;
                $destPath = $uploadDir . $newFileName;

                // Remove any existing image
                foreach ($allowedExtensions as $ext) {
                    $existingFile = $uploadDir . $eventId . '.' . $ext;
                    if (file_exists($existingFile)) {
                        unlink($existingFile);
                    }
                }

                if (!move_uploaded_file($fileTmpPath, $destPath)) {
                    Debug::log("Error moving uploaded file.");
                }
            } else {
                Debug::log("Invalid file type. Allowed types: jpg, jpeg, png, gif, webp.");
            }
        }

        // Update event details (without touching the image field)
        if ($db->updateEvent($eventId, $name, $date, $startHour, $price, $categoryId, null)) {
            header("Location: /ProiectDaw/user-login.php");
            exit();
        } else {
            Debug::log("Error updating event.");
        }
    }
?>