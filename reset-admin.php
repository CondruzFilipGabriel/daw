<?php
    require_once __DIR__ . '/modules/header.php';

    // Define admin credentials (update these values as needed)
    $adminEmail = "filip_student@yahoo.com";
    $adminPassword = "pass";

    // Hash the password
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

    try {
        // Delete the existing admin user
        $queryDelete = "DELETE FROM users WHERE name = 'admin'";
        $resultDelete = $db->getConnection()->query($queryDelete);

        if ($resultDelete) {
            Debug::log("Deleted existing admin user.");
        } else {
            Debug::log("Failed to delete existing admin user: " . $db->getConnection()->error);
        }

        // Recreate the admin user
        $queryInsert = "INSERT INTO users (name, email, password, rights) VALUES ('admin', ?, ?, 'admin')";
        $stmt = $db->getConnection()->prepare($queryInsert);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $db->getConnection()->error);
        }

        $stmt->bind_param('ss', $adminEmail, $hashedPassword);
        $success = $stmt->execute();

        if ($success) {
            Debug::log("Admin user recreated successfully.");
            echo "Admin user has been reset successfully.";
        } else {
            throw new Exception("Failed to recreate admin user: " . $stmt->error);
        }
    } catch (Exception $e) {
        Debug::log($e->getMessage());
        echo "An error occurred: " . htmlspecialchars($e->getMessage());
    }
?>
