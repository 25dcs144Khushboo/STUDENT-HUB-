<?php

header("Content-Type: application/json");

require_once "db.php";

$sql = "SELECT event_id, title, event_date, event_time, venue, category, description
        FROM events
        ORDER BY event_date ASC";

$result = $conn->query($sql);

$events = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $events[] = [
            "id" => (int)$row["event_id"],
            "title" => $row["title"],
            "date" => $row["event_date"],
            "time" => date("h:i A", strtotime($row["event_time"])),
            "venue" => $row["venue"],
            "category" => $row["category"],
            "description" => $row["description"]
        ];

    }

}

echo json_encode($events);

$conn->close();

?>