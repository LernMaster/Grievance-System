<?php
header("Access-Control-Allow-Origin: http://192.168.77.250:3000"); // Replace with your React app's URL
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Replace these credentials with your actual database credentials
$host = '192.168.77.250';
$user = 'root';
$password = '';
$database = 'sgp';

// Connect to the database
$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Endpoint to retrieve all columns from admin_info based on email
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    if ($email === '') {
        echo json_encode(['error' => 'Email not provided']);
        exit;
    }

    // Query to retrieve all columns from admin_info based on email
    $query = "SELECT * FROM admin_info WHERE Email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $roll = $row['Roll'];
        
        // Check if Roll is empty
        $isEmptyRoll = empty($roll);
        
        echo json_encode(['data' => $row, 'success' => !$isEmptyRoll]);
    } else {
        echo json_encode(['error' => 'Error querying database']);
    }
}

mysqli_close($conn);
?>
