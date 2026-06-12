<?php
session_start();

include "../../conn.php";
include "../../functions2.php";

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');


$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ar-origin');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");

$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['language'], $shonupost['random'], $shonupost['signature'], $shonupost['timestamp'])) {
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
        $email = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['email']));
        $emailType = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['emailType']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));

        $shonustr = '{"email":"' . $email . '", "emailType":"' . $emailType . '", "language":"' . $language . '", "random":"' . $random . '"}';
        $shonusign = strtoupper(md5($shonustr));

        if ($shonusign) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, 1);

            if ($data_auth['status'] === 'Success') {
                $sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);

                if (mysqli_num_rows($sesresult) == 1) {
                    $otp = rand(100000, 999999);
                    $_SESSION['otp'] = $otp;
                    $_SESSION['otp_expiry'] = time() + 200;

                    // Mailgun API Integration
                    $apiKey = "e7aaf49722dde187107a1d66e0cf7281-3d4b3a2a-97136132"; // Replace with your Mailgun API Key
                    $domain = "88bitluck.com"; // Replace with your Mailgun Domain

                    $postData = [
                        'from' => '55Five  <mailgun@' . $domain . '>',
                        'to' => $email,
                        'subject' => 'Your OTP Code For 55FIVE email Binding',
                        'html' => "Your OTP code is: <b>$otp</b>. It is valid for 10 minutes."
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.mailgun.net/v3/$domain/messages");
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($ch, CURLOPT_USERPWD, "api:$apiKey");
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $response = curl_exec($ch);
                    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($httpStatus == 200) {
                      
     $updateotp = "UPDATE shonu_subjects SET emailotp = '$otp' WHERE akshinak = '$author'";
	$updateotpresult = $conn->query($updateotp); 
                      
                      
                      
                      
                        $res['code'] = 0;
                        $res['msg'] = 'Mail sent successfully';
                        $res['msgCode'] = 154;
                    } else {
                        $res['code'] = 0;
                        $res['msg'] = 'Failed to send email';
                        $res['msgCode'] = 200;
                    }
                } else {
                    $res['code'] = 4;
                    $res['msg'] = 'No operation permission';
                    $res['msgCode'] = 2;
                }
            } else {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
    }
} else {
    http_response_code(405);
}

echo json_encode($res);
?>
