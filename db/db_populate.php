<?php
return [
    // Create the Users Table
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        rights ENUM('user', 'moderator', 'admin') DEFAULT 'user' NOT NULL,
        tsv_code VARCHAR(10),
        tsv_code_expiration DATETIME
    )",


    // Create the Categories Table
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        image VARCHAR(255) NOT NULL
    )",

    // Create the Events Table
    "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        start_hour TIME NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        category_id INT NOT NULL,
        image VARCHAR(255),
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )",

    // Create the Artists Table
    "CREATE TABLE IF NOT EXISTS artists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL
    )",

    // Create the Event_Artists Table
    "CREATE TABLE IF NOT EXISTS event_artists (
        event_id INT NOT NULL,
        artist_id INT NOT NULL,
        PRIMARY KEY (event_id, artist_id),
        FOREIGN KEY (event_id) REFERENCES events(id),
        FOREIGN KEY (artist_id) REFERENCES artists(id)
    )",

    // Create the Tickets Table
    "CREATE TABLE IF NOT EXISTS tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        event_id INT NOT NULL,
        seat_number INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (event_id) REFERENCES events(id)
    )",

    // Create the Sessions Table
    "CREATE TABLE IF NOT EXISTS sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        ip_address VARCHAR(45) NOT NULL,
        token VARCHAR(255) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        geolocation VARCHAR(255),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",

    // Create the Analytics Table
    "CREATE TABLE IF NOT EXISTS analytics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT NOT NULL,
        page VARCHAR(255) NOT NULL,
        page_load_count INT DEFAULT 0,
        browser VARCHAR(100) NOT NULL,
        operating_system VARCHAR(100) NOT NULL,
        device_type ENUM('desktop', 'mobile') NOT NULL,
        date DATE NOT NULL,
        FOREIGN KEY (session_id) REFERENCES sessions(id)
    )",

    // Create the Hall Table
    "CREATE TABLE IF NOT EXISTS hall (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        capacity INT DEFAULT 300
    )",

    // Insert Categories with Images
    "INSERT INTO categories (name, image) VALUES
        ('Walz', 'vals.webp'),
        ('Band Concert', 'formatie.webp'),
        ('Singer Concert', 'cantaret.webp'),
        ('Classical Music Concert', 'concert_clasic.webp'),
        ('Standup Comedy', 'standup.webp'),
        ('Conference', 'conferinta.webp'),
        ('Presentations', 'prezentari.webp')",

    // Insert Admin User
    "INSERT INTO users (name, email, password, rights) VALUES
        ('admin', 'filip-student@yahoo.com', '" . password_hash('dbpass', PASSWORD_DEFAULT) . "', 'admin')",

    // Insert Events with Default Category Images
    "INSERT INTO events (name, date, start_hour, price, category_id, image) VALUES
        ('Walz Evening', '2024-01-10', '18:00:00', 50.00, 1, 'vals.webp'),
        ('Rock Band Live', '2024-01-12', '20:00:00', 70.00, 2, 'formatie.webp'),
        ('Pop Singer Night', '2024-01-15', '19:00:00', 80.00, 3, 'cantaret.webp'),
        ('Beethoven Symphony', '2024-01-20', '17:00:00', 100.00, 4, 'concert_clasic.webp'),
        ('Comedy Gala', '2024-01-25', '21:00:00', 60.00, 5, 'standup.webp'),
        ('Tech Conference', '2024-01-30', '10:00:00', 40.00, 6, 'conferinta.webp'),
        ('Product Presentations', '2024-02-05', '14:00:00', 30.00, 7, 'prezentari.webp')",

    // Insert Artists
    "INSERT INTO artists (name) VALUES
        ('Johann Strauss II'),
        ('The Rockers'),
        ('Maria Popstar'),
        ('Vienna Philharmonic'),
        ('John Comedian'),
        ('Tech Guru'),
        ('Marketing Specialist')",

    // Link Events to Artists
    "INSERT INTO event_artists (event_id, artist_id) VALUES
        (1, 1), -- Walz Evening -> Johann Strauss II
        (2, 2), -- Rock Band Live -> The Rockers
        (3, 3), -- Pop Singer Night -> Maria Popstar
        (4, 4), -- Beethoven Symphony -> Vienna Philharmonic
        (5, 5), -- Comedy Gala -> John Comedian
        (6, 6), -- Tech Conference -> Tech Guru
        (7, 7)  -- Product Presentations -> Marketing Specialist"
];