<?php
    $url = "https://ro.wikipedia.org/wiki/Concertul_de_Anul_Nou_de_la_Viena";

    try {
        $htmlContent = file_get_contents($url);

        if ($htmlContent === false) {
            throw new Exception("Could not fetch the webpage content.");
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($htmlContent);

        $xpath = new DOMXPath($dom);

        $div = $xpath->query('//div[contains(@class, "mw-content-ltr mw-parser-output")]');

        if ($div->length > 0) {
            $paragraph = $div[0]->getElementsByTagName('p')->item(0);

            if ($paragraph) {
                echo "<div>{$paragraph->nodeValue}</div>";
            } else {
                echo "Paragraph not found.";
            }
        } else {
            echo "Div not found.";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
?>
