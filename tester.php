<?php 
    require_once 'modules/header.php';
    require_once 'modules/pieChart.php';

    $data = [
        ['value' => 40, 'name' => 'Google Chrome'],
        ['value' => 30, 'name' => 'Mozilla Firefox'],
        ['value' => 20, 'name' => 'Safari'],
        ['value' => 10, 'name' => 'Others'],
    ];

    $chartData = PieChart::render("Distributia utilizatorilor in functie de tipul browserelor utilizate", $data);


    require_once 'modules/geolocation.php';

    // $ip = '82.78.48.73'; // Example IP
    // $location = Geolocation::getLocation($ip);

    // echo 'Country: ' . $location['country'] . PHP_EOL;
    // echo 'City: ' . $location['city'] . PHP_EOL;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pie Chart</title>
</head>
<body>
    <img src="<?= $chartData ?>" alt="Pie Chart">

    <!-- <h3>Ip-ul tau este din tara: <?= $location['country'] ?></h3>
    <h3>Si din orasul: <?= $location['city'] ?></h3> -->
</body>
</html>
