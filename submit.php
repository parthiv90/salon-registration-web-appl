<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// To remove unwanted errors
error_reporting(E_ALL);

// Initialize variables for error and success messages
$error_message = "";
$success_message = "";

// Add your connection code
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Establish database connection (modify these according to your database configuration)
  $server = "localhost";
  $username = "root";
  $password = "";
  $database = "submit"; // Added database name

  $con = mysqli_connect($server, $username, $password, $database); // Fixed parameter order

  // Check connection
  if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Prepare SQL statement to insert data into the database
  $username = $_POST['username'];
  $email = $_POST['email'];
  $salon = $_POST['salon'];
  $salondate = $_POST['salondate'];
  $salontime = $_POST['salontime'];

  // Basic validation
  if (empty($username) || empty($email) || empty($salon) || empty($salondate) || empty($salontime)) {
    $error_message = "All fields are required.";
  }

  // Validate email format
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Invalid email format.";
  }

  if (empty($error_message)) {
    // Begin transaction
    mysqli_begin_transaction($con);

    // Prepare SQL statement to increment registration count
    $update_count_sql = "UPDATE salonreg SET registration_count = registration_count + 1 WHERE salon = '$salon' AND salondate = '$salondate' AND salontime = '$salontime'";

    // Execute the update query 
    mysqli_query($con, $update_count_sql);

    // Prepare SQL statement to insert new registration
    $insert_sql = "INSERT INTO `salonreg` (`username`, `email`, `salon`, `salondate`, `salontime`, `dt`) VALUES ('$username', '$email', '$salon', '$salondate', '$salontime', current_timestamp())";

    // Execute the insert query
    if (mysqli_query($con, $insert_sql)) {
      // Commit the transaction
      mysqli_commit($con);
      // Registration successful

      // Send email confirmation
      require 'PHPMailer-master/src/Exception.php';
      require 'PHPMailer-master/src/PHPMailer.php';
      require 'PHPMailer-master/src/SMTP.php';

      // Create an instance; passing `true` enables exceptions
      $mail = new PHPMailer(true);

      // Server settings
      $mail->isSMTP();                                            // Send using SMTP
      $mail->Host = 'smtp.gmail.com';                       // Set the SMTP server to send through
      $mail->SMTPAuth = true;                                   // Enable SMTP authentication
      $mail->Username = 'yourGmail@gmail.com';         // SMTP username
      $mail->Password = 'gmail password';                  // SMTP password
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable implicit TLS encryption
      $mail->Port = 465;                                    // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

      // Recipients
      $mail->setFrom('yourGmail@gmail.com', 'salonreg');
      $mail->addAddress($email, $username);     // Add a recipient (using the submitted email and username)

      // Content
      $mail->isHTML(true);                                        // Set email format to HTML
      $mail->Subject = "{$username} Successfully booked!";
      $mail->Body = "Dear {$username},<br><br>
                     Thank you for choosing <b>{$salon}</b> for your salon appointment. We are pleased to confirm your booking for {$salontime} on {$salondate}.<br><br>
                     Our team is committed to providing you with exceptional service and ensuring you have a wonderful experience.<br><br>
                     If you have any questions or need to make changes to your appointment, feel free to contact us.<br><br>
                     We look forward to welcoming you to {$salon} and providing you with the best salon experience possible.<br><br>
                     Best Regards,<br>
                     BookMyGlam!";

      try {
        // Attempt to send the email
        $mail->send();
        $success_message = "Message has been sent";
      } catch (Exception $e) {
        $error_message = "Message could not be sent. Mailer Error: " . $e->getMessage();
      }
    } else {
      // Rollback the transaction if there's an error
      mysqli_rollback($con);
      $error_message = "ERROR: " . $insert_sql . "<br>" . mysqli_error($con);
    }
  }

  if (empty($error_message)) {
    // Check if the registration exists for the same salon, date, and time
    $check_sql = "SELECT COUNT(*) AS count FROM salonreg WHERE salon = '$salon' AND salondate = '$salondate' AND salontime = '$salontime'";
    $check_result = mysqli_query($con, $check_sql);
    $check_row = mysqli_fetch_assoc($check_result);
    $registration_count = $check_row['count'];

    if ($registration_count >= 5) {
      $error_message = "You have reached the maximum registrations.Please choose another salon, date, or time.";
    }
  }

  // Close the connection
  mysqli_close($con); 
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salon Registration Form</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-image: url('best.png');
      /* Replace 'path/to/your/image.jpg' with the actual path to your image */
      background-size: cover;
      background-repeat: no-repeat;
      color: #fff;
      margin: 0;
      /* Reset default margin */
      padding: 0;
      /* Reset default padding */
    }

    .container {
      background-color: black;
      /* Add a semi-transparent black overlay */
      border: 5px solid black;
      border-radius: 8px;
      box-shadow: 0px 4px 8px black;
      padding: 20px;
      margin: 20px auto;
      max-width: 400px;
    }

    h2 {
      text-align: center;
      margin-bottom: 36px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      font-weight: bold;
      color: #fff;
    }

    input[type="text"],
    input[type="email"],
    select,
    input[type="datetime-local"],
    button {
      width: 100%;
      padding: 10px;
      border: 1px solid #fff;
      border-radius: 4px;
      background-color: white;
      color: #fff;
      margin-top: 5px;
    }

    button[type="submit"] {
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 12px 20px;
      font-size: 16px;
      cursor: pointer;
      width: 100%;
      margin-top: 20px;
    }

    button[type="submit"]:hover {
      background-color: #0056b3;
    }

    #salon {
      width: 99%;
      padding: 6px;
      border: 2px solid #fff;
      border-radius: 4px;
      background-color: white;
      color: black;
      margin-top: 5px;
      outline: none;
      transition: border-color 0.3s ease;
    }

    #username,
    #email,
    #salonTiming {
      width: 95%;
      padding: 6px;
      border: 2px solid #fff;
      border-radius: 4px;
      background-color: white;
      color: black;
      margin-top: 5px;
      outline: none;
      transition: border-color 0.3s ease;
    }

    .success {
      background-color: green; /* Green */
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
    <h2>Salon Registration Form</h2>
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
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="registrationForm">
      <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="salonName">Salon Name:</label>
        <select id="salon" name="salon" required>
          <option value="5 Star Salon">5 Star Salon</option>
          <option value="Aam Admi Salon">Aam Admi Salon</option>
          <option value="Glamour Glow Salon">Glamour Glow Salon</option>
          <option value="Saffron Glow Salon">Saffron Glow Salon</option>
          <option value="Elegant Edge Salon">Elegant Edge Salon</option>
        </select>
      </div>

      <div class="form-group">
        <label for="salonTiming">Salon Date:</label>
        <input type="date" id="salonTiming" name="salondate" required />
      </div>

      <div class="form-group">
        <label for="salonName">Salon Timing:</label>
        <select id="salon" name="salontime" required>
          <option value="">Select Timing</option>
          <option value="7:00 AM">7:00 AM</option>
          <option value="8:00 AM">8:00 AM</option>
          <option value="9:00 AM">9:00 AM</option>
          <option value="10:00 AM">10:00 AM</option>
          <option value="11:00 AM">11:00 AM</option>
          <option value="12:00 PM">12:00 PM</option>
          <option value="1:00 PM">1:00 PM</option>
          <option value="2:00 PM">2:00 PM</option>
          <option value="3:00 PM">3:00 PM</option>
          <option value="4:00 PM">4:00 PM</option>
          <option value="5:00 PM">5:00 PM</option>
          <option value="6:00 PM">6:00 PM</option>
          <option value="7:00 PM">7:00 PM</option>
          <option value="8:00 PM">8:00 PM</option>
        </select>
      </div>
      <button type="submit">Submit</button>
    </form>
  </div>
</body>

</html>