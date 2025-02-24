<?php

// Database config
$dbConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'root',
    'name' => 'jj_janices633'
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

if (!$email) {
    die("Invalid email format.");
}

// SQL Query
$sql = "INSERT INTO users (household, citizenship, requirement, household_income, ownership_status, private_property_ownership, first_time_applicant, name, email, phone) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssss", $household, $citizenship, $requirement, $household_income, $ownership_status, $private_property_ownership, $first_time_applicant, $name, $email, $phone);
$stmt->execute();

// Close connection
$stmt->close();
$conn->close();

$msg = '';
switch (true) {
    case $ownership_status === 'Yes, MOP completed':
        header("Location: congratulation/");
        exit;
    case $ownership_status === 'Yes, still within MOP':
        header("Location: mop/");
        exit;
    case $citizenship === 'No, no Singapore Citizens or Permanent Residents' || 
         $requirement === 'No' || 
         $household_income === 'No' || 
         $private_property_ownership === 'Yes':
        header("Location: appeal/");
        exit;
    case $ownership_status === 'No, do not own any HDB':
        header("Location: appeal/");
        exit;
}

?>