<?php
    include_once 'header.php';

    // Admins only
    if (!$user || $user['rights'] != 'admin') {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    // Validam continutul si il actualizam (daca e cazul)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $eventId = intval($_POST['id']);
        $name = trim($_POST['name']);
        $date = trim($_POST['date']);
        $startHour = trim($_POST['start_hour']);
        $price = floatval($_POST['price']);
        $categoryId = intval($_POST['category_id']);

        // Gestionam upload-ul imaginilor (nu si in baza de date)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../img/events/';
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = $eventId . '.' . $fileExtension;
                $destPath = $uploadDir . $newFileName;

                // Stergem imaginile existente
                foreach ($allowedExtensions as $ext) {
                    $existingFile = $uploadDir . $eventId . '.' . $ext;
                    if (file_exists($existingFile)) {
                        unlink($existingFile);
                    }
                }

                if (!move_uploaded_file($fileTmpPath, $destPath)) {
                    Debug::log("Eroare la scrierea fisierelor uploadate.");
                }
            } else {
                Debug::log("Tip de imagine invalid. Tipuri permise: jpg, jpeg, png, gif, webp.");
            }
        }

        // Actualizam detaliile eventului (fara sa modificam campul imagine)
        if ($db->updateEvent($eventId, $name, $date, $startHour, $price, $categoryId, null)) {
            header("Location: /ProiectDaw/user-login.php");
            exit();
        } else {
            Debug::log("Eroare la actulizarea eventului.");
        }
    }
?>