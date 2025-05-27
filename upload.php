<?php
session_start(); 
include('includes/config.php');
if(isset($_SESSION['id'])){
    // Simulate a user ID (replace with $_SESSION['uid'] if using session-based authentication)
$uid = $_SESSION['id']; // Example UID
echo "<script>
            console.log('{$uid}');
            </script>";
$name = $_SESSION['username'];
$final = $uid . $name;


// Check if form is submitted
if (isset($_POST['upload'])) {
    // Check if file is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        
        // Define the target directory (e.g., folder/uid/)
        $targetDir = "payment_ss/$final/";
        
        // Create the directory if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Create with permissions
        }

        // Generate a unique file name to prevent overwriting
        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $uniqueName = md5(time() . $image['name']) . '.' . $extension;

        // Define the full path to save the image
        $targetFile = $targetDir . $uniqueName;
        echo"<script>console.log('{$targetFile}')</script>";
        // Move the uploaded file to the target directory
        if (move_uploaded_file($image['tmp_name'], $targetFile)) {
            mysqli_query($con,"update orders set paymentMethod='QR Payment',payment_ss = '{$uniqueName}' where userId='".$_SESSION['id']."' and paymentMethod is null and order_token ='".$_SESSION['ordertoken']."' ");
            unset($_SESSION['cart']);
            echo "<script>
            alert('Your payment screenshot will be verified soon.');
            window.location.href = 'payment_message.php';
            </script>";
        } else {
            echo "Failed to upload the image. Please try again.";
        }
    } else {
        echo "No file uploaded or an error occurred.";
    }
} else {
    echo "Invalid request.";
}
}else{
    header('location:logout.php');
}
?>
