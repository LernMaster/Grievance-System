<?php
header("Access-Control-Allow-Origin: http://192.168.77.250:3000"); // Replace with your React app's URL
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'sgp';
// Disable caching for the login response
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';


// Disable caching for the login response

// Replace these credentials with your actual database credentials


// Connect to the database




// Disable caching for the login response

// Replace these credentials with your actual database credentials


// Connect to the database
$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $to = isset($data['to']) ? $data['to'] : '';
    $subject = isset($data['subject']) ? $data['subject'] : '';
    
    // Check if the email exists in the admin_login table
    $checkEmailQuery = "SELECT * FROM student_login WHERE email = '$to'";
    $result = mysqli_query($conn, $checkEmailQuery);
    
    if (mysqli_num_rows($result) > 0) {
        // Email exists in the database, proceed with sending the email
        $randomSixDigitNumber = rand(100000, 999999);
        $message = '<html>
        <head>
        <style>
          .container {
            background-color: #F7F7F7;
            border:2px solid black;
            padding-bottom: 20px;
            color:#F8F8F8;
            font-family: "Gill Sans", sans-serif;
            border-radius:10px;
          }
          .container hr{
            margin-left:30px;
            margin-right:30px;
          }
          .container .body{
            padding-left:40px;
            padding-right:40px;
          }
          .container .body p{
            color:#343a40;
          }
          .container  h1{
              text-align: center;
              color:#19AFFF;
          }
          .container .body h2{
              text-align: center;
              color:black;
          }
  
        </style>
        </head>
        <body>
        <div class="container">
          <h1>Student Email Verification</h1>
          <hr/>
          <div class="body">
          <p>Hello <span>'.$to.'</span></p>
          <p>This is a generated OTP to Reset Your Password</p>
          <br/>
          <p>Your Verfication code:</p>
          <h2>'. $randomSixDigitNumber .'</h2>
          </div>
        </div>
        </body>
      </html>';
        
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pradeept.21cse@kongu.edu';
            $mail->Password = 'syjzftnfvrzkjydq';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('pradeept.21cse@kongu.edu', 'Student Complaint Portal'); // Replace with your Gmail email address and sender name
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $message;

            // Send the email
            $mail->send();

            $response = array("otp" => $randomSixDigitNumber, "message" => "");
        } catch (Exception $e) {
            $response = array("message" => "Failed to send the email: " . $e->getMessage());
        }
    } else {
        // Email does not exist in the database
        $response = array("message" => "*Email is invalid");
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    echo "Email failed";
}
?>












