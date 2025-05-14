<?php
require_once __DIR__ . '/vendor/autoload.php'; 

use AfricasTalking\SDK\AfricasTalking;

function sendSMS($to, $message) {

    global $mysqli;

    $stmt = $mysqli->prepare("INSERT INTO messages (student_id, phone, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $student_id, $to, $message);
    $stmt->execute();

    $username = "sandbox"; 
    $apiKey   = "atsk_7a45cac1fc762d08167eda88fcc56f9383b00ed680ccd642de535ab0af6eec238442ae1e";

    $AT = new AfricasTalking($username, $apiKey);

    $sms = $AT->sms();

    try {
        $result = $sms->send([
            'to'      => $to,
            'message' => $message,
            'from'    => "BRD-LAPTOP-SUPPORT" //61612 
        ]);
        return true;
    } catch (Exception $e) {
        file_put_contents("sms_error_log.txt", $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}
