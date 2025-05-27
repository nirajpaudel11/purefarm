<?php
session_start();
include('includes/config.php');
// Get the pidx from the URL
$pidx = $_GET['pidx'] ?? null;

if ($pidx) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
        CURLOPT_HTTPHEADER => array(
            'Authorization: key b2d09c43309f41feb8db370e76c37558',
            'Content-Type: application/json',
        ),
    ));
 

    $response = curl_exec($curl);
    curl_close($curl);

    if ($response) {
        $responseArray = json_decode($response, true);
        echo "<script>console.log(" . json_encode($responseArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ");</script>";
        switch ($responseArray['status']) {
            case 'Completed':
                mysqli_query($con,"update orders set paymentMethod='Khalti Wallet' where userId='".$_SESSION['id']."' and paymentMethod is null ");
		        unset($_SESSION['cart']);
                $_SESSION['transaction_msg'] = '<script>
                        Swal.fire({
                            icon: "success",
                            title: "Transaction successful.",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>';

                    echo "<script>alert('Payment completed: " . $responseArray['transaction_id'] . "');
                    window.location.href = 'payment_message.php';</script>";
                exit();
                break;
            case 'Expired':
            case 'User canceled':
                
                $_SESSION['transaction_msg'] = '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Transaction failed.",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>';
                    echo "<script>
                                alert('Payment canceled:');
                                window.location.href = 'my-cart.php';
                            </script>";
                exit();
                break;
            default:
                $_SESSION['transaction_msg'] = '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Transaction failed.",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>';
                header("Location: my-cart.php");
                exit();
                break;
        }
    }
}