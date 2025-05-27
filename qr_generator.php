<?php
// Include the QR code library
include 'phpqrcode/qrlib.php'; // Make sure the path is correct

// Data string as provided
$data = "{'accountNumber':'0765759978620001','accountName':'KSHITISH BHURTEL','amount: 500.00','bankCode':'BOALNPKA','accountType':'New Premium Super Chamatkarik Bachat Khata - Silver (Online)','bankCodeCIPS':'2301'}";

// Path to save the generated QR code
$qr_file = 'qrcodes/esewa_qr.png'; // Save the QR with the appropriate name

// Generate the QR code and save it to a file
QRcode::png($data, $qr_file, QR_ECLEVEL_L, 4, 2);

// Display the QR code image
echo '<h3>Your eSewa QR Code:</h3>';
echo '<img src="' . $qr_file . '" alt="eSewa QR Code">';
?>
