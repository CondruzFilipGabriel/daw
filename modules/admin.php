<?php
    include_once 'modules/header.php';

    // Restrict access to admins only
    if (!$user || $user['rights'] !== 'admin') {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    $allUsers = $db->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>
<body>
    <h3>Administrare utilizatori</h3>
    <table class="users-management">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nume</th>
                <th>Email</th>
                <th>Parola</th>
                <th>Drepturi</th>
                <th>Actiuni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allUsers as $userData): ?>
                <tr>
                    <form action="admin-update-account.php" method="POST">
                        <td><?= htmlspecialchars($userData['id']) ?></td>
                        <td>
                            <input type="text" name="name" value="<?= htmlspecialchars($userData['name']) ?>" required>
                        </td>
                        <td>
                            <input type="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
                        </td>
                        <td>
                            <input type="password" name="password" placeholder="nu se schimba parola">
                        </td>
                        <td>
                            <select name="rights" required>
                                <option value="user" <?= $userData['rights'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $userData['rights'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="moderator" <?= $userData['rights'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                            </select>
                        </td>
                        <td>
                            <button type="submit">Update</button>
                            <input type="hidden" name="id" value="<?= $userData['id'] ?>">
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

<?php
    include_once 'modules/footer.php';
?>
