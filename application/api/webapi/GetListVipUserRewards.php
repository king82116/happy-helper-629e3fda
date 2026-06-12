<?php 
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
    if (isset($shonupost['language'], $shonupost['random'], $shonupost['signature'], $shonupost['timestamp'], $shonupost['vipLevel'])) {
        $language = htmlspecialchars(mysqli_real_escape_string($conn, (string) $shonupost['language']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, (string) $shonupost['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, (string) $shonupost['signature']));
        $lvl = (int) $shonupost['vipLevel'];
        $shonustr = '{"language":'.$language.',"random":"'.$random.'","vipLevel":'.$lvl.'}';
        $shonusign = strtoupper(md5($shonustr));

        if ($shonusign == $signature) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION'] ?? '');
            if (count($bearer) < 2 || empty($bearer[1])) {
                $res['code'] = 4;
                $res['msg'] = 'Authorization header missing';
                $res['msgCode'] = 4;
                http_response_code(401);
                echo json_encode($res);
                exit;
            }

            $author = $bearer[1];
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, true);

            if (!is_array($data_auth) || ($data_auth['status'] ?? '') !== 'Success') {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
                exit;
            }

            $shonuid = (int) $data_auth['payload']['id'];
            $sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '" . mysqli_real_escape_string($conn, $author) . "' LIMIT 1";
            $sesresult = $conn->query($sesquery);

            if (!$sesresult || mysqli_num_rows($sesresult) !== 1) {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
                exit;
            }

            $vipCheckQuery = "SELECT lvl FROM vip WHERE userid = '$shonuid' LIMIT 1";
            $vipCheckResult = $conn->query($vipCheckQuery);

            if ($vipCheckResult && $vipCheckResult->num_rows > 0) {
                $reward1 = getVipLevelUpReward($lvl);
                $reward2 = getVipMonthlyReward($lvl);

                $data = [
                    [
                        'id' => 0,
                        'rewardType' => 1,
                        'integral' => 0,
                        'balance' => $reward1,
                        'status' => 1,
                        'rate' => 0.0,
                    ],
                    [
                        'id' => 0,
                        'rewardType' => 2,
                        'integral' => 0,
                        'balance' => $reward2,
                        'status' => 1,
                        'rate' => 0.0,
                    ],
                ];

                $currentMonthStart = date('Y-m-01 00:00:00');
                $currentMonthEnd = date('Y-m-t 23:59:59');
                $statusQuery = "SELECT type AS rewardType, status, motta, created_at
                                FROM viprec
                                WHERE user_id = '$shonuid'
                                  AND lvl = $lvl
                                  AND (
                                      type != 2
                                      OR (type = 2 AND created_at BETWEEN '$currentMonthStart' AND '$currentMonthEnd')
                                  )";
                $statusResult = $conn->query($statusQuery);

                if ($statusResult) {
                    while ($row = $statusResult->fetch_assoc()) {
                        foreach ($data as &$rewardData) {
                            if ($rewardData['rewardType'] == (int) $row['rewardType']) {
                                $rewardData['balance'] = $row['motta'];
                                $rewardData['status'] = (int) $row['status'];
                                break;
                            }
                        }
                        unset($rewardData);
                    }
                }

                $res = [
                    'data' => $data,
                    'code' => 0,
                    'msg' => 'Succeed',
                    'msgCode' => 0,
                    'serviceNowTime' => $shnunc,
                ];
            } else {
                $res = [
                    'data' => [],
                    'code' => 0,
                    'msg' => 'VIP not open: No level found for user',
                    'msgCode' => 0,
                    'serviceNowTime' => $shnunc,
                ];
            }

            http_response_code(200);
            echo json_encode($res);
            exit;
        }

        $res['code'] = 5;
        $res['msg'] = 'Wrong signature';
        $res['msgCode'] = 3;
        http_response_code(200);
        echo json_encode($res);
        exit;
    }

    $res['code'] = 7;
    $res['msg'] = 'Param is Invalid';
    $res['msgCode'] = 6;
    http_response_code(200);
    echo json_encode($res);
    exit;
}

http_response_code(405);
echo json_encode($res);
?>
