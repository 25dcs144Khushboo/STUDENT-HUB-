
<?php
require_once "auth.php";
require_once "db.php";

requireRole("admin");

$uploadDir = __DIR__ . "/uploads/events/";
$uploadUrl = "uploads/events/";
$maxFileSize = 2 * 1024 * 1024;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$error = "";
$editEvent = null;

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function uploadPoster($file, $uploadDir, $maxFileSize)
{
    if (!isset($file) || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Poster upload failed.");
    }

    if ($file["size"] > $maxFileSize) {
        throw new Exception("Poster size must not exceed 2 MB.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowedTypes[$mime])) {
        throw new Exception("Only JPG, PNG, and WEBP posters are allowed.");
    }

    if (getimagesize($file["tmp_name"]) === false) {
        throw new Exception("Please upload a valid image.");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowedTypes[$mime];

    if (!move_uploaded_file($file["tmp_name"], $uploadDir . $filename)) {
        throw new Exception(
            "Could not save poster. Check the uploads/events folder permissions."
        );
    }

    return $filename;
}

function deletePosterFile($filename, $uploadDir)
{
    if (
        !empty($filename) &&
        basename($filename) === $filename &&
        is_file($uploadDir . $filename)
    ) {
        unlink($uploadDir . $filename);
    }
}

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (
            !isset($_POST["csrf_token"]) ||
            !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
        ) {
            throw new Exception("Invalid request. Please refresh the page.");
        }

        $action = $_POST["action"] ?? "";

        if ($action === "save") {
            $eventId = (int)($_POST["event_id"] ?? 0);
            $title = trim($_POST["title"] ?? "");
            $eventDate = trim($_POST["event_date"] ?? "");
            $eventTime = trim($_POST["event_time"] ?? "");
            $venue = trim($_POST["venue"] ?? "");
            $category = trim($_POST["category"] ?? "");
            $description = trim($_POST["description"] ?? "");

            if (
                $title === "" ||
                $eventDate === "" ||
                $eventTime === "" ||
                $venue === "" ||
                $category === ""
            ) {
                throw new Exception(
                    "Please fill in the title, date, time, venue, and category."
                );
            }

            $dateObject = DateTime::createFromFormat("!Y-m-d", $eventDate);
            $timeObject = DateTime::createFromFormat("!H:i", $eventTime);

            if (
                !$dateObject ||
                $dateObject->format("Y-m-d") !== $eventDate
            ) {
                throw new Exception("Please enter a valid event date.");
            }

            if (
                !$timeObject ||
                $timeObject->format("H:i") !== $eventTime
            ) {
                throw new Exception("Please enter a valid event time.");
            }

            $dateValue = $dateObject->format("Y-m-d");
            $timeValue = $timeObject->format("H:i:s");

            $existingPoster = null;

            if ($eventId > 0) {
                $stmt = $pdo->prepare(
                    "SELECT poster FROM events WHERE event_id = ?"
                );
                $stmt->execute([$eventId]);
                $existingEvent = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existingEvent) {
                    throw new Exception("Event not found.");
                }

                $existingPoster = $existingEvent["poster"];
            }

            $newPoster = uploadPoster(
                $_FILES["poster"] ?? null,
                $uploadDir,
                $maxFileSize
            );

            $poster = $newPoster ?? $existingPoster;

            if ($eventId > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE events
                     SET title = ?,
                         event_date = ?,
                         event_time = ?,
                         venue = ?,
                         category = ?,
                         description = ?,
                         poster = ?
                     WHERE event_id = ?"
                );

                $stmt->execute([
                    $title,
                    $dateValue,
                    $timeValue,
                    $venue,
                    $category,
                    $description,
                    $poster,
                    $eventId
                ]);

                if ($newPoster !== null) {
                    deletePosterFile($existingPoster, $uploadDir);
                }

                $message = "Event updated successfully.";
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO events
                     (title, event_date, event_time, venue, category, description, poster)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $title,
                    $dateValue,
                    $timeValue,
                    $venue,
                    $category,
                    $description,
                    $poster
                ]);

                $message = "Event added successfully.";
            }
        } elseif ($action === "delete") {
            $eventId = (int)($_POST["event_id"] ?? 0);

            $stmt = $pdo->prepare(
                "SELECT poster FROM events WHERE event_id = ?"
            );
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                throw new Exception("Event not found.");
            }

            $stmt = $pdo->prepare(
                "DELETE FROM events WHERE event_id = ?"
            );
            $stmt->execute([$eventId]);

            deletePosterFile($event["poster"], $uploadDir);

            $message = "Event deleted successfully.";
        } else {
            throw new Exception("Invalid action.");
        }
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

if (isset($_GET["edit"])) {
    $editId = (int)$_GET["edit"];

    $stmt = $pdo->prepare(
        "SELECT * FROM events WHERE event_id = ?"
    );
    $stmt->execute([$editId]);
    $editEvent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$editEvent) {
        $error = "Event not found.";
    }
}

$stmt = $pdo->query(
    "SELECT * FROM events ORDER BY event_date DESC, event_time DESC"
);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Event Management</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f4f7fc;
            color: #263445;
        }

        .container {
            max-width: 1150px;
            margin: auto;
        }

        h1, h2 {
            color: #24527a;
        }

        .panel {
            background: white;
            padding: 22px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px #00000010;
        }

        label {
            display: block;
            margin-top: 14px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-size: 15px;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button, .button {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background: #24527a;
            color: white;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .delete-button {
            background: #c0392b;
        }

        .cancel-button {
            background: #64748b;
        }

        .message {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            background: #e1f7e8;
            color: #176534;
        }

        .error {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            background: #fde8e8;
            color: #a61b1b;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #e6f0ff;
        }

        .poster {
            width: 100px;
            max-height: 100px;
            object-fit: contain;
        }

        .actions form {
            display: inline-block;
        }

        .actions button {
            margin: 3px 0;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .panel {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h1>StudentHub Event Management</h1>

    <?php if ($message !== ""): ?>
        <div class="message"><?= h($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="error"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="panel">
        <h2><?= $editEvent ? "Update Event" : "Add New Event" ?></h2>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token"
                   value="<?= h($_SESSION["csrf_token"]) ?>">

            <input type="hidden" name="action" value="save">

            <input type="hidden" name="event_id"
                   value="<?= h($editEvent["event_id"] ?? "") ?>">

            <label for="title">Event Title *</label>
            <input type="text" id="title" name="title" required
                   value="<?= h($editEvent["title"] ?? "") ?>">

            <label for="event_date">Event Date *</label>
            <input type="date" id="event_date" name="event_date" required
                   value="<?= h($editEvent["event_date"] ?? "") ?>">

            <label for="event_time">Event Time *</label>
            <input type="time" id="event_time" name="event_time" required
                   value="<?= h(
                       isset($editEvent["event_time"])
                           ? substr($editEvent["event_time"], 0, 5)
                           : ""
                   ) ?>">

            <label for="venue">Venue *</label>
            <input type="text" id="venue" name="venue" required
                   value="<?= h($editEvent["venue"] ?? "") ?>">

            <label for="category">Category *</label>
            <input type="text" id="category" name="category" required
                   value="<?= h($editEvent["category"] ?? "") ?>"
                   placeholder="Technical, Sports, Cultural, etc.">

            <label for="description">Description</label>
            <textarea id="description" name="description"><?= h(
                $editEvent["description"] ?? ""
            ) ?></textarea>

            <label for="poster">Event Poster (JPG, PNG, WEBP; maximum 2 MB)</label>
            <input type="file" id="poster" name="poster"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

            <?php if (!empty($editEvent["poster"])): ?>
                <p>Current poster:</p>
                <img class="poster"
                     src="<?= h($uploadUrl . $editEvent["poster"]) ?>"
                     alt="Current event poster">
                <p>Leave the poster field empty to keep the current poster.</p>
            <?php endif; ?>

            <button type="submit">
                <?= $editEvent ? "Update Event" : "Add Event" ?>
            </button>

            <?php if ($editEvent): ?>
                <a class="button cancel-button"
                   href="event_management.php">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="panel">
        <h2>All Events</h2>

        <?php if (empty($events)): ?>
            <p>No events have been added yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= h($event["event_id"]) ?></td>

                            <td>
                                <?php if (!empty($event["poster"])): ?>
                                    <img class="poster"
                                         src="<?= h($uploadUrl . $event["poster"]) ?>"
                                         alt="Event poster">
                                <?php else: ?>
                                    No poster
                                <?php endif; ?>
                            </td>

                            <td><?= h($event["title"]) ?></td>
                            <td><?= h($event["event_date"]) ?></td>
                            <td><?= h($event["event_time"]) ?></td>
                            <td><?= h($event["venue"]) ?></td>
                            <td><?= h($event["category"]) ?></td>
                            <td><?= nl2br(h($event["description"] ?? "")) ?></td>

                            <td class="actions">
                                <a class="button"
                                   href="event_management.php?edit=<?= (int)$event["event_id"] ?>">
                                    Edit
                                </a>

                                <form method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= h($_SESSION["csrf_token"]) ?>">

                                    <input type="hidden" name="action"
                                           value="delete">

                                    <input type="hidden" name="event_id"
                                           value="<?= (int)$event["event_id"] ?>">

                                    <button type="submit" class="delete-button">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
