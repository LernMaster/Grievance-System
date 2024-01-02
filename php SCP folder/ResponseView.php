<?php
header("Access-Control-Allow-Origin: http://192.168.77.250:3000"); // Replace with your React app's URL
header("Access-Control-Allow-Methods: GET"); // Change to GET
header("Access-Control-Allow-Headers: Content-Type");

// Disable caching for the response
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");

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

// Endpoint to handle retrieving events_response
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $eventid = isset($_GET['EventId']) ? $_GET['EventId'] : '';
    $email = isset($_GET['email']) ? $_GET['email'] : '';

    // Create a prepared statement to check the availability of the code
    $selectQuery = "SELECT * FROM events WHERE event_id = ? AND email = ?";
    $stmt = mysqli_prepare($conn, $selectQuery);

    // Bind parameters
    mysqli_stmt_bind_param($stmt, "is", $eventid, $email);

    // Execute the prepared statement
    mysqli_stmt_execute($stmt);

    // Get the result
    $result = mysqli_stmt_get_result($stmt);

    // Check if there is a result
    if ($row = mysqli_fetch_assoc($result)) {
        $responseQuery = "SELECT Response,TimeStop,Email FROM events_response WHERE Event_Id = ?";
        $responseStmt = mysqli_prepare($conn, $responseQuery);
        // Bind the Event_Id parameter
        mysqli_stmt_bind_param($responseStmt, "i", $eventid);

        // Execute the prepared statement
        mysqli_stmt_execute($responseStmt);

        // Get the result
        $responseResult = mysqli_stmt_get_result($responseStmt);

        // Fetch all matching rows
        $responseData = [];
        while ($responseRow = mysqli_fetch_assoc($responseResult)) {
            $responseData[] = $responseRow;
        }
        // Return the data
        echo json_encode($responseData);
    } else {
        // EventId and email combination does not exist in the events table
        echo json_encode(['success' => false, 'message' => 'EventId and email not found in the events table']);
    }

    // Close the prepared statements
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($responseStmt);
}

mysqli_close($conn);
?>
