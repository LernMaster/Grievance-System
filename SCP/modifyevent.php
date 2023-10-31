<?php
header("Access-Control-Allow-Origin: http://192.168.157.250:3000"); // Replace with your React app's URL
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Disable caching for the login response
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");

// Replace these credentials with your actual database credentials
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'admin';

// Connect to the database
$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Endpoint to handle updating events
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $eventId = isset($data['eventId']) ? $data['eventId'] : '';
    $email = isset($data['email']) ? $data['email'] : '';
    $lastDate = isset($data['lastDate']) ? $data['lastDate'] : '';
    $limit = isset($data['limit']) ? $data['limit'] : '';
    $status = isset($data['status']) ? $data['status'] : '';

    if ($eventId === '' || $email === '' || $lastDate === '' || $limit === '' || $status === '') {
        echo json_encode(['success' => false, 'message' => 'Please provide eventId, email, lastDate, limit, and status']);
        exit;
    }

    // Check if the lastDate is greater than or equal to today's date
    if ($lastDate >= date('Y-m-d')) {
        // Update status, limit, and intervalTime in the events table
        $updateEventQuery = "UPDATE events SET IntervalTime = '$lastDate', Status = '$status', limits = '$limit' WHERE event_id = '$eventId' AND email = '$email'";
        $updateEventResult = mysqli_query($conn, $updateEventQuery);

        if ($updateEventResult) {
            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        } else {
            die('Query failed: ' . mysqli_error($conn));
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'lastDate is not greater than or equal to today']);
    }
}

mysqli_close($conn);
?>
