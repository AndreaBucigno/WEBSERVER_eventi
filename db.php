<?php
// configurazione per la connessione al database su phpmyadmin con localhost
$host = "localhost";
$db_name = "cityevents_db";
$username = "root"; 
$password = "";

try {
    
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);

    $conn->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) 

{
    http_response_code(500);
    echo json_encode(["error" => "Connection error: " . $exception->getMessage()]);
    exit;
}
?>