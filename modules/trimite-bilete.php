<?php
    include_once '../modules/header.php';
    include_once '../modules/trimite.php';

    if (!$user) {
        header("Location: /ProiectDaw/user-login.php");
        exit;
    }

    // Preluam datele din POST request
    $eventName = $_POST['event_name'] ?? null;
    $eventDate = $_POST['event_date'] ?? null;
    $reservedSeats = $_POST['reserved_seats'] ?? null;

    if (!$eventName || !$eventDate || !$reservedSeats) {
        Debug::log("Date invalide primite pentru biletele care trebuiesc descarcate.");
        header("Location: /ProiectDaw/user-login.php");
        exit;
    }

    // Convertim locurile rezervate intr-un array
    $reservedSeatsArray = explode(',', $reservedSeats);

    // Preluam datele userului
    $beneficiar = $user['name'];
    $emailBeneficiar = $user['email'];

    // Trimitem biletele cu metoda trimiteBilete()
    $trimite = new Trimite();
    $trimite->trimiteBilete($beneficiar, $emailBeneficiar, $eventName, $eventDate, $reservedSeatsArray);

    // Redirect la user-login.php
    header("Location: /ProiectDaw/user-login.php");
    exit;
?>