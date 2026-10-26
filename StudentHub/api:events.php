<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

function sendJSON($success, $message, $data = null, $status = 200)
{
    http_response_code($status);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

try {
    require_once __DIR__ . "/../db.php";

    if (!isset($_SESSION["user_id"])) {
        sendJSON(false, "Please log in first.", null, 401);
    }

    if (($_SESSION["role"] ?? "") !== "admin") {
        sendJSON(false, "Admin access required.", null, 403);
    }

    $method = $_SERVER["REQUEST_METHOD"];

    if ($method === "GET") {
        $stmt = $pdo->query(
            "SELECT event_id, title, event_date, event_time,
                    venue, category, description
             FROM events
             ORDER BY event_id DESC"
        );

        sendJSON(true, "Events loaded successfully.",
            $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($method !== "POST") {
        header("Allow: GET, POST");
        sendJSON(false, "Unsupported request method.", null, 405);
    }

    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        sendJSON(false, "Invalid JSON request.", null, 400);
    }

    $action = $input["action"] ?? "";

    if ($action === "add" || $action === "update") {
        $id = filter_var(
            $input["event_id"] ?? null,
            FILTER_VALIDATE_INT
        );

        $title = trim($input["title"] ?? "");
        $date = trim($input["event_date"] ?? "");
        $time = trim($input["event_time"] ?? "");
        $venue = trim($input["venue"] ?? "");
        $category = trim($input["category"] ?? "");
        $description = trim($input["description"] ?? "");

        if (
            $title === "" || $date === "" || $time === "" ||
            $venue === "" || $category === ""
        ) {
            sendJSON(false, "Fill in all required fields.", null, 422);
        }

        $dateObject = DateTime::createFromFormat("!Y-m-d", $date);
        $timeObject = DateTime::createFromFormat("!H:i", $time);

        if (!$dateObject || $dateObject->format("Y-m-d") !== $date) {
            sendJSON(false, "Invalid event date.", null, 422);
        }

        if (!$timeObject || $timeObject->format("H:i") !== $time) {
            sendJSON(false, "Invalid event time.", null, 422);
        }

        if ($action === "add") {
            $stmt = $pdo->prepare(
                "INSERT INTO events
                (title, event_date, event_time, venue, category, description)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $title,
                $date,
                $timeObject->format("H:i:s"),
                $venue,
                $category,
                $description
            ]);

            sendJSON(true, "Event added successfully.", [
                "event_id" => $pdo->lastInsertId()
            ], 201);
        }

        if (!$id || $id < 1) {
            sendJSON(false, "Valid event ID required.", null, 422);
        }

        $stmt = $pdo->prepare(
            "UPDATE events
             SET title = ?, event_date = ?, event_time = ?,
                 venue = ?, category = ?, description = ?
             WHERE event_id = ?"
        );

        $stmt->execute([
            $title,
            $date,
            $timeObject->format("H:i:s"),
            $venue,
            $category,
            $description,
            $id
        ]);

        $check = $pdo->prepare(
            "SELECT event_id FROM events WHERE event_id = ?"
        );
        $check->execute([$id]);

        if (!$check->fetch()) {
            sendJSON(false, "Event not found.", null, 404);
        }

        sendJSON(true, "Event updated successfully.");
    }

    if ($action === "delete") {
        $id = filter_var(
            $input["event_id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {
            sendJSON(false, "Valid event ID required.", null, 422);
        }

        $stmt = $pdo->prepare(
            "DELETE FROM events WHERE event_id = ?"
        );
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            sendJSON(false, "Event not found.", null, 404);
        }

        sendJSON(true, "Event deleted successfully.");
    }

    sendJSON(false, "Unknown action.", null, 400);

} catch (Throwable $e) {
    error_log("StudentHub Events API: " . $e->getMessage());
    sendJSON(false, "Server/database error: " . $e->getMessage(), null, 500);
}
