<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// To remove unwanted errors
error_reporting(E_ALL);

$message = ""; // Initialize the message variable

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Establish database connection (modify these according to your database configuration)
    $server = "localhost";
    $username = "root";
    $password = "";
    $database = "salon_booking"; // Added database name

    // Attempt to connect to the database
    $con = mysqli_connect($server, $username, $password, $database);

    // Check connection
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Prepare SQL statement to insert data into the database
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);

    // Generate a unique activation code
    $activation_code = md5(uniqid(rand(), true));

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } elseif ($password !== $confirm_password) { // Check if password and confirm password match
        $message = "Password and Confirm Password do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL statement using prepared statements to prevent SQL injection
        $sql = "INSERT INTO registration (username, email, password, activation_code, dt) VALUES (?, ?, ?, ?, current_timestamp())";
        $stmt = mysqli_prepare($con, $sql);

        if ($stmt) {
            // Bind parameters to the statement
            mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $activation_code);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Send email with activation link using PHPMailer
                require 'PHPMailer-master/src/Exception.php';
                require 'PHPMailer-master/src/PHPMailer.php';
                require 'PHPMailer-master/src/SMTP.php';

                // Create a PHPMailer instance
                $mail = new PHPMailer(true);

                try {
                    // SMTP server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'youremail@gmail.com';
                    $mail->Password = 'your Gmail password'; // Replace with your Gmail password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Sender and recipient settings
                    $mail->setFrom('youremail@gmail.com', 'your name');
                    $mail->addAddress($email, $username);

                    // Email content
                    $mail->isHTML(true);
                    $mail->Subject = 'Activate Your Account';
                    $activation_url = "http://" . $_SERVER['HTTP_HOST'] . "/salonreg/login.php?email=$email&code=$activation_code&status=activated";
                    $mail->Body = "Dear $username, <br><br>Please click the following link to activate your account: <br><br><a href='$activation_url'>$activation_url</a>";

                    // Send email
                    $mail->send();

                    $message = "Registration successful. Please check your email to activate your account.";
                } catch (Exception $e) {
                    $message = "Error sending activation email: " . $mail->ErrorInfo;
                }
            } else {
                $message = "Error executing SQL statement: " . mysqli_error($con);
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            $message = "Error preparing SQL statement: " . mysqli_error($con);
        }
    }

    // Close the connection
    mysqli_close($con);
}

// Check if activation link is clicked
if (isset($_GET['email'], $_GET['code'], $_GET['status']) && $_GET['status'] === 'activated') {
    // Display success message
    $message = "Your account has been activated successfully. You can now log in.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Registration</title>
  <link rel="stylesheet" href="asset/css/styles.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-image: url("best.png");
      background-size: cover;
      background-repeat: no-repeat;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      background-color: #000000;
      border-radius: 20px;
      box-shadow: 0px 8px 20px rgb(0, 0, 0);
      width: 400px;
      max-width: 100%;
      overflow: hidden;
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: rgb(255, 255, 255);
      font-size: 24px;
      font-weight: bold;
    }

    .message {
      background-color: #4CAF50;
            /* Green */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      color: rgb(255, 255, 255);
      font-size: 16px;
      font-weight: bold;
    }

    input {
      padding: 12px;
      border: none;
      border-bottom: 2px solid rgb(255, 255, 255);
      background-color: transparent;
      color: rgb(255, 255, 255);
      font-size: 16px;
      outline: none;
      transition: border-color 0.3s ease;
      width: 100%;
      border-radius: 10px;
    }

    input.invalid {
      border-color: rgb(255, 255, 255);
      box-shadow: 0 0 8px rgb(104, 102, 102);
    }

    button {
      padding: 12px;
      background-color: #000000;
      color: rgb(255, 255, 255);
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      outline: none;
      transition: background-color 0.3s ease;
      width: 100%;
      border-radius: 10px;
    }

    button:hover {
      background-color: #000000;
    }
  </style>
</head>

<body>
    <div class="container">
        <h2>User Registration</h2>
        <!-- Display message if set -->
        <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <!-- Add this form to your HTML -->
        <form action="registration.php" method="post" id="registrationForm">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>
    </div>
</body>

</html>
