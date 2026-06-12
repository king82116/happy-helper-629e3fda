<?php
include "../../conn.php";
include "../../functions2.php";

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, AR-REAL-IP');
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

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo json_encode($res);
    exit;
}

if (!isset($shonupost['language']) || !isset($shonupost['logintype']) || !isset($shonupost['phonetype']) || !isset($shonupost['pwd'])
    || !isset($shonupost['random']) || !isset($shonupost['signature']) || !isset($shonupost['timestamp']) || !isset($shonupost['username']) || !isset($shonupost['captchaId']) || !isset($shonupost['track'])) {
    $res['msg'] = 'Missing required parameters';
    $res['msgCode'] = 102;
    http_response_code(400);
    echo json_encode($res);
    exit;
}

$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
$logintype = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['logintype']));
$phonetype = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['phonetype']));
$pwd = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['pwd']));
$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
$username = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['username']));
$shonustr = '{"language":"'.$language.'","logintype":"'.$logintype.'","phonetype":"'.$phonetype.'","pwd":"'.$pwd.'","random":"'.$random.'","username":"'.$username.'"}';

$captchaId = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['captchaId']));
$trackData = $shonupost['track'];

$captchaQuery = "SELECT correctPositionx FROM captcha_data WHERE captchaId='$captchaId'";
$captchaResult = $conn->query($captchaQuery);
if ($captchaResult && mysqli_num_rows($captchaResult) == 1) {
    $captchaRow = mysqli_fetch_assoc($captchaResult);
    $correctPositionx = $captchaRow['correctPositionx'];
    $userTracks = $trackData['tracks'];
    $lastTrack = end($userTracks);
    $userPositionx = $lastTrack['x'];
    
    if (abs($userPositionx - $correctPositionx) > 5) {
        $res = [
            "data" => null,
            "code" => 1,
            "msg" => "Verification failed, please try again",
            "msgCode" => 31,
            "serviceNowTime" => date("Y-m-d H:i:s")
        ];
        http_response_code(200);
        echo json_encode($res);
        exit;
    }
} else {
    $res['msg'] = 'Captcha data not found';
    $res['msgCode'] = 32;
    http_response_code(400);
    echo json_encode($res);
    exit;
}

if (substr($username, 0, 2) == "91") {
    $username = substr($username, 2);
}

if ($logintype == 'mobile') {
    $shonusql = "SELECT id, password, status, ishonup, codechorkamukala FROM shonu_subjects WHERE mobile='$username'";
} else if ($logintype == 'email') {
    $shonusql = "SELECT id, password, status, ishonup, codechorkamukala FROM shonu_subjects WHERE email='$username'";
} else {
    $shonusql = "SELECT id, password, status, ishonup, codechorkamukala FROM shonu_subjects WHERE mobile='$username'";
}

$shonuresult = $conn->query($shonusql);
$shonunum = mysqli_num_rows($shonuresult);

if ($shonunum != 1) {
    $res['code'] = 1;
    $res['msg'] = 'User not exists';
    $res['msgCode'] = 101;
    http_response_code(200);
    echo json_encode($res);
    exit;
}

$shonurow = mysqli_fetch_array($shonuresult);
$password = $shonurow['password'];

if ($password != md5($pwd)) {
    $pwderrsql = "UPDATE shonu_subjects SET shonupwderr=shonupwderr+1 WHERE mobile='$username'";
    $conn->query($pwderrsql);
    $pwderr = "SELECT shonupwderr FROM shonu_subjects WHERE mobile='$username'";
    $pwderrresult = $conn->query($pwderr);
    $pwderrrow = mysqli_fetch_array($pwderrresult);
    $pwderrvalue = $pwderrrow['shonupwderr'];
    
    $data['tokenHeader'] = 'Bearer ';
    $data['token'] = null;
    $data['expiresIn'] = 0;
    $data['refreshToken'] = null;
    $data['passwordErrorNum'] = $pwderrvalue;
    $data['passwordErrorMaxNum'] = 30;
    
    $res['code'] = 1;
    $res['msg'] = 'Password does not correct';
    $res['msgCode'] = 147;
    http_response_code(200);
    echo json_encode($res);
    exit;
}

if ($shonurow['status'] != 1) {
    $res['code'] = 1;
    $res['msg'] = 'YOUR ID HAS BEEN BLOCKED PLEASE CONTACT SUPPORT FOR FURTHER QUERY';
    $res['msgCode'] = 122;
    http_response_code(200);
    echo json_encode($res);
    exit;
}

$data['expiresIn'] = time() + 86400;
$shnutkn_head = array('alg' => 'HS256', 'typ' => 'JWT');
$shnutkn_load = array('id' => $shonurow['id'], 'mobile' => $username, 'status' => $shonurow['status'], 'expire' => $data['expiresIn'], 'ishonup' => $shonurow['ishonup'], 'codechorkamukala' => $shonurow['codechorkamukala']);
$data['tokenHeader'] = 'Bearer ';
$data['token'] = generate_jwt($shnutkn_head, $shnutkn_load);
$shnutkn_head_rfsh = array('alg' => 'HS256', 'typ' => 'JWT');
$shnutkn_load_rfsh = array('id' => $shonurow['id'], 'mobile' => $username, 'status' => $shonurow['status'], 'expire' => $data['expiresIn']);
$data['refreshToken'] = generate_jwt($shnutkn_head_rfsh, $shnutkn_load_rfsh);
$data['passwordErrorNum'] = 0;
$data['passwordErrorMaxNum'] = 30;

$ipaddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$user_agent = $_SERVER['HTTP_USER_AGENT'];

$pwderrsql = "UPDATE shonu_subjects SET shonupwderr=0, ishonup='$ipaddress', shonullgnt='$shnunc', akshinak='" . $data['token'] . "', tnegaresunohs='$user_agent' WHERE mobile='$username'";
$conn->query($pwderrsql);

$idQuery = "SELECT id FROM shonu_subjects WHERE mobile = '$username'";
$idResult = $conn->query($idQuery);

if ($idResult && $idResult->num_rows > 0) {
    $row = $idResult->fetch_assoc();
    $id = $row['id'];
    $title = "LOGIN NOTIFICATION";
    $state = 0;
    $insertNotificationQuery = "INSERT INTO notification (state, title, user_id, created_at) VALUES ($state, '$title', $id, '$shnunc')";
    $conn->query($insertNotificationQuery);
}

$res['data'] = $data;
$res['code'] = 0;
$res['msg'] = 'Succeed';
$res['msgCode'] = 0;
http_response_code(200);
echo json_encode($res);
?>
