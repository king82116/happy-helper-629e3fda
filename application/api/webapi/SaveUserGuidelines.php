<?php 
    include "../../conn.php";
    include "../../functions2.php";

    header('Content-Type: application/json; charset=utf-8');
    header('Strict-Transport-Security: max-age=31536000');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
    header('Access-Control-Allow-Credentials: true');
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    header('Access-Control-Allow-Origin: ' . $origin);
    header('vary: Origin');

    date_default_timezone_set("Asia/Kolkata");
    $shnunc = date("Y-m-d H:i:s");

    $res = [
        'code' => 11,
        'msg' => 'Method not allowed',
        'msgCode' => 12,
        'serviceNowTime' => $shnunc,
    ];

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        http_response_code(405);
        echo json_encode($res);
        exit();
    }

    // Get the request body
    $shonubody = file_get_contents("php://input");
    $shonupost = json_decode($shonubody, true);

    // Check if required parameters are present
    if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
        // Sanitize the inputs
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
        $timestamp = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['timestamp']));

        // Create the string for signature validation
        // Ensure that the concatenation of language, random, and timestamp is correct
        $shonustr = '{"language":"'.$language.'","random":"'.$random.'","timestamp":"'.$timestamp.'"}';
        $shonusign = strtoupper(md5($shonustr));

        // Verify the signature
        if ($shonusign == $signature) {
            // Get the authorization header for JWT token
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];  

            // Check if the JWT token is valid
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, true);

            if ($data_auth['status'] === 'Success') {
                // Get user information from database
                $sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);

                if ($sesnum == 1) {
                    // Process the data if user is valid
                    $shonuid = $data_auth['payload']['id'];
                    $chkserial_ad = mysqli_query($conn, "SELECT * FROM `tb_agent` WHERE `userid`='".$shonuid."' AND `status`='1'");
                    $chkserialrow_ad = mysqli_num_rows($chkserial_ad);

                    if ($chkserialrow_ad == 1) {
                        // Update user guidelines status here, if needed
                        $update_query = "UPDATE user_guidelines SET status = 'completed' WHERE user_id = '$shonuid'";
                        $conn->query($update_query);

                        // Respond with success
                        $res['data'] = null;  // Set data to null as required in the response
                        $res['code'] = 0;
                        $res['msg'] = 'Succeed';
                        $res['msgCode'] = 0;
                        $res['serviceNowTime'] = $shnunc;
                        http_response_code(200);
                        echo json_encode($res);
                    } else {
                        // Unauthorized action, no permission
                        $res['code'] = 4;
                        $res['msg'] = 'No operation permission';
                        $res['msgCode'] = 2;
                        http_response_code(401);
                        echo json_encode($res);
                    }
                } else {
                    // User not found
                    $res['code'] = 4;
                    $res['msg'] = 'No operation permission';
                    $res['msgCode'] = 2;
                    http_response_code(401);
                    echo json_encode($res);
                }
            } else {
                // Invalid JWT token
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
            }
        } else {
            $res['data'] = null;
            $res['code'] = 0;
            $res['msg'] = 'Succeed';
            $res['msgCode'] = 0;
            http_response_code(200);
            echo json_encode($res);
        }
    } else {
        // Invalid parameters error
        $res['code'] = 7;
        $res['msg'] = 'Invalid parameters';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);
    }
?>
