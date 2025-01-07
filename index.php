<?php
    include 'modules/header.php';
    $events = $db->getAllEvents();
?>

<div class="event-container">

<?php foreach ($events as $event) { ?>
    <form class="event-article" action="rezerva.php" method="POST">
        <img class="event-image" src="img/events/generic/<?= $event['image'] ?>" alt="Vals">
        <div class="event-article-content">
            <h4><?= $event['title'] ?></h4>
            <h5 class="event-date"><?= $event['date_time'] ?></h5>
            <h5 class="event-price">Pret: <?= $event['price'] ?> RON / loc.</h5>

            <input type="hidden" name="event_name" value="<?= $event['title'] ?>">
            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
            <input type="hidden" name="event_price" value="<?= $event['price'] ?>">
            <input type="hidden" name="event_date" value="<?= $event['date_time'] ?>">

            <div class="seat-selector">
                <button type="submit" class="event-buton-rezervare">Rezervă locuri:</button>
                <input type="number" name="number_of_seats" class="seat_nb" value="0" min="0" max="300">
            </div>
        </div>
    </form>

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

<script src="js/index.js"></script>

<?php include 'modules/footer.php'; ?>