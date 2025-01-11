<?php
    include_once 'header.php';

    // Restrict access to admins only
    if (!$user || $user['rights'] != 'admin') {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    // Validate and create the event
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $date = trim($_POST['date']);
        $startHour = trim($_POST['start_hour']);
        $price = floatval($_POST['price']);
        $categoryId = intval($_POST['category_id']);

        // Insert the event and get the event ID
        $eventId = $db->createEvent($name, $date, $startHour, $price, $categoryId);

        if ($eventId) {
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../img/events/';
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = $eventId . '.' . $fileExtension;
                    $destPath = $uploadDir . $newFileName;                  

                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        Debug::log("Image uploaded as: " . $newFileName);
                    } else {
                        Debug::log("Error moving uploaded file.");
                    }
                } else {
                    Debug::log("Invalid file type. Allowed types: jpg, jpeg, png, gif, webp.");
                }
            }

            header("Location: /ProiectDaw/user-login.php");
            exit();
        } else {
            Debug::log("Error creating event.");
        }
    }
?>