<?php

require_once 'helper_discord.php';

header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function safe_redirect($url) {
    if (!headers_sent()) {
        header("Location: https://janicez635.sg-host.com/" . $url);
        exit;
    } else {
        die("Cannot redirect, headers already sent.");
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    safe_redirect("registration/");
    exit;
}

// Database config
$dbConfig = [
    'host' => 'localhost',
    'user' => 'u4sdrnhrckqnh',
    'pass' => 'pykfdufecu4b',
    'name' => 'dbnua7p3kox1va'
];

// MySQL connection
$conn = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbConfig['name']);

// Connection check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$household = htmlspecialchars($_POST['option']) ?? null;
$citizenship = htmlspecialchars($_POST['citizenship']) ?? null;
$requirement = htmlspecialchars($_POST['age']) ?? null;
$household_income = htmlspecialchars($_POST['income']) ?? null;
$ownership_status = htmlspecialchars($_POST['hdb']) ?? null;
$private_property_ownership = htmlspecialchars($_POST['private_property']) ?? null;
$first_time_applicant = htmlspecialchars($_POST['first_time']) ?? null;
$name = htmlspecialchars($_POST['name']) ?? null;
$phone = htmlspecialchars($_POST['phone']) ?? null;
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ?? null;

// SQL Query
$sql = "INSERT INTO users (household, citizenship, requirement, household_income, ownership_status, private_property_ownership, first_time_applicant, name, email, phone) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssss", $household, $citizenship, $requirement, $household_income, $ownership_status, $private_property_ownership, $first_time_applicant, $name, $email, $phone);

if ($stmt->execute()) {
    $inserted_id = $stmt->insert_id;

    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inserted_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        sendLeadToDiscord($user);
    } else {
        die("Error: User not found after insert");
    }
} else {
    die("Error: " . $stmt->error);
}

// Close connection
$stmt->close();
$conn->close();

$msg = '';

switch (true) {
    case $citizenship === 'No, not Singapore Citizens or Permanent Residents' || 
         $requirement === 'No' || 
         $household_income === 'No' || 
         $private_property_ownership === 'Yes':
        safe_redirect("disqualification/");
        exit;
    case $ownership_status === 'Yes, MOP completed':
        safe_redirect("congratulation/");
        exit;
    case $ownership_status === 'Yes, still within MOP':
        safe_redirect("mop/");
        exit;
    case $ownership_status === 'No, do not own any HDB':
        safe_redirect("appeal/");
        exit;
    default:
        safe_redirect("/");
        exit;
}

?>