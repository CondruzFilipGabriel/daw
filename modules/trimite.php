<?php
    include_once __DIR__ . '/header.php';
    include_once __DIR__ . '/bilet.php';
    include_once __DIR__ . '/factura.php';
    include_once __DIR__ . '/mail.php';

    class Trimite {

        public static function creazaSiTrimiteFacturaSiBilete($jsonData, $userId, $beneficiar, $emailBeneficiar, $reservedSeats) {
            // Decode JSON data
            $data = json_decode($jsonData, true);

            // Generate the invoice
            $factura = new Factura();
            $filePath = $factura->creazaFactura($jsonData, $userId, $beneficiar);

            // Generate the tickets
            $bilet = new Bilet();
            $tickets = [];
            $seatIndex = 0;

            foreach ($data['tickets'] as $ticket) {
                $showName = $ticket['name'];
                $showDate = $ticket['dateTime'];
                $quantity = $ticket['quantity'];

                for ($i = 0; $i < $quantity; $i++) {
                    if (isset($reservedSeats[$seatIndex])) {
                        $seat = $reservedSeats[$seatIndex];
                        $pdfString = $bilet->genereazaBilet($showName, $showDate, $seat);
                        $tickets[] = [
                            'data' => $pdfString,
                            'name' => "bilet_$seat.pdf"
                        ];
                        $seatIndex++;
                    } else {
                        Debug::log("Nepotrivire intre numarul de locuri si lista locurilor rezervates.");
                    }
                }
            }

            // Create the email body
            $emailBody = "<p>Draga <b>$beneficiar</b></p> 
                          <p>Atasat puteti descarca " .
                          (count($reservedSeats) > 1 ? "biletele si factura fiscala" : "biletul si factura fiscala") . " aferenta platii acestora.</p> 
                          <p>Va multumim ca ati ales Sala Regala de Muzica!</p> 
                          <p>Va dorim o experienta cat mai placuta!</p>";

            // Send the email
            $mail = new Mail();
            $attachments = [$filePath];
            $sent = $mail->send(
                $emailBeneficiar,
                "Sala Regala de Muzica: " . (count($reservedSeats) > 1 ? "biletele si factura fiscala" : "biletul si factura fiscala"),
                $emailBody,
                $attachments,
                $tickets
            );

            if ($sent) {
                header("Location: /ProiectDaw/user-login.php");
                exit;
            } else {
                Debug::log("Failed to send email (creazaSiTrimiteFacturaSiBilete) to $emailBeneficiar");
            }
        }

        public function trimiteBilete($beneficiar, $emailBeneficiar, $reservedSeats) {
            // Generate the tickets
            $bilet = new Bilet();
            $tickets = [];
            foreach ($reservedSeats as $seat) {
                $pdfString = $bilet->genereazaBilet(1, 'Concertul Anului', $seat);
                $tickets[] = [
                    'data' => $pdfString,
                    'name' => "bilet_$seat.pdf"
                ];
            }

            // Create the email body
            $emailBody = "<p>Draga <b>$beneficiar</b></p> 
                          <p>Atasat puteti descarca " .
                          (count($reservedSeats) > 1 ? "biletele dumneavoastra" : "biletul dumneavoastra") . ".</p> 
                          <p>Va multumim ca ati ales Sala Regala de Muzica!</p> 
                          <p>Va dorim o experienta cat mai placuta!</p>";

            // Send the email
            $mail = new Mail();
            $sent = $mail->send(
                $emailBeneficiar,
                "Sala Regala de Muzica: " . (count($reservedSeats) > 1 ? "biletele dumneavoastra" : "biletul dumneavoastra"),
                $emailBody,
                [],
                $tickets
            );

            if ($sent) {
                header("Location: /ProiectDaw/user-login.php");
                exit;
            } else {
                Debug::log("Failed to send email to $emailBeneficiar");
            }
        }
    }
?>