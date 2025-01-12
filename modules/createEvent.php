<?php
    include_once 'header.php';

    // Admins only
    if (!$user || $user['rights'] != 'admin') {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    // Validam si cream eventul
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $date = trim($_POST['date']);
        $startHour = trim($_POST['start_hour']);
        $price = floatval($_POST['price']);
        $categoryId = intval($_POST['category_id']);

        // Inseram eventul si returnam id-ul (folosit pentru eventuala imagine dedicata)
        $eventId = $db->createEvent($name, $date, $startHour, $price, $categoryId);

        if ($eventId) {
            // Uploadam imaginea (daca exista)
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../img/events/';
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = $eventId . '.' . $fileExtension;
                    $destPath = $uploadDir . $newFileName;                  
                    $successfulMove = move_uploaded_file($fileTmpPath, $destPath);
                    
                    if (!$successfulMove) {
                        Debug::log("Erroare la mutarea sau uploadarea fisierului.");
                    }
                } else {
                    Debug::log("Tip invalid de imagine introdus. Sunt permise doar: jpg, jpeg, png, gif, webp.");
                }
            }
            header("Location: /ProiectDaw/user-login.php");
            exit();
        } else {
            Debug::log("Eroare in crearea eventului.");
        }
    }
?>