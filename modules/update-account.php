<div class="login-form">    
    <h3><?= $alert ?></h3>
    <h3>Administrare utilizator</h3>
    <form action="verify-update-account.php" method="POST">
        <table>
            <tbody>
                <tr>
                    <td>
                        <label for="name">Nume:</label>
                    </td>
                    <td>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </td>
                </tr>

                <tr>
                    <td>
                        <label for="email">Email:</label>
                    </td>
                    <td>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </td>
                </tr>

                <tr>
                    <td>   
                        <label for="pass1">Schimba parola:</label>                    
                    </td>
                    <td>
                        <input type="password" id="pass1" name="pass1" value="" placeholder="nu se schimba parola">
                    </td>
                </tr>

                <tr>
                    <td>
                        <label for="pass2">Rescrie parola noua:</label>
                    </td>
                    <td>
                        <input type="password" id="pass2" name="pass2" value="" placeholder="nu se schimba parola">
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <button type="submit" class="event-buton-rezervare">Update utilizator</button>
    </form>

    <br>
    <form action="/ProiectDaw/verify-delete-account.php" method="POST">
        <button type="submit" class="event-buton-rezervare red-text">Sterge utilizator</button>
    </form>

    <br>
    <form action="/ProiectDaw/logout.php" method="POST">
        <button type="submit" class="event-buton-rezervare blue-text">Logout</button>
    </form>


    <h3>Bilete Achizitionate</h3>
    <table class="users-management">
        <thead>
            <tr>
                <th>Numele evenimentului</th>
                <th>Data evenimentului</th>
                <th>Pretul unui bilet</th>
                <th>Numar de bilete</th>
                <th>Descarca bilet</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $tickets = $db->getUserTickets($user['user_id']);
            $groupedTickets = [];

            // Group tickets by event
            foreach ($tickets as $ticket) {
                $eventKey = $ticket->showName . $ticket->showDate;
                if (!isset($groupedTickets[$eventKey])) {
                    $groupedTickets[$eventKey] = [
                        'name' => $ticket->showName,
                        'date' => $ticket->showDate,
                        'price' => $ticket->ticketPrice,
                        'seats' => [],
                    ];
                }
                $groupedTickets[$eventKey]['seats'][] = $ticket->seatNumber;
            }

            // Render grouped tickets
            foreach ($groupedTickets as $event) {
                $seatNumbers = $event['seats'];
                $numTickets = count($seatNumbers);
                ?>
                <tr>
                    <td><?= htmlspecialchars($event['name']) ?></td>
                    <td><?= htmlspecialchars($event['date']) ?></td>
                    <td><?= htmlspecialchars($event['price']) ?> RON</td>
                    <td><?= $numTickets ?></td>
                    <td>
                        <form action="/ProiectDaw/modules/trimite-bilete.php" method="POST">
                            <input type="hidden" name="event_name" value="<?= htmlspecialchars($event['name']) ?>">
                            <input type="hidden" name="event_date" value="<?= htmlspecialchars($event['date']) ?>">
                            <input type="hidden" name="reserved_seats" value="<?= implode(',', $seatNumbers) ?>">
                            <button type="submit">Trimite bilete</button>
                        </form>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>


    <br>
    <?php 
        if($user['rights'] === 'admin') {
            include_once 'modules/admin.php';
        }
    ?>
</div>