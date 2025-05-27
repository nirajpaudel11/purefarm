<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// SMTP credentials
$mail_id = "hemantasharma223@gmail.com";
$mail_pass = "szpo stan gtve rbvy";

try {
    // Recipient details
    $recipients = [
        [
            'email' => $_SESSION['login'], // 1st recipient email
            'name' => $_SESSION['username'], // 1st recipient name
            'subject' => 'Payment Successful!',
            'body' => "Dear {$_SESSION['username']},\nYour order has been placed successfully. Your payment token is: {$_SESSION['ordertoken']}."
        ],
        [
            'email' => 'kshitishbhurtel@tuicms.edu.np', // 2nd recipient email
            'name' => 'GSM Bhurtel', // 2nd recipient name
            'subject' => 'Order Confirmation!',
            'body' => "Hello Admin,\nYou have received an order from {$_SESSION['username']}. Payment token is: {$_SESSION['ordertoken']}."
        ]
    ];

    // SMTP configuration
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $mail_id;
    $mail->Password = $mail_pass; // Your App Password or Gmail password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    foreach ($recipients as $recipient) {
        $mail->clearAddresses(); // Clear previous recipient
        $mail->setFrom($mail_id, 'Kshitish Bhurtel');
        $mail->addAddress($recipient['email'], $recipient['name']);
        $mail->Subject = $recipient['subject'];
        $mail->Body = $recipient['body'];
        
        // Send mail
        $mail->send();
    }

    unset($_SESSION['ordertoken']);
    // echo 'Emails have been sent successfully';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
