<?php
include "../../conn.php";

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
$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['language']) && isset($shonupost['pageNo']) && isset($shonupost['pageSize']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
        $pageNo = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['pageNo']));
        $pageSize = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['pageSize']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
        $shonustr = '{"language":' . $language . ',"pageNo":' . $pageNo . ',"pageSize":' . $pageSize . ',"random":"' . $random . '"}';
        $shonusign = strtoupper(md5($shonustr));
        if ($shonusign == $signature) {
            $list[0]['title'] = '🔒 Official Security Notice';
            $list[0]['siteMessage'] = 'Our customer service will never send any links to members—if you receive a link from someone claiming to be Josh Game customer service, please do not click it, as it may lead to hacking or data loss; always verify through our official website.';
            $list[0]['addtime'] = '2024-03-24 16:23:27';
            $list[1]['title'] = '🔒 आधिकारिक सुरक्षा सूचना';
            $list[1]['siteMessage'] = 'हमारी कस्टमर सर्विस कभी भी सदस्यों को कोई लिंक नहीं भेजेगी — यदि आपको कोई लिंक किसी ऐसे व्यक्ति से प्राप्त होता है जो खुद को JOSH GAME कस्टमर सर्विस बता रहा है, तो कृपया उस पर क्लिक न करें, क्योंकि यह हैकिंग या डेटा चोरी का कारण बन सकता है। कृपया हमेशा हमारी आधिकारिक वेबसाइट के माध्यम से ही सत्यापित करें।';
            $list[1]['addtime'] = '2024-03-24 16:23:05';
            $list[2]['title'] = 'WELCOMME TO JOSH GAME';
            $list[2]['siteMessage'] = '🎉🎉🎉Welcome to join the JOSH platform. We provide a brand new gaming experience and a comprehensive range of popular games. ❤️‍🔥❤️‍🔥❤️‍🔥You are welcome to register at JOSH and participate in the game. Thank you.';
            $list[2]['addtime'] = '2024-03-24 16:24:00';
            $data['list'] = $list;

            $res['data'] = $data;
            $res['code'] = 0;
            $res['msg'] = 'Succeed';
            $res['msgCode'] = 0;
            http_response_code(200);
            echo json_encode($res);
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