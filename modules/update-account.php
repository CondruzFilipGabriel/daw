<div class="login-form">
<!-- Add fields to update user account information -->
    <form action="/ProiectDaw/modules/process-update-account.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br><br>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>
        
        <!-- Add other account update fields if necessary -->

        <button type="submit" class="event-buton-rezervare">Update Account</button>
    </form>

    <br>
    <!-- Add logout button -->
    <form action="/ProiectDaw/logout.php" method="POST">
        <button type="submit" class="event-buton-rezervare">Logout</button>
    </form>
</div>