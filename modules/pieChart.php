<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/header.php';

    use Amenadiel\JpGraph\Graph\PieGraph;
    use Amenadiel\JpGraph\Plot\PiePlot;

    class PieChart
    {
        /**
         * Renders a pie chart and returns it as a base64-encoded string.
         *
         * @param array $data Array of objects: [ { "value": numeric, "name": string } ].
         * @return string Base64-encoded string of the chart image.
         */
        
        public static function render(string $title, array $data): string {
            try {
                // Extract values and names
                $values = array_map(fn($item) => $item['value'], $data);
                $names = array_map(fn($item) => $item['name'], $data);
        
                // Create the PieGraph
                $graph = new PieGraph(600, 400);
                $graph->SetShadow();
                $graph->title->Set($title);
                $piePlot = new PiePlot($values);
                $piePlot->SetLegends($names);
                $graph->Add($piePlot);
        
                // Debug to confirm image generation
                // $outputPath = __DIR__ . '/../img/chart.png';
                $outputPath = realpath(__DIR__ . '/../img') . DIRECTORY_SEPARATOR . 'chart_' . uniqid() . '.png';
                $graph->Stroke($outputPath);
        
                if (!file_exists($outputPath)) {
                    Debug::log("Failed to generate image file at $outputPath.");
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