<?php
    include_once '../modules/header.php';
    include_once '../modules/trimite.php';

    if (!$user) {
        header("Location: /ProiectDaw/user-login.php");
        exit;
    }

    // Get data from the POST request
    $eventName = $_POST['event_name'] ?? null;
    $eventDate = $_POST['event_date'] ?? null;
    $reservedSeats = $_POST['reserved_seats'] ?? null;

    if (!$eventName || !$eventDate || !$reservedSeats) {
        Debug::log("Date invalide primite pentru biletele care trebuiesc descarcate.");
        header("Location: /ProiectDaw/user-login.php");
        exit;
    }

    // Convert reserved seats to an array
    $reservedSeatsArray = explode(',', $reservedSeats);

    // Fetch user details
    $beneficiar = $user['name'];
    $emailBeneficiar = $user['email'];

    // Call the trimiteBilete method
    $trimite = new Trimite();
    $trimite->trimiteBilete($beneficiar, $emailBeneficiar, $eventName, $eventDate, $reservedSeatsArray);

    // Redirect to user-login.php
    header("Location: /ProiectDaw/user-login.php");
    exit;
?>