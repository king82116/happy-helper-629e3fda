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
    if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
        $shonustr = '{"language":'.$language.',"random":"'.$random.'"}';
        $shonusign = strtoupper(md5($shonustr));
        
        if ($shonusign == $signature) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];                
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, 1);

            if ($data_auth['status'] === 'Success') {
                $sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);

                if ($sesnum == 1) {
                    $vipquery = "SELECT expe, lvl FROM vip WHERE userid = ".$data_auth['payload']['id'];
                    $vipresult = $conn->query($vipquery);
                    $viparr = mysqli_fetch_array($vipresult);

                    $currentExp = (int)$viparr['expe'];
                    $currentLvl = (int)$viparr['lvl'];

                    // VIP levels config [id, startExp, endExp]
                    $levels = [
                        [1, 1, 3000],
                        [2, 3000, 30000],
                        [3, 30000, 400000],
                        [4, 400000, 4000000],
                        [5, 4000000, 20000000],
                        [6, 20000000, 80000000],
                        [7, 80000000, 300000000],
                        [8, 300000000, 1000000000],
                        [9, 1000000000, 5000000000],
                        [10, 5000000000, 9999999999]
                    ];

                    $vip_levels = [];
                    foreach ($levels as $level) {
                        list($id, $start, $end) = $level;

                        // 🔥 Main fix: Force 100% progress for completed levels
                        if ($currentLvl > $id || $currentExp >= $end) {
                            $progress = 100;
                        
                        } else {
                            $progress = round((($currentExp - $start) / ($end - $start)) * 100);
                        }

                        $vip_levels[] = [
                            "id" => $id,
                            "vipName" => "VIP$id",
                            "status" => 1,
                            "currentExp" => $currentExp,
                            "upgrade" => $end,
                            "relegationExp" => $start,
                            "relegation" => $start,
                            "deductExp" => $end - $start,
                            "amount" => 1,
                            "progressPercent" => $progress,
                            "upgradeStatus" => $currentLvl >= $id ? 1 : 0
                        ];
                    }

                    $res['data'] = $vip_levels;
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    http_response_code(200);
                    echo json_encode($res);
                } else {
                    $res['code'] = 4;
                    $res['msg'] = 'No operation permission';
                    $res['msgCode'] = 2;
                    http_response_code(401);
                    echo json_encode($res);
                }
            } else {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);
    }        
} else {
    http_response_code(405);
    echo json_encode($res);
}
?>
