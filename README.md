# Salon Registration Web App

A web application designed for salon registration, featuring a user-friendly interface for customers to explore services, view pricing, register/login, and contact the salon. The backend is powered by PHP, integrated with a XAMPP server for seamless operation, and utilizes PHPMailer for email functionality.

## Features
- **User Registration & Login**: Secure registration and login system using PHP.
- **Gallery**: A visual showcase of salon services and facilities.
- **Pricing Page**: Detailed pricing information for salon services.
- **Contact Page**: Easy communication for customer inquiries.
- **Email Notifications**: Uses PHPMailer for sending emails.

## Project Structure
- **HTML**: Static pages such as `index.html`, `gallery.html`, and `price.html`.
- **PHP**: Backend scripts for user authentication and email sending (`login.php`, `register.php`, `submit.php`).
- **PHPMailer**: Email handling through the `PHPMailer-master` directory.
- **Assets**: Images and resources stored in folders like `asset` and image files such as `salon1.jpg`.
- **Dependencies**: Node.js for JavaScript packages, defined in `package.json`.

## Prerequisites
- **XAMPP**: Install [XAMPP](https://www.apachefriends.org/index.html) to run the Apache server and PHP backend.
- **Node.js**: Install [Node.js](https://nodejs.org/) to manage JavaScript dependencies.

## How to Configure PHPMailer
1. **Locate the PHPMailer Script**:
   - Open the PHP file where PHPMailer is implemented (e.g., `submit.php`).
   
2. **Add Your Gmail Credentials**:
   - Locate the following lines in the PHPMailer code:
     ```php
     $mail->Username = 'your-email@gmail.com'; // Your Gmail address
     $mail->Password = 'your-password';       // Your Gmail password or app password
     ```
   - Replace `'your-email@gmail.com'` with your Gmail address.
   - Replace `'your-password'` with your Gmail password or [App Password](https://support.google.com/accounts/answer/185833?hl=en) if 2-factor authentication is enabled.

3. **Enable "Less Secure App Access" or Use App Passwords**:
   - If using a standard Gmail account:
     - Enable **Less Secure App Access** in your Google Account settings.
     - Go to [Google Security Settings](https://myaccount.google.com/security) > Enable "Less Secure App Access".
   - For accounts with 2-factor authentication:
     - Generate an **App Password** via [Google App Passwords](https://support.google.com/accounts/answer/185833?hl=en).

4. **Test Email Functionality**:
   - Send a test email to ensure the configuration is working.
   - Check your Gmail account for any security-related notifications and verify them if necessary.

## How to Run
1. **Install Dependencies**:
   ```bash
   npm install
Set Up XAMPP:
Install and launch XAMPP.
Start the Apache and MySQL modules from the XAMPP Control Panel.
Place the Project Files:
Copy the project folder (salonreg_github) to the htdocs directory of your XAMPP installation.
Import Database:
Open phpMyAdmin in your browser (http://localhost/phpmyadmin).
Create a new database (e.g., salon_db).
Import the SQL file (if provided) or manually set up tables for user authentication and other functionalities.
Access the Application:
Open your browser and navigate to http://localhost/salonreg_github.
