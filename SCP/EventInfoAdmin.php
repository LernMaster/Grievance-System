<?php
header("Access-Control-Allow-Origin: http://192.168.157.250:3000");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'admin';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $email = isset($_GET['email']) ? $_GET['email'] : '';

    if ($email === '') {
        echo json_encode(['success' => false, 'message' => 'Please provide email']);
        exit;
    }

    $email = mysqli_real_escape_string($conn, $email); // Sanitize input

    $eventQuery = "SELECT Limits,Title,event_id,IntervalTime,Status,Date FROM events WHERE Email = '$email'";
    $eventResult = mysqli_query($conn, $eventQuery);

    if ($eventResult) {
        $eventData = array(); // Initialize an array to store the results

        while ($row = mysqli_fetch_assoc($eventResult)) {
            $eventData[] = $row;
        }

        if (count($eventData) > 0) {
            echo json_encode(['success' => true, 'data' => $eventData]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No data found for the provided email']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . mysqli_error($conn)]);
    }
}

mysqli_close($conn);
?>
