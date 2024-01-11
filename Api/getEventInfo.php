<?php
header("Access-Control-Allow-Origin: http://localhost:3000"); // Replace with your React app's URL
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'sgp';
// Disable caching for the login response
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $eventId = isset($_GET['eventId']) ? $_GET['eventId'] : '';
    $email = isset($_GET['email']) ? $_GET['email'] : '';

    if ($eventId === '' || $email === '') {
        echo json_encode(['success' => false, 'message' => 'Please provide eventId and email']);
        exit;
    }

    $fetchStudentInfoQuery = "SELECT Department, Batch, Class FROM student_info WHERE Email = '$email'";
    $studentInfoData = mysqli_query($conn, $fetchStudentInfoQuery);

    if (!$studentInfoData) {
        die('Query failed: ' . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Server error']);
    } else {
        $studentInfoRow = mysqli_fetch_assoc($studentInfoData);
        $department = $studentInfoRow['Department'];
        $batch = $studentInfoRow['Batch'];
        $class = $studentInfoRow['Class'];
        
        $checkConstraintsQuery = "SELECT constraints FROM events WHERE event_id = '$eventId' ";

        $constraintsResult = mysqli_query($conn, $checkConstraintsQuery);
        
        if (!$constraintsResult) {
            die('Query failed: ' . mysqli_error($conn));
            echo json_encode(['success' => false, 'message' => 'Server error']);
        } else {
            $constraintsRow = mysqli_fetch_assoc($constraintsResult);
            
            if ($constraintsRow) {
                $constraint = json_decode($constraintsRow['constraints'], true);
                
                $hasConstraints = false;
                    if (($constraint[0] === $department && $constraint[1] === $batch && $constraint[2] === 'F')
                    ) {
                        $hasConstraints = true;
                    }

                echo json_encode(['success' => $hasConstraints, 'message' => $constraint[0],'a' => $constraint[1], 'b'=> $constraint[2],'ag' => $department, 'bc'=> ($constraint[0] === 'Not Applied') ||
                ($constraint[0] === $department && $constraint[1] === 'Not Applied') ||
                ($constraint[0] === $department && $constraint[1] === $batch && $constraint[2] === 'Not Applied') ||
                ($constraint[0] === $department && $constraint[1] === $batch && $constraint[2] === 'F'),'bq'=> $batch]);
                if ($hasConstraints) {
                $checkStatusQuery = "SELECT Status, IntervalTime FROM events WHERE event_id = '$eventId'";
                $statusResult = mysqli_query($conn, $checkStatusQuery);

                if (!$statusResult) {
                    die('Query failed: ' . mysqli_error($conn));
                    echo json_encode(['success' => false, 'message' => 'Server error']);
                }

                $statusRow = mysqli_fetch_assoc($statusResult);
                $eventStatus = $statusRow['Status'];
                $intervalTime = $statusRow['IntervalTime'];
                                        
                if ($eventStatus === 'closed') {
                    echo json_encode(['success' => false, 'message' => 'The event is already closed']);
                    exit;
                }
                if (strtotime($intervalTime) < strtotime(date('Y-m-d'))) {
                    $updateStatusQuery = "UPDATE events SET Status = 'closed' WHERE event_id = '$eventId'";
                    $updateStatusResult = mysqli_query($conn, $updateStatusQuery);

                    if (!$updateStatusResult) {
                        die('Query failed: ' . mysqli_error($conn));
                        echo json_encode(['success' => false, 'message' => 'Server error']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Event is closed because IntervalTime is expired.']);
                        exit;
                    }
                }

                $query = "SELECT COUNT(*) AS validEventCount FROM events WHERE event_id = '$eventId' AND Status = 'open'";
                $result = mysqli_query($conn, $query);

                if (!$result) {
                    die('Query failed: ' . mysqli_error($conn));
                    echo json_encode(['success' => false, 'message' => 'Server error']);
                } else {
                    $row = mysqli_fetch_assoc($result);
                    $isValidEvent = $row['validEventCount'] > 0;

                    if ($isValidEvent) {
                        $checkResponseQuery = "SELECT COUNT(*) AS responseCount FROM events_response WHERE Event_id = '$eventId' AND Email = '$email'";
                        $responseResult = mysqli_query($conn, $checkResponseQuery);

                        if (!$responseResult) {
                            die('Query failed: ' . mysqli_error($conn));
                            echo json_encode(['success' => false, 'message' => 'Server error']);
                        }

                        $responseRow = mysqli_fetch_assoc($responseResult);
                        $responseCount = $responseRow['responseCount'];

                        $limitQuery = "SELECT `limits` FROM events WHERE event_id = '$eventId'";
                        $limitResult = mysqli_query($conn, $limitQuery);

                        if ($limitResult) {
                            $limitRow = mysqli_fetch_assoc($limitResult);

                            if ($limitRow && isset($limitRow['limits'])) {
                                $eventLimit = $limitRow['limits'];

                                if ($responseCount == 0) {
                                    $studentQuery = "SELECT Name, Roll_No FROM student_info WHERE Email='$email'";
                                    $studentResult = mysqli_query($conn, $studentQuery);

                                    if ($studentResult) {
                                        $studentInfo = mysqli_fetch_assoc($studentResult);

                                        $eventQuery = "SELECT * FROM events WHERE event_id = '$eventId'";
                                        $eventResult = mysqli_query($conn, $eventQuery);

                                        if ($eventResult) {
                                            $eventInfo = mysqli_fetch_assoc($eventResult);
                                            echo json_encode(['success' => true, 'eventInfo' => $eventInfo, 'studentInfo' => $studentInfo]);
                                        } else {
                                            echo json_encode(['success' => false, 'message' => 'Server error']);
                                        }
                                    } else {
                                        echo json_encode(['success' => false, 'message' => 'Error fetching student information']);
                                    }
                                } else {
                                    echo json_encode(['success' => false, 'message' => 'Email already responded to this event']);
                                }
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Invalid event']);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Invalid event']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Invalid event']);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Constraints not matching exceptional condition']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No constraints found for the given event']);
        }

        }
    }
}
mysqli_close($conn);
?>
