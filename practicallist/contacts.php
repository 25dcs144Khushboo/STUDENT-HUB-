<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Please submit the form first.");
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name == "" || $email == "" || $subject == "" || $message == "") {
    die("All fields are required.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address.");
}

$file = __DIR__ . "/contacts.json";

if (!file_exists($file)) {

    $result = file_put_contents($file, "[]");

    if ($result === false) {
        die(
            "ERROR: PHP cannot create contacts.json.<br><br>" .
            "PHP is looking in:<br>" .
            __DIR__
        );
    }
}

$data = file_get_contents($file);

if ($data === false) {
    die("ERROR: PHP cannot read contacts.json.");
}

$data = trim($data);

if ($data == "") {
    $contacts = [];
} else {

    $contacts = json_decode($data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {

        die(
            "ERROR: contacts.json has invalid JSON.<br>" .
            json_last_error_msg()
        );
    }

    if (!is_array($contacts)) {
        $contacts = [];
    }
}

$newContact = [
    "name" => $name,
    "email" => $email,
    "subject" => $subject,
    "message" => $message
];

$contacts[] = $newContact;

$json = json_encode(
    $contacts,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);

if ($json === false) {
    die("ERROR: JSON conversion failed.");
}

$result = file_put_contents(
    $file,
    $json,
    LOCK_EX
);

if ($result === false) {

    die(
        "ERROR: PHP cannot write to contacts.json.<br><br>" .
        "File location:<br>" .
        $file . "<br><br>" .
        "Check the file/folder permissions."
    );
}

echo "<h1>Message Saved Successfully!</h1>";

echo "<p>Your data has been stored in contacts.json.</p>";

echo "<p><strong>File location:</strong><br>";
echo htmlspecialchars($file);
echo "</p>";

echo "<p><a href='contacts.html'>Go Back</a></p>";

?>

