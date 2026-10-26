<?php

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "Invalid request";
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name == "" || $email == "" || $subject == "" || $message == "") {
    echo "All fields are required.";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Please enter a valid email.";
    exit;
}

$name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
$email = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
$subject = htmlspecialchars($subject, ENT_QUOTES, "UTF-8");
$message = htmlspecialchars($message, ENT_QUOTES, "UTF-8");

$file = __DIR__ . "/contacts.json";

if (!file_exists($file)) {
    file_put_contents($file, "[]");
}

$data = file_get_contents($file);

if ($data === false || trim($data) == "") {
    $contacts = [];
} else {
    $contacts = json_decode($data, true);

    if (!is_array($contacts)) {
        $contacts = [];
    }
}

$newContact = [
    "name" => $name,
    "email" => $email,
    "subject" => $subject,
    "message" => $message,
    "date" => date("Y-m-d H:i:s")
];

$contacts[] = $newContact;

$json = json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($file, $json, LOCK_EX) === false) {
    echo "Error: Data could not be saved.";
    exit;
}

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>StudentHub</title>";
echo "<link rel='stylesheet' href='contacts.css'>";
echo "</head>";

echo "<body>";

echo "<header>";
echo "<h1>StudentHub</h1>";
echo "</header>";

echo "<main>";
echo "<section class='banner'>";
echo "<h2>Message Sent Successfully!</h2>";
echo "<p>Thank you for contacting StudentHub.</p>";
echo "<p>Your message has been saved successfully.</p>";
echo "<br>";
echo "<a href='contacts.html'>Back to Contact Page</a>";
echo "</section>";
echo "</main>";

echo "</body>";
echo "</html>";

?>

