<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// To Remove unwanted errors
error_reporting(E_ALL);

// Initialize variables for error and success messages
$error_message = "";
$success_message = "";

// Add your connection Code
if (isset($_POST['email'])) {
    // Establish database connection (modify these according to your database configuration)
    $server = "localhost";
    $username = "root";
    $password = "";
    $database = "salon_booking"; // Added database name

    $con = mysqli_connect($server, $username, $password, $database); // Fixed parameter order

    // Check connection
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Prepare SQL statement to fetch user data from the database
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic validation
    if (empty($email) || empty($password)) {
        $error_message = "All fields are required.";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    }

    if (empty($error_message)) {
        // Prepare SQL statement to insert new registration
        $insert_sql = "INSERT INTO `login1` (`email`, `password`, `dt`) VALUES ('$email', '$password', current_timestamp())";

        // Begin transaction
        mysqli_begin_transaction($con);

        // Execute the insert query
        if (mysqli_query($con, $insert_sql)) {
            // Commit the transaction
            mysqli_commit($con);
            // Registration successful

            // Query user for login
            $sql = "SELECT * FROM `registration` WHERE `email` = '$email'";
            $result = mysqli_query($con, $sql);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                // Check if the entered password matches the hashed password stored in the database
                if (password_verify($password, $row['password'])) {
                    // Passwords match, login successful
                    $success_message = "Login successful!";

                    // Send login email using PHPMailer
                    require 'PHPMailer-master/src/Exception.php';
                    require 'PHPMailer-master/src/PHPMailer.php';
                    require 'PHPMailer-master/src/SMTP.php';

                    // Create a PHPMailer instance
                    $mail = new PHPMailer(true);

                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'your-email';
                        $mail->Password = 'your Gmail password'; // Replace with your Gmail password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        // Sender and recipient settings
                        $mail->setFrom('Youremail@gmail.com', 'Login');
                        $mail->addAddress($email);

                        // Email content
                        $mail->isHTML(true);
                        $mail->Subject = 'Successful Login';
                        $mail->Body = "You have successfully logged in to our website.";

                        // Send email
                        $mail->send();
                    } catch (Exception $e) {
                        $error_message = "Error sending login email: " . $mail->ErrorInfo;
                    }
                } else {
                    // Passwords don't match
                    $error_message = "Incorrect email or password. You must have registered first!";
                }
            } else {
                // No user found with the entered email
                $error_message = "Incorrect email or password. You must have registered first!";
            }
        } else {
            // Rollback the transaction
            mysqli_rollback($con);
            $error_message = "Error inserting data into the database.";
        }

        // Close the connection
        mysqli_close($con);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        .form-container {
            padding: 40px;
            color: rgb(0, 0, 0);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: rgb(255, 255, 255);
            font-size: 24px;
            font-weight: bold;
        }

        input {
            padding: 12px;
            margin-bottom: 20px;
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

        .toggle-button {
            display: block;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px 110px;
            padding: 10px;
            width: 100%;
        }

        .success {
            background-color: #4CAF50;
            /* Green */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error {
            background-color: #f44336;
            /* Red */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-container" id="loginFormContainer">
            <h2>Login</h2>
            <?php if (!empty($success_message)): ?>
                <div class="success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form action="login.php" id="loginForm" method="post">
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <button type="submit">Login</button>
            </form>
            <a href="registration.php" class="toggle-button">Registration</a>
        </div>
    </div>

    <script>
        function validateLoginForm() {
            // No need for validation logic here since it's not used in the form submission
            return true;
        }
    </script>
</body>

</html>
