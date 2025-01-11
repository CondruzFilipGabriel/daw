<?php
    include_once 'modules/header.php';

    // Restrict access to admins only
    if (!$user || $user['rights'] !== 'admin') {
        header("Location: /ProiectDaw/index.php");
        exit();
    }

    $allUsers = $db->getAllUsers();
    $allEvents = $db->getAllEvents();

    $uploadDir = realpath(__DIR__ . '/../img/events') . DIRECTORY_SEPARATOR;

    // Fetch analytics data for pie charts
    $analyticsData = [
        'country' => $db->getAnalyticsGroupBy('country'),
        'browser' => $db->getAnalyticsGroupBy('browser'),
        'os' => $db->getAnalyticsGroupBy('operating_system'),
        'device_type' => $db->getAnalyticsGroupBy('device_type')
    ];
    require_once 'modules/pieChart.php';
?>

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

<h3>Administrare evenimente</h3>
<table class="users-management">
    <thead>
        <tr>
            <th>Nume eveniment</th>
            <th>Data</th>
            <th>Ora de inceput</th>
            <th class="coloana-pret">Pret</th>
            <th>Categorie</th>
            <th>Imagine</th>
            <th>Actiuni</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $categories = $db->getAllCategories();
        foreach ($allEvents as $eventData): ?>
            <tr>
                <form action="modules/updateEvent.php" method="POST" enctype="multipart/form-data">
                    <td>
                        <input type="text" name="name" value="<?= htmlspecialchars($eventData['title']) ?>" required>
                    </td>
                    <td>
                        <input type="date" name="date" value="<?= htmlspecialchars(explode(' ', $eventData['date_time'])[0]) ?>" required>
                    </td>
                    <td>
                        <input type="time" name="start_hour" value="<?= htmlspecialchars(explode(' ', $eventData['date_time'])[1]) ?>" required>
                    </td>
                    <td class="coloana-pret">
                        <input type="number" name="price" value="<?= htmlspecialchars($eventData['price']) ?>" step="0.01" required>
                    </td>
                    <td>
                        <select name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $category['id'] == $eventData['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="left">
                        <?php
                            $files = glob($uploadDir . $eventData['id'] . '.*');
                            $hasImage = ($files && file_exists($files[0]));
                        ?>

                        <?php if (!$hasImage): ?>
                            <!-- Default file input when no image exists -->
                            <input type="file" name="image" accept="image/*">
                        <?php else: ?>

                            <!-- Custom button when an image exists -->
                            <input type="file" name="image" accept="image/*" id="fileInput<?= $eventData['id'] ?>" style="display: none;">

                            <button type="button" style="text-align: left !important;" onclick="document.getElementById('fileInput<?= $eventData['id'] ?>').click();">
                                Replace File
                            </button>

                            <!-- Placeholder for displaying selected filename -->
                            <span id="fileName<?= $eventData['id'] ?>"></span>

                            <script>
                                // Show the first 10 characters of the selected file name
                                document.getElementById('fileInput<?= $eventData['id'] ?>').addEventListener('change', function() {
                                    const fileName = this.files.length > 0 ? this.files[0].name : '';
                                    const shortName = fileName.length > 10 ? fileName.substring(0, 10) + '...' : fileName;
                                    document.getElementById('fileName<?= $eventData['id'] ?>').textContent = shortName;
                                });
                            </script>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="submit">Update</button>
                        <input type="hidden" name="id" value="<?= $eventData['id'] ?>">
                    </td>
                </form>
                <td colspan="7" style="text-align: center;">
                    <form action="modules/deleteEvent.php" method="POST">
                        <input type="hidden" name="id" value="<?= $eventData['id'] ?>">
                        <button type="submit" class="event-button-delete">Sterge</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <form action="modules/createEvent.php" method="POST" enctype="multipart/form-data">
                <td>
                    <input type="text" name="name" placeholder="Nume eveniment" required>
                </td>
                <td>
                    <input type="date" name="date" required>
                </td>
                <td>
                    <input type="time" name="start_hour" required>
                </td>
                <td class="coloana-pret">
                    <input type="number" name="price" step="0.01" required>
                </td>
                <td>
                    <select name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="file" name="image" accept="image/*">
                </td>
                <td colspan="2">
                    <button type="submit" class="event-button-create">Creaza eveniment</button>
                </td>
            </form>
        </tr>
    </tbody>
</table>

<h3>Server Analytics</h3>
<table class="users-management">
    <thead>
        <tr>
            <th>Atribut</th>
            <th>Valoare</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="atribut">Vizitatori unici</td>
            <td><?= $db->getTotalAnalyticsCount() ?></td>
        </tr>
        <tr>
            <td class="atribut">Pagini vizualizate</td>
            <td><?= $db->getTotalPagesViewed() ?></td>
        </tr>
        <tr>
            <td class="atribut">Timpul mediu pe pagini</td>
            <td><?= number_format($db->getAverageTimeSpent(), 2) ?> secunde</td>
        </tr>
        <tr>
            <td class="atribut">Timpul mediu de incărcare a paginilor</td>
            <td><?= number_format($db->getAveragePageLoadTime(), 2) ?> secunde</td>
        </tr>
        <tr>
            <td class="atribut">Ultima pagină vizualizată</td>
            <td><?= htmlspecialchars($db->getLastPageViewed()) ?></td>
        </tr>
    </tbody>
</table>
<br>

<div style="display: flex; flex-wrap: wrap; gap: 20px;">
    <div class="piechart">
        <img src="<?= PieChart::render('Distribuție pe Țări', $analyticsData['country']) ?>" alt="Distribuție pe Țări">
    </div>
    <div class="piechart">
        <img src="<?= PieChart::render('Distribuție Browser', $analyticsData['browser']) ?>" alt="Distribuție Browser">
    </div>
    <div class="piechart">
        <img src="<?= PieChart::render('Distribuție Sisteme de Operare', $analyticsData['os']) ?>" alt="Distribuție Sisteme de Operare">
    </div>
    <div class="piechart">
        <img src="<?= PieChart::render('Distribuție Dispozitive', $analyticsData['device_type']) ?>" alt="Distribuție Dispozitive">
    </div>
</div>

<?php
    include_once 'modules/footer.php';
?>