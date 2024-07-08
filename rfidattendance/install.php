<?php
    //Connect to database
    $servername = "localhost";
    $username = "root";     //put your phpmyadmin username.(default is "root")
    $password = "";         //if your phpmyadmin has a password put it here.(default is "root")
    $dbname = "biometricattendace"; // Database name

    $conn = new mysqli($servername, $username, $password);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully<br>";
    } else {
        echo "Error creating database: " . $conn->error;
    }

    // Connect to the created database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // SQL to create table users
    $sql = "CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `username` varchar(100) NOT NULL,
            `serialnumber` double NOT NULL,
            `gender` varchar(10) NOT NULL,
            `email` varchar(50) NOT NULL,
            `phone` varchar(20) NOT NULL, // New field: Phone number
            `address` varchar(255) NOT NULL, // New field: Address
            `fingerprint_id` int(11) NOT NULL,
            `fingerprint_select` tinyint(1) NOT NULL DEFAULT '0',
            `user_date` date NOT NULL,
            `time_in` time NOT NULL,
            `del_fingerid` tinyint(1) NOT NULL DEFAULT '0',
            `add_fingerid` tinyint(1) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1";

    if ($conn->query($sql) === TRUE) {
        echo "Table users created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    // SQL to create table users_logs
    $sql = "CREATE TABLE IF NOT EXISTS `users_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `username` varchar(100) NOT NULL,
            `serialnumber` double NOT NULL,
            `fingerprint_id` int(5) NOT NULL,
            `checkindate` date NOT NULL,
            `timein` time NOT NULL,
            `timeout` time NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1";

    if ($conn->query($sql) === TRUE) {
        echo "Table users_logs created successfully";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>
