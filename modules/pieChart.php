<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/header.php';

    use Amenadiel\JpGraph\Graph\PieGraph;
    use Amenadiel\JpGraph\Plot\PiePlot;

    class PieChart
    {
        /**
         * Creaza un pie-chart si returneaza un string base64-encoded
         * 
         * Primeste ca parametri un array de forma:  [ { "value": numeric, "name": string } ]
         */
        
         public static function render(string $title, array $data): string {
            try {
                // Extragem valorile si numele
                $values = array_map(fn($item) => $item['value'], $data);
                $names = array_map(fn($item) => $item['name'], $data);
        
                // Create the PieGraph
                $graph = new PieGraph(600, 400);
                $graph->SetShadow();
                $graph->title->Set($title);
                $piePlot = new PiePlot($values);
                $piePlot->SetLegends($names);
                $graph->Add($piePlot);
        
                // Generate a filename based on the title
                $filename = str_replace(' ', '_', strtolower($title)) . '.png';
                $outputPath = realpath(__DIR__ . '/../img') . DIRECTORY_SEPARATOR . $filename; // pentru Windows
        
                // Stergem fisierele care au acelasi nume (pentru a nu exista probleme la scriere)
                if (file_exists($outputPath)) {
                    unlink($outputPath);
                }
        
                $graph->Stroke($outputPath);
        
                if (!file_exists($outputPath)) {
                    Debug::log("Nu s-au putut genera fisierul $outputPath.");
                }
        
                $imageData = file_get_contents($outputPath);
                return 'data:image/png;base64,' . base64_encode($imageData);
            } catch (Exception $e) {
                Debug::log("JPGraph Error: " . $e->getMessage());
                return False;
            }
        }        
    }
?>