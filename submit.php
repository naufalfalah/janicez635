<?php

require_once 'config.php';
require_once 'database.php';
require_once 'helper_discord.php';

loadEnv(__DIR__ . '/.env');

header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function safe_redirect($url) {
    if (!headers_sent()) {
        header("Location: " . getenv('BASE_URL') . $url);
        exit;
    } else {
        die("Cannot redirect, headers already sent.");
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    safe_redirect("registration/");
    exit;
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
        VALUES (:household, :citizenship, :requirement, :household_income, :ownership_status, :private_property_ownership, :first_time_applicant, :name, :email, :phone)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':household' => $household,
    ':citizenship' => $citizenship,
    ':requirement' => $requirement,
    ':household_income' => $household_income,
    ':ownership_status' => $ownership_status,
    ':private_property_ownership' => $private_property_ownership,
    ':first_time_applicant' => $first_time_applicant,
    ':name' => $name,
    ':email' => $email,
    ':phone' => $phone,
]);

$inserted_id = $pdo->lastInsertId();

$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $inserted_id]);
$user = $stmt->fetch();

if ($user) {
    sendLeadToDiscord($user);
} else {
    die("Error: User not found after insert");
}

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