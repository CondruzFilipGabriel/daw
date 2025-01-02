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

    <br>
    <?php 
        if($user['rights'] === 'admin') {
            include_once 'modules/admin.php';
        }
    ?>
</div>