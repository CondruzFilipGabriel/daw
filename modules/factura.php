<?php
    // Facturile vor fi salvate ca: users/user_id/factura.data_si_ora.pdf
    // Acesta e trimis pe email doar cand se face rezervarea si nu mai poate fi descarcat ulterior

    require_once __DIR__ . '/../fpdf/fpdf.php';

    class Factura {
        private $pdf;

        public function __construct() {
            $this->pdf = new FPDF();
            $this->pdf->AliasNbPages();
        }

        public function creazaFactura($jsonData, $userId, $beneficiar) {
            // Decode JSON data
            $data = json_decode($jsonData, true);

            // Verificam existenta directorului (sau il cream daca nu exista)
            $userDir = "users/" . $userId;
            if (!file_exists($userDir)) {
                mkdir($userDir, 0777, true);
            }

            // Generam fisierul PDF
            $fileName = $userDir . "/" . "factura.". date('Ymd_His') . ".pdf";

            // Adaugam o pagina si generam continutul
            $this->pdf->AddPage();

            // Detalii emitent
            $this->pdf->SetFont('Helvetica', '', 10);
            $this->pdf->Cell(100, 6, 'Sala Regala de Muzica', 0, 0);
            $this->pdf->Cell(0, 6, 'Cumparator: ' . $beneficiar, 0, 1, 'R');
            $this->pdf->Cell(100, 6, 'Nr. reg. com.: J40/123/2023', 0, 0);
            $this->pdf->Cell(0, 6, '', 0, 1, 'R');
            $this->pdf->Cell(100, 6, 'C.I.F.: RO12345678', 0, 0);
            $this->pdf->Cell(0, 6, '', 0, 1, 'R');
            $this->pdf->Cell(100, 6, 'Sediul: Str. Muzicii nr. 10, Sector 1, Bucuresti', 0, 0);
            $this->pdf->Cell(0, 6, '', 0, 1, 'R');
            $this->pdf->Ln(10);

            // Titlu si data curenta
            $this->pdf->SetFont('Helvetica', 'B', 14);
            $this->pdf->Cell(0, 10, 'FACTURA', 0, 1, 'C');
            $this->pdf->SetFont('Helvetica', '', 12);
            $this->pdf->Cell(0, 10, 'Data: ' . date('d-m-Y'), 0, 1, 'C');
            $this->pdf->Ln(5);

            // Table Header
            $this->pdf->SetFont('Helvetica', 'B', 10);
            $this->pdf->Cell(10, 10, 'Nr.', 1);
            $this->pdf->Cell(70, 10, 'Denumirea produselor sau a serviciilor', 1);
            $this->pdf->Cell(20, 10, 'U.M.', 1);
            $this->pdf->Cell(20, 10, 'Cantitatea', 1);
            $this->pdf->Cell(30, 10, 'Pret unitar', 1);
            $this->pdf->Cell(30, 10, 'Valoarea', 1, 1);

            // Date despre blete
            $this->pdf->SetFont('Helvetica', '', 10);
            $counter = 1;
            foreach ($data['tickets'] as $ticket) {
                $unitPrice = $ticket['price'];
                $quantity = isset($ticket['quantity']) ? $ticket['quantity'] : 1;
                $totalPrice = $unitPrice * $quantity;

                $this->pdf->Cell(10, 10, $counter, 1);
                $this->pdf->Cell(70, 10, $ticket['name'], 1);
                $this->pdf->Cell(20, 10, 'Buc.', 1);
                $this->pdf->Cell(20, 10, $quantity, 1);
                $this->pdf->Cell(30, 10, number_format($unitPrice, 2) . ' ' . $ticket['currency'], 1);
                $this->pdf->Cell(30, 10, number_format($totalPrice, 2) . ' ' . $ticket['currency'], 1, 1);

                $counter++;
            }

            // Pret total
            $this->pdf->SetFont('Helvetica', 'B', 10);
            $this->pdf->Cell(150, 10, 'Total', 1);
            $this->pdf->Cell(30, 10, number_format($data['total'], 2) . ' RON', 1, 1);

            // Salvam fisierul
            $this->pdf->Output('F', $fileName);

            return $fileName;
        }
    }
?>