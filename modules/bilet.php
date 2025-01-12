<?php
    require_once __DIR__ . '/../fpdf/fpdf.php';
    require_once __DIR__ . '/../vendor/autoload.php';

    use chillerlan\QRCode\QRCode;
    use chillerlan\QRCode\QROptions;

    class Bilet {

        public function genereazaBilet($showName, $showDate, $seatNumber) {
            // Se pregatesc datele pentru codul QR
            $qrData = "Show Name: $showName\nDate: $showDate\nSeat: $seatNumber";
        
            // Optiunile pentru codul QR
            $options = new QROptions([
                'eccLevel' => QRCode::ECC_L,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'imageBase64' => false,
            ]);
        
            // Generarea codului QR
            $qrCode = new QRCode($options);
            $qrImage = $qrCode->render($qrData);
        
            // Salvam codul QR intr-un fisier temporar
            $qrFilePath = __DIR__ . '/../temp_qr_code.png';
            file_put_contents($qrFilePath, $qrImage);
        
            // Cream PDF-ul
            $pdf = new FPDF('L', 'mm', [100, 50]); // biletul e format landscape
            $pdf->AddPage();
        
            // adaugam codul QR si detaliile biletului
            $pdf->Image($qrFilePath, 5, 5, 40, 40);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(50, 10);
            $pdf->Cell(0, 5, 'Sala Regala de Muzica', 0, 1, 'L');
            $pdf->SetFont('Helvetica', '', 8);
            $pdf->SetXY(50, 17);
            $pdf->MultiCell(0, 4, "$showName\n$showDate\nLoc: $seatNumber", 0, 'L');
        
            // Salvam fisierul PDF intr-o variabila string
            $pdfString = $pdf->Output('', 'S');
        
            // Stergem fisierul QR temporar
            unlink($qrFilePath);
        
            return $pdfString;
        }        
    }
?>