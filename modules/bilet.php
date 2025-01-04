<?php
    require_once __DIR__ . '/../fpdf/fpdf.php';
    require_once __DIR__ . '/../vendor/autoload.php';

    use chillerlan\QRCode\QRCode;
    use chillerlan\QRCode\QROptions;

    class Bilet {

        public function genereazaBilet($showName, $showDate, $seatNumber) {
            // Prepare the QR code data
            $qrData = "Show Name: $showName\nDate: $showDate\nSeat: $seatNumber";
        
            // Configure QR code options
            $options = new QROptions([
                'eccLevel' => QRCode::ECC_L,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'imageBase64' => false,
            ]);
        
            // Generate QR code
            $qrCode = new QRCode($options);
            $qrImage = $qrCode->render($qrData);
        
            // Save QR code to a temporary file
            $qrFilePath = __DIR__ . '/../temp_qr_code.png';
            file_put_contents($qrFilePath, $qrImage);
        
            // Create the PDF
            $pdf = new FPDF('L', 'mm', [100, 50]); // Smaller ticket size in landscape
            $pdf->AddPage();
        
            // Add QR code and ticket details
            $pdf->Image($qrFilePath, 5, 5, 40, 40);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(50, 10);
            $pdf->Cell(0, 5, 'Sala Regala de Muzica', 0, 1, 'L');
            $pdf->SetFont('Helvetica', '', 8);
            $pdf->SetXY(50, 17);
            $pdf->MultiCell(0, 4, "$showName\n$showDate\nLoc: $seatNumber", 0, 'L');
        
            // Output the PDF as a string
            $pdfString = $pdf->Output('', 'S');
        
            // Clean up temporary QR code file
            unlink($qrFilePath);
        
            return $pdfString;
        }        
    }
?>