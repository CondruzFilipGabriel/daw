<?php
    include_once 'modules/header.php';

    // Verificam daca utilizatorul e autentificat
    if (!$user) {
        header("Location: user-login.php");
        exit;
    }

    // Validam datele introduse
    $numberOfSeats = $_POST['number_of_seats'] ?? null;
    $eventId = $_POST['event_id'] ?? null;
    $eventName = $_POST['event_name'] ?? null;
    $eventPrice = $_POST['event_price'] ?? null;
    $eventDate = $_POST['event_date'] ?? null;

    if (!preg_match('/^\d+$/', $numberOfSeats) || $numberOfSeats < 0 || $numberOfSeats > 300) {
        $_SESSION['alert'] = "Numarul de locuri trebuie sa fie un numar intre 0 si 300.";
        header("Location: index.php");
        exit;
    }

    if($numberOfSeats == 0){
        header("Location: index.php");
        exit;
    }

    // Rezervam locurile
    $reservationSuccess = $db->reserveSeats($eventId, (int)$numberOfSeats, (float)$eventPrice, (int)$user['user_id']);

    if (!$reservationSuccess) {
        $_SESSION['alert'] = "Nu exista suficiente locuri libere pentru rezervarea dumneavoastra.";
        header("Location: index.php");
        exit;
    }

    // Pregatim datele pentru factura si bilete
    $jsonData = json_encode([
        "tickets" => [
            [
                "name" => $eventName,
                "dateTime"=> $eventDate,
                "price" => (float)$eventPrice,
                "quantity" => (int)$numberOfSeats,
                "currency" => "RON"
            ]
            ],
        "total" => (float)$eventPrice * (int)$numberOfSeats
    ]);

    $beneficiar = $user['name'];
    $emailBeneficiar = $user['email'];

    // incarcam modulele necesare si trimitem factura si biletele
    include_once 'modules/trimite.php';
    Trimite::creazaSiTrimiteFacturaSiBilete($jsonData, $user['user_id'], $beneficiar, $emailBeneficiar, $reservationSuccess);

    header("Location: /ProiectDaw/user-login.php");
    exit;
?>