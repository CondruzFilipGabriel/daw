<?php
    include_once 'db/db.php';
    $db = DB::getInstance();
    $events = $db->getAllEvents();

    include 'modules/header.php';
?>

    <div class="event-container">

    <?php foreach ($events as $event) { ?>

        <article class="event-article">
            <img class="event-image" src="img/events/generic/<?= $event['image'] ?>" alt="Vals">
            <div class="event-article-content">
                <h4><?= $event['title'] ?></h4>
                <h5 class="event-date"><?= $event['date_time'] ?></h5>
                <h5 class="event-price">Pret: <?= $event['price'] ?> RON / loc.</h5>
                <a href="#book-now" class="event-buton-rezervare">Rezervă locuri</a>
            </div>
        </article>

    <?php } ?>

    </div>

    <div class="anunt">
        <b>
            Anul acesta, Sala Regala de Muzica va gazdui 
            <a href="https://ro.wikipedia.org/wiki/Concertul_de_Anul_Nou_de_la_Viena">
                Concertul de Anul Nou de la Viena!
            </a>
        </b>
        <?php include "modules/external.php" ?>
    </div>

    <h5 id="disclaimer">
        <b>Acest site este un proiect școlar și nu reprezintă o entitate reală</b>
    </h5>
<?php include 'modules/footer.php'; ?>