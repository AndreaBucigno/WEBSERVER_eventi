<?php
// api.php - Web Service per l'Utente Finale
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

include_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    // READ: L'utente vede gli eventi
    case 'get_events':
        $stmt = $conn->prepare("SELECT * FROM events WHERE available_seats > 0 ORDER BY event_date ASC");
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $events]);
        break;

    // BUSINESS LOGIC: L'utente prenota un posto
    case 'book':
        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->event_id) && !empty($data->user_email)) {
            $check = $conn->prepare("SELECT available_seats FROM events WHERE id = ?");
            $check->execute([$data->event_id]);
            $event = $check->fetch(PDO::FETCH_ASSOC);

            if($event && $event['available_seats'] > 0) {
                $conn->beginTransaction();
                try {
                    $book = $conn->prepare("INSERT INTO bookings (event_id, user_name, user_email) VALUES (?, ?, ?)");
                    $book->execute([$data->event_id, $data->user_name, $data->user_email]);

                    $update = $conn->prepare("UPDATE events SET available_seats = available_seats - 1 WHERE id = ?");
                    $update->execute([$data->event_id]);

                    $conn->commit();
                    echo json_encode(["status" => "success", "message" => "Prenotazione confermata."]);
                } catch(Exception $e) {
                    $conn->rollBack();
                    http_response_code(500);
                    echo json_encode(["status" => "error", "message" => "Errore durante la prenotazione."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Posti esauriti per questo evento."]);
            }
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Servizio non disponibile per gli utenti."]);
        break;
}
?>