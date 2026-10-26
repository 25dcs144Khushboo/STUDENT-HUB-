
<?php
session_start();

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

function respond($success, $message, $data = null, $status = 200) {
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
        respond(false, "Please log in first.", null, 401);
    }

    if (($_SESSION["role"] ?? "") !== "admin") {
        respond(false, "Admin access required.", null, 403);
    }

    $method = $_SERVER["REQUEST_METHOD"];

    if ($method === "GET") {
        $stmt = $pdo->query(
            "SELECT event_id, title, event_date, event_time,
                    venue, category, description
             FROM events
             ORDER BY event_id DESC"
        );

        respond(true, "Events loaded.", $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($method !== "POST") {
        header("Allow: GET, POST");
        respond(false, "Method not allowed.", null, 405);
    }

    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        respond(false, "Invalid JSON request.", null, 400);
    }

    $action = $input["action"] ?? "";

    if ($action === "add" || $action === "update") {
        $title = trim($input["title"] ?? "");
        $date = trim($input["event_date"] ?? "");
        $time = trim($input["event_time"] ?? "");
        $venue = trim($input["venue"] ?? "");
        $category = trim($input["category"] ?? "");
        $description = trim($input["description"] ?? "");

        if ($title === "" || $date === "" || $time === "" ||
            $venue === "" || $category === "") {
            respond(false, "Please fill in all required fields.", null, 400);
        }

        $validDate = DateTime::createFromFormat("!Y-m-d", $date);
        $validTime = DateTime::createFromFormat("!H:i", $time);

        if (!$validDate || $validDate->format("Y-m-d") !== $date) {
            respond(false, "Invalid event date.", null, 400);
        }

        if (!$validTime || $validTime->format("H:i") !== $time) {
            respond(false, "Invalid event time.", null, 400);
        }

        if ($action === "add") {
            $stmt = $pdo->prepare(
                "INSERT INTO events
                 (title, event_date, event_time, venue, category, description)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $title, $date, $time, $venue, $category, $description
            ]);

            respond(true, "Event added successfully.", [
                "event_id" => $pdo->lastInsertId()
            ]);
        }

        $id = filter_var($input["event_id"] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            respond(false, "A valid event ID is required.", null, 400);
        }

        $stmt = $pdo->prepare(
            "UPDATE events
             SET title = ?, event_date = ?, event_time = ?,
                 venue = ?, category = ?, description = ?
             WHERE event_id = ?"
        );

        $stmt->execute([
            $title, $date, $time, $venue, $category, $description, $id
        ]);

        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare("SELECT event_id FROM events WHERE event_id = ?");
            $check->execute([$id]);

            if (!$check->fetch()) {
                respond(false, "Event not found.", null, 404);
            }
        }

        respond(true, "Event updated successfully.");
    }

    if ($action === "delete") {
        $id = filter_var($input["event_id"] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            respond(false, "A valid event ID is required.", null, 400);
        }

        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            respond(false, "Event not found.", null, 404);
        }

        respond(true, "Event deleted successfully.");
    }

    respond(false, "Unknown action.", null, 400);

} catch (Throwable $e) {
    error_log("StudentHub API error: " . $e->getMessage());
    respond(false, "Server or database error. Check the PHP error log.", null, 500);
}
